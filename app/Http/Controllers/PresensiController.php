<?php

namespace App\Http\Controllers;

use App\Models\KartuRfid;
use App\Models\Presensi;
use App\Models\Rombel;
use App\Models\RombelSiswa;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PresensiController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
            'tahun_ajaran_id' => ['nullable', 'exists:tahun_ajaran,id'],
            'rombel_id' => ['nullable', 'exists:rombel,id'],
        ]);

        $tanggal = ($filters['tanggal'] ?? null) ?: now()->toDateString();
        $tahun_ajarans = TahunAjaran::latest()->get();
        $tahun_ajaran_id = ($filters['tahun_ajaran_id'] ?? null)
            ?: optional($tahun_ajarans->firstWhere('is_aktif', true))->id;
        $rombel_id = $filters['rombel_id'] ?? null;
        $rombels = collect();
        $presensi_data = collect();

        if ($tahun_ajaran_id) {
            $rombels = $this->accessibleRombels($request)
                ->where('tahun_ajaran_id', $tahun_ajaran_id)
                ->orderByRaw("CASE tingkat WHEN '7' THEN 1 WHEN '8' THEN 2 WHEN '9' THEN 3 ELSE 4 END")
                ->orderByRaw('LENGTH(nama)')
                ->orderBy('nama')
                ->get();
        }

        if ($rombel_id && ! $rombels->contains('id', (int) $rombel_id)) {
            abort_if($request->user()->role === 'wali_kelas', 403);
            $rombel_id = null;
        }

        if ($rombel_id) {
            $rombel_siswas = RombelSiswa::with('siswa')
                ->where('rombel_id', $rombel_id)
                ->whereHas('siswa', fn ($query) => $query->where('status', 'aktif'))
                ->where('tanggal_masuk', '<=', $tanggal)
                ->where(fn ($query) => $query->whereNull('tanggal_keluar')->orWhere('tanggal_keluar', '>=', $tanggal))
                ->get();

            $existing_presensi = Presensi::where('rombel_id', $rombel_id)
                ->where('tanggal', $tanggal)
                ->get()
                ->keyBy('siswa_id');

            $presensi_data = $rombel_siswas->map(function ($rs) use ($existing_presensi) {
                $presensi = $existing_presensi->get($rs->siswa_id);

                return (object) [
                    'siswa' => $rs->siswa,
                    'status' => $presensi?->status,
                    'waktu_scan' => $presensi?->waktu_scan,
                    'metode' => $presensi?->metode,
                ];
            })->sortBy(fn ($item) => $item->siswa->nama_lengkap);
        }

        return view('pages.presensi.index', compact(
            'tahun_ajarans', 'rombels', 'presensi_data',
            'tahun_ajaran_id', 'rombel_id', 'tanggal'
        ), ['title' => 'Manajemen Presensi Harian']);
    }

    public function kiosk()
    {
        return view('pages.presensi.kiosk', ['title' => 'Kiosk Presensi RFID']);
    }

    public function scan(Request $request)
    {
        $request->merge(['kode_uid' => strtoupper(trim((string) $request->input('kode_uid')))]);
        $validated = $request->validate([
            'kode_uid' => ['required', 'string', 'min:4', 'max:32', 'regex:/^[A-Z0-9:-]+$/'],
        ]);

        $kartu = KartuRfid::where('kode_uid', $validated['kode_uid'])->where('status', 'aktif')->first();
        if (! $kartu) {
            return response()->json(['success' => false, 'message' => 'Kartu tidak dikenali atau tidak aktif.'], 404);
        }

        $siswa = Siswa::whereKey($kartu->siswa_id)->where('status', 'aktif')->first();
        if (! $siswa) {
            return response()->json(['success' => false, 'message' => 'Siswa tidak ditemukan atau statusnya sudah tidak aktif.'], 422);
        }

        $hari_ini = now()->toDateString();
        $rombel_siswa = RombelSiswa::with(['rombel.tahunAjaran'])
            ->where('siswa_id', $siswa->id)
            ->where('tanggal_masuk', '<=', $hari_ini)
            ->where(fn ($query) => $query->whereNull('tanggal_keluar')->orWhere('tanggal_keluar', '>=', $hari_ini))
            ->whereHas('rombel.tahunAjaran', fn ($query) => $query->where('is_aktif', true))
            ->latest('tanggal_masuk')
            ->first();

        if (! $rombel_siswa) {
            return response()->json(['success' => false, 'message' => 'Siswa belum terdaftar di Rombel (Kelas) pada Tahun Ajaran yang aktif.'], 422);
        }

        $waktu_sekarang = now()->format('H:i:s');
        $batas_terlambat = Carbon::createFromFormat('H:i:s', config('presensi.batas_terlambat'));
        $status_kehadiran = now()->format('H:i:s') > $batas_terlambat->format('H:i:s') ? 'terlambat' : 'hadir';

        try {
            [$presensi, $baru] = DB::transaction(function () use ($siswa, $rombel_siswa, $hari_ini, $waktu_sekarang, $status_kehadiran) {
                $presensi = Presensi::where('siswa_id', $siswa->id)
                    ->where('tanggal', $hari_ini)
                    ->lockForUpdate()
                    ->first();

                if ($presensi) {
                    return [$presensi, false];
                }

                return [Presensi::create([
                    'siswa_id' => $siswa->id,
                    'rombel_id' => $rombel_siswa->rombel_id,
                    'tanggal' => $hari_ini,
                    'waktu_scan' => $waktu_sekarang,
                    'status' => $status_kehadiran,
                    'metode' => 'rfid',
                    'dicatat_oleh' => null,
                ]), true];
            });
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem saat menyimpan presensi.'], 500);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'nama' => $siswa->nama_lengkap,
                'nis' => $siswa->nis,
                'foto_url' => $siswa->foto_url ? asset('storage/'.$siswa->foto_url) : null,
                'kelas' => 'Kelas '.$rombel_siswa->rombel->label,
                'waktu' => $presensi->waktu_scan,
                'status' => $presensi->status,
                'pesan' => $baru ? 'Berhasil melakukan presensi.' : 'Anda sudah melakukan presensi hari ini.',
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rombel_id' => ['required', 'exists:rombel,id'],
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'presensi' => ['required', 'array'],
            'presensi.*' => ['required', Rule::in(['hadir', 'terlambat', 'izin', 'sakit', 'alpa'])],
        ]);

        $rombel = $this->accessibleRombels($request)->findOrFail($validated['rombel_id']);
        $anggotaIds = RombelSiswa::where('rombel_id', $rombel->id)
            ->whereHas('siswa', fn ($query) => $query->where('status', 'aktif'))
            ->where('tanggal_masuk', '<=', $validated['tanggal'])
            ->where(fn ($query) => $query->whereNull('tanggal_keluar')->orWhere('tanggal_keluar', '>=', $validated['tanggal']))
            ->pluck('siswa_id')
            ->map(fn ($id) => (string) $id)
            ->sort()
            ->values();
        $submittedIds = collect(array_keys($validated['presensi']))->map(fn ($id) => (string) $id)->sort()->values();

        if ($submittedIds->duplicates()->isNotEmpty() || $submittedIds->all() !== $anggotaIds->all()) {
            return back()->withErrors(['presensi' => 'Data presensi harus memuat seluruh siswa aktif anggota rombel pada tanggal tersebut.'])->withInput();
        }

        DB::transaction(function () use ($validated, $rombel, $request) {
            $existing = Presensi::where('rombel_id', $rombel->id)
                ->where('tanggal', $validated['tanggal'])
                ->lockForUpdate()
                ->get()
                ->keyBy('siswa_id');

            foreach ($validated['presensi'] as $siswaId => $status) {
                $presensi = $existing->get((int) $siswaId);
                if ($presensi && $presensi->status === $status) {
                    continue;
                }

                if ($presensi) {
                    $presensi->update(['status' => $status, 'dicatat_oleh' => $request->user()->id]);
                    continue;
                }

                Presensi::create([
                    'siswa_id' => $siswaId,
                    'rombel_id' => $rombel->id,
                    'tanggal' => $validated['tanggal'],
                    'waktu_scan' => in_array($status, ['hadir', 'terlambat'], true) ? now()->format('H:i:s') : null,
                    'status' => $status,
                    'metode' => 'manual',
                    'dicatat_oleh' => auth()->id(),
                ]);
            }
        });

        return redirect()->route('presensi.index', [
            'tahun_ajaran_id' => $rombel->tahun_ajaran_id,
            'rombel_id' => $rombel->id,
            'tanggal' => $validated['tanggal'],
        ])->with('success', 'Data presensi berhasil disimpan.');
    }

    private function accessibleRombels(Request $request)
    {
        return Rombel::query()->when(
            $request->user()->role === 'wali_kelas',
            fn ($query) => $query->whereHas('waliGuru', fn ($guru) => $guru->where('user_id', $request->user()->id))
        );
    }
}
