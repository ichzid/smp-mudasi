<?php

namespace App\Http\Controllers;

use App\Models\JadwalPresensi;
use App\Models\KartuRfid;
use App\Models\Presensi;
use App\Models\Rombel;
use App\Models\RombelSiswa;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class PresensiController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate(['tanggal' => ['nullable', 'date_format:Y-m-d'], 'tahun_ajaran_id' => ['nullable', 'exists:tahun_ajaran,id'], 'rombel_id' => ['nullable', 'exists:rombel,id']]);
        $tanggal = $filters['tanggal'] ?? now('Asia/Jakarta')->toDateString();
        $tahun_ajarans = TahunAjaran::latest()->get();
        $tahun_ajaran_id = $filters['tahun_ajaran_id'] ?? optional($tahun_ajarans->firstWhere('is_aktif', true))->id;
        $rombel_id = $filters['rombel_id'] ?? null;
        $rombels = $tahun_ajaran_id ? $this->accessibleRombels($request)->where('tahun_ajaran_id', $tahun_ajaran_id)->orderBy('tingkat')->orderBy('nama')->get() : collect();
        if ($rombel_id && ! $rombels->contains('id', (int) $rombel_id)) {
            abort_if($request->user()->role === 'wali_kelas', 403);
        }
        $presensi_data = collect();
        if ($rombel_id && $rombels->contains('id', (int) $rombel_id)) {
            $existing = Presensi::where('rombel_id', $rombel_id)->whereDate('tanggal', $tanggal)->get()->keyBy('siswa_id');
            $presensi_data = RombelSiswa::with('siswa')->where('rombel_id', $rombel_id)->whereHas('siswa', fn ($q) => $q->where('status', 'aktif'))->where('tanggal_masuk', '<=', $tanggal)->where(fn ($q) => $q->whereNull('tanggal_keluar')->orWhere('tanggal_keluar', '>=', $tanggal))->get()->map(function ($rs) use ($existing) {
                $p = $existing->get($rs->siswa_id);

                return (object) ['siswa' => $rs->siswa, 'status' => $p?->status, 'waktu_masuk' => $p?->waktu_masuk, 'metode_masuk' => $p?->metode_masuk, 'waktu_pulang' => $p?->waktu_pulang, 'status_pulang' => $p?->status_pulang, 'alasan_pulang_cepat' => $p?->alasan_pulang_cepat, 'catatan_pulang' => $p?->catatan_pulang];
            })->sortBy(fn ($i) => $i->siswa->nama_lengkap);
        }

        return view('pages.presensi.index', compact('tahun_ajarans', 'rombels', 'presensi_data', 'tahun_ajaran_id', 'rombel_id', 'tanggal'), ['title' => 'Manajemen Presensi Harian']);
    }

    public function kiosk()
    {
        return view('pages.presensi.kiosk', ['title' => 'Kiosk Presensi RFID']);
    }

    public function scan(Request $request)
    {
        $request->merge(['kode_uid' => strtoupper(trim((string) $request->input('kode_uid')))]);
        $uid = $request->validate(['kode_uid' => ['required', 'string', 'min:4', 'max:32', 'regex:/^[A-Z0-9:-]+$/']])['kode_uid'];
        $now = Carbon::now('Asia/Jakarta');
        $tanggal = $now->toDateString();
        $kartu = KartuRfid::where('kode_uid', $uid)->where('status', 'aktif')->first();
        if (! $kartu) {
            return $this->scanError('Kartu tidak dikenali atau tidak aktif.', 404);
        }
        $siswa = Siswa::whereKey($kartu->siswa_id)->where('status', 'aktif')->first();
        if (! $siswa) {
            return $this->scanError('Siswa tidak ditemukan atau tidak aktif.');
        }
        $anggota = RombelSiswa::with('rombel')->where('siswa_id', $siswa->id)->where('tanggal_masuk', '<=', $tanggal)->where(fn ($q) => $q->whereNull('tanggal_keluar')->orWhere('tanggal_keluar', '>=', $tanggal))->whereHas('rombel.tahunAjaran', fn ($q) => $q->where('is_aktif', true))->latest('tanggal_masuk')->first();
        if (! $anggota) {
            return $this->scanError('Siswa belum terdaftar di kelas pada Tahun Ajaran aktif.');
        }
        $jadwal = $this->jadwal($now);
        if (! $jadwal || ! $jadwal->is_aktif) {
            return $this->scanError('Presensi tidak aktif pada hari ini.');
        }
        if (! $jadwal->jam_mulai_masuk || ! $jadwal->batas_terlambat || ! $jadwal->jam_mulai_pulang) {
            Log::error('Jadwal presensi tidak lengkap', ['hari' => $now->isoWeekday()]);

            return $this->scanError('Jadwal presensi hari ini belum lengkap. Hubungi Admin.');
        }
        try {
            $result = DB::transaction(function () use ($siswa, $anggota, $jadwal, $now, $tanggal) {
                Siswa::whereKey($siswa->id)->lockForUpdate()->firstOrFail();
                $p = Presensi::where('siswa_id', $siswa->id)->whereDate('tanggal', $tanggal)->lockForUpdate()->first();
                $time = $now->format('H:i:s');
                if (! $p) {
                    $p = Presensi::create(['siswa_id' => $siswa->id, 'rombel_id' => $anggota->rombel_id, 'tanggal' => $tanggal, 'waktu_scan' => $time, 'metode' => 'rfid', 'waktu_masuk' => $time, 'status' => $time <= $jadwal->batas_terlambat ? 'hadir' : 'terlambat', 'metode_masuk' => 'rfid']);

                    return [$p, 'masuk', 200];
                }
                if (! $p->waktu_masuk || in_array($p->status, ['izin', 'sakit', 'alpa'], true)) {
                    return [$p, 'lengkap', 200];
                }
                if ($p->waktu_pulang) {
                    return [$p, 'lengkap', 200];
                }
                if ($time < $jadwal->jam_mulai_pulang) {
                    return [$p, 'terlalu_awal', 422];
                }
                $p->update(['waktu_pulang' => $time, 'status_pulang' => 'tepat_waktu', 'metode_pulang' => 'rfid']);

                return [$p, 'pulang', 200];
            });
        } catch (\Throwable $e) {
            report($e);

            return $this->scanError('Terjadi kesalahan sistem saat menyimpan presensi.', 500);
        }
        [$p,$action,$code] = $result;
        $messages = ['masuk' => 'Presensi masuk berhasil.', 'pulang' => 'Presensi pulang berhasil.', 'terlalu_awal' => 'Belum waktunya pulang. Jadwal pulang hari ini pukul '.substr($jadwal->jam_mulai_pulang, 0, 5).' WIB.', 'lengkap' => ! $p->waktu_masuk ? 'Presensi manual izin/sakit/alpa tidak dapat diproses melalui kiosk.' : 'Presensi hari ini sudah lengkap.'];

        return response()->json(['success' => ! in_array($action, ['terlalu_awal'], true), 'action' => $action, 'type' => $action, 'message' => $messages[$action], 'data' => ['nama' => $siswa->nama_lengkap, 'nis' => $siswa->nis, 'foto_url' => $siswa->foto_url ? asset('storage/'.$siswa->foto_url) : null, 'kelas' => $anggota->rombel->kelas_label, 'waktu_masuk' => $p->waktu_masuk, 'waktu_pulang' => $p->waktu_pulang, 'status' => $p->status, 'status_pulang' => $p->status_pulang, 'jadwal_pulang' => $jadwal->jam_mulai_pulang, 'pesan' => $messages[$action]]], $code);
    }

    public function store(Request $request)
    {
        $v = $request->validate(['rombel_id' => ['required', 'exists:rombel,id'], 'tanggal' => ['required', 'date_format:Y-m-d', 'before_or_equal:'.Carbon::now('Asia/Jakarta')->toDateString()], 'presensi' => ['required', 'array'], 'presensi.*.status' => ['required', Rule::in(['hadir', 'terlambat', 'izin', 'sakit', 'alpa'])], 'presensi.*.waktu_masuk' => ['nullable', 'date_format:H:i'], 'presensi.*.waktu_pulang' => ['nullable', 'date_format:H:i'], 'presensi.*.status_pulang' => ['nullable', Rule::in(['tepat_waktu', 'pulang_cepat'])], 'presensi.*.alasan_pulang_cepat' => ['nullable', 'string', 'max:255'], 'presensi.*.catatan_pulang' => ['nullable', 'string', 'max:1000']]);
        $rombel = $this->accessibleRombels($request)->findOrFail($v['rombel_id']);
        $anggota = RombelSiswa::where('rombel_id', $rombel->id)->whereHas('siswa', fn ($q) => $q->where('status', 'aktif'))->where('tanggal_masuk', '<=', $v['tanggal'])->where(fn ($q) => $q->whereNull('tanggal_keluar')->orWhere('tanggal_keluar', '>=', $v['tanggal']))->pluck('siswa_id')->map(fn ($id) => (string) $id)->sort()->values();
        if (collect(array_keys($v['presensi']))->map(fn ($id) => (string) $id)->sort()->values()->all() !== $anggota->all()) {
            return back()->withErrors(['presensi' => 'Data presensi harus memuat seluruh siswa aktif anggota kelas.'])->withInput();
        }
        $jadwal = $this->jadwal(Carbon::parse($v['tanggal'], 'Asia/Jakarta'));
        if (! $jadwal || ! $jadwal->is_aktif) {
            return back()->withErrors(['tanggal' => 'Presensi tidak aktif pada tanggal tersebut.'])->withInput();
        }
        $jamMulaiPulang = substr($jadwal->jam_mulai_pulang, 0, 5);
        $sekarangWib = Carbon::now('Asia/Jakarta');
        $tanggalHariIni = $v['tanggal'] === $sekarangWib->toDateString();
        $waktuSekarang = $sekarangWib->format('H:i');
        foreach ($v['presensi'] as $id => $row) {
            $hadir = in_array($row['status'], ['hadir', 'terlambat'], true);
            if ($tanggalHariIni && ! empty($row['waktu_masuk']) && $row['waktu_masuk'] > $waktuSekarang) {
                return back()->withErrors(["presensi.$id.waktu_masuk" => 'Waktu masuk tidak boleh melebihi waktu sekarang.'])->withInput();
            }if ($tanggalHariIni && ! empty($row['waktu_pulang']) && $row['waktu_pulang'] > $waktuSekarang) {
                return back()->withErrors(["presensi.$id.waktu_pulang" => 'Waktu pulang tidak boleh melebihi waktu sekarang.'])->withInput();
            }
            if (! $hadir && (! empty($row['waktu_masuk']) || ! empty($row['waktu_pulang']))) {
                return back()->withErrors(["presensi.$id.status" => 'Izin, sakit, atau alpa tidak boleh memiliki jam masuk/pulang.'])->withInput();
            }if ($hadir && ! $row['waktu_masuk']) {
                return back()->withErrors(["presensi.$id.waktu_masuk" => 'Waktu masuk wajib diisi untuk siswa hadir.'])->withInput();
            }if (! empty($row['status_pulang']) && empty($row['waktu_pulang'])) {
                return back()->withErrors(["presensi.$id.status_pulang" => 'Status pulang hanya boleh diisi jika waktu pulang diisi.'])->withInput();
            }if (! empty($row['waktu_pulang']) && $row['waktu_pulang'] <= $row['waktu_masuk']) {
                return back()->withErrors(["presensi.$id.waktu_pulang" => 'Waktu pulang harus setelah waktu masuk.'])->withInput();
            }if (! empty($row['waktu_pulang']) && (! $jadwal || ! $jadwal->jam_mulai_pulang)) {
                return back()->withErrors(["presensi.$id.waktu_pulang" => 'Jadwal pulang belum tersedia.'])->withInput();
            }if (! empty($row['waktu_pulang']) && $row['waktu_pulang'] < $jamMulaiPulang && empty($row['alasan_pulang_cepat'])) {
                return back()->withErrors(["presensi.$id.alasan_pulang_cepat" => 'Pulang cepat wajib memiliki alasan.'])->withInput();
            }
        }
        DB::transaction(function () use ($v, $rombel, $request, $jamMulaiPulang) {
            foreach (collect($v['presensi'])->sortKeys() as $id => $row) {
                Siswa::whereKey($id)->lockForUpdate()->firstOrFail();
                $presensi = Presensi::where('siswa_id', $id)->whereDate('tanggal', $v['tanggal'])->lockForUpdate()->first();
                $hadir = in_array($row['status'], ['hadir', 'terlambat'], true);
                $masuk = $hadir ? $row['waktu_masuk'].':00' : null;
                $pulang = $hadir && ! empty($row['waktu_pulang']) ? $row['waktu_pulang'].':00' : null;
                $statusPulang = $pulang ? ($row['waktu_pulang'] < $jamMulaiPulang ? 'pulang_cepat' : 'tepat_waktu') : null;
                $metodeMasuk = $presensi?->metode_masuk === 'rfid' && $presensi->waktu_masuk === $masuk ? 'rfid' : 'manual';
                $metodePulang = $pulang ? ($presensi?->metode_pulang === 'rfid' && $presensi->waktu_pulang === $pulang ? 'rfid' : 'manual') : null;

                $presensi ??= new Presensi(['siswa_id' => $id, 'tanggal' => $v['tanggal']]);
                $presensi->fill([
                    'rombel_id' => $rombel->id,
                    'status' => $row['status'],
                    'waktu_masuk' => $masuk,
                    'waktu_scan' => $presensi->metode === 'rfid' ? $presensi->waktu_scan : $masuk,
                    'metode' => $presensi->metode === 'rfid' ? 'rfid' : 'manual',
                    'metode_masuk' => $metodeMasuk,
                    'dicatat_oleh' => $request->user()->id,
                    'waktu_pulang' => $pulang,
                    'status_pulang' => $statusPulang,
                    'metode_pulang' => $metodePulang,
                    'alasan_pulang_cepat' => $statusPulang === 'pulang_cepat' ? $row['alasan_pulang_cepat'] : null,
                    'catatan_pulang' => $pulang ? ($row['catatan_pulang'] ?? null) : null,
                    'pulang_dicatat_oleh' => $pulang ? $request->user()->id : null,
                ])->save();
            }
        });

        return redirect()->route('presensi.index', ['tahun_ajaran_id' => $rombel->tahun_ajaran_id, 'rombel_id' => $rombel->id, 'tanggal' => $v['tanggal']])->with('success', 'Data presensi berhasil disimpan.');
    }

    private function jadwal(Carbon $tanggal): ?JadwalPresensi
    {
        $jadwal = JadwalPresensi::untukTanggal($tanggal);
        if ($jadwal) {
            return $jadwal;
        }
        $batas = config('presensi.batas_terlambat');
        $pulang = config('presensi.jam_mulai_pulang');
        if (! $batas || ! $pulang) {
            return null;
        }

        return new JadwalPresensi(['hari' => $tanggal->isoWeekday(), 'jam_mulai_masuk' => config('presensi.jam_mulai_masuk', '06:00'), 'batas_terlambat' => $batas, 'jam_mulai_pulang' => $pulang, 'is_aktif' => true]);
    }

    private function scanError(string $message, int $code = 422)
    {
        return response()->json(['success' => false, 'action' => 'error', 'type' => 'error', 'message' => $message], $code);
    }

    private function accessibleRombels(Request $request)
    {
        return Rombel::query()->when($request->user()->role === 'wali_kelas', fn ($q) => $q->whereHas('waliGuru', fn ($g) => $g->where('user_id', $request->user()->id)));
    }
}
