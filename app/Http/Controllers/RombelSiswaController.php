<?php

namespace App\Http\Controllers;

use App\Models\RombelSiswa;
use App\Models\Rombel;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RombelSiswaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tahun_ajarans = TahunAjaran::latest()->get();
        $tahun_ajaran_id = $request->input('tahun_ajaran_id')
            ?: optional($tahun_ajarans->firstWhere('is_aktif', true))->id;
        $rombel_id = $request->input('rombel_id');
        
        $rombels = collect();
        $rombel_siswas = collect();

        if ($tahun_ajaran_id) {
            $rombels = Rombel::where('tahun_ajaran_id', $tahun_ajaran_id)
                ->orderByRaw("CASE tingkat WHEN '7' THEN 1 WHEN '8' THEN 2 WHEN '9' THEN 3 ELSE 4 END")
                ->orderByRaw('LENGTH(nama)')
                ->orderBy('nama')
                ->get();
        }

        if ($rombel_id) {
            $rombel = $rombels->firstWhere('id', (int) $rombel_id);
            abort_unless($rombel, 404);

            $rombel_siswas = RombelSiswa::with(['siswa', 'rombel.tahunAjaran'])
                ->where('rombel_id', $rombel_id)
                ->orderByRaw('tanggal_keluar IS NOT NULL')
                ->orderBy(Siswa::select('nama_lengkap')->whereColumn('siswa.id', 'rombel_siswa.siswa_id'))
                ->get();
        }

        return view('pages.rombel-siswa.index', compact('tahun_ajarans', 'rombels', 'rombel_siswas', 'tahun_ajaran_id', 'rombel_id'), ['title' => 'Manajemen Rombel Siswa']);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $rombel_id = $request->input('rombel_id');
        $rombel = Rombel::with('tahunAjaran')->findOrFail($rombel_id);
        
        $siswaAktifDiTahunAjaran = RombelSiswa::whereNull('tanggal_keluar')
            ->whereHas('rombel', function ($query) use ($rombel) {
                $query->where('tahun_ajaran_id', $rombel->tahun_ajaran_id);
            })
            ->pluck('siswa_id');

        $siswas = Siswa::where('status', 'aktif')
            ->whereNotIn('id', $siswaAktifDiTahunAjaran)
            ->orderBy('nama_lengkap')
            ->get();

        return view('pages.rombel-siswa.create', compact('rombel', 'siswas'), ['title' => 'Tambah Anggota Rombel']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'rombel_id' => ['required', 'exists:rombel,id'],
            'siswa_id' => ['required', 'array', 'min:1'],
            'siswa_id.*' => ['required', 'distinct', 'exists:siswa,id'],
            'tanggal_masuk' => ['required', 'date'],
        ]);

        $rombel = Rombel::findOrFail($validated['rombel_id']);

        DB::transaction(function () use ($validated, $rombel) {
            $siswaIds = collect($validated['siswa_id'])->map(fn ($id) => (int) $id)->unique()->values();
            $siswas = Siswa::whereIn('id', $siswaIds)->lockForUpdate()->get()->keyBy('id');

            foreach ($siswaIds as $siswaId) {
                $siswa = $siswas->get($siswaId);
                if (!$siswa || $siswa->status !== 'aktif') {
                    throw ValidationException::withMessages([
                        'siswa_id' => 'Hanya siswa berstatus aktif yang dapat ditambahkan.',
                    ]);
                }

                $keanggotaan = RombelSiswa::query()
                    ->where('siswa_id', $siswaId)
                    ->whereHas('rombel', fn ($query) => $query->where('tahun_ajaran_id', $rombel->tahun_ajaran_id))
                    ->lockForUpdate()
                    ->get();

                $overlap = $keanggotaan->contains(function (RombelSiswa $anggota) use ($validated) {
                    return $anggota->tanggal_keluar === null
                        || $anggota->tanggal_keluar->gte($validated['tanggal_masuk']);
                });

                if ($overlap) {
                    throw ValidationException::withMessages([
                        'siswa_id' => "Keanggotaan {$siswa->nama_lengkap} pada tahun ajaran ini belum ditutup atau tanggalnya bertumpang tindih.",
                    ]);
                }

                RombelSiswa::create([
                    'rombel_id' => $rombel->id,
                    'siswa_id' => $siswaId,
                    'tanggal_masuk' => $validated['tanggal_masuk'],
                ]);
            }
        });

        return redirect()->route('rombel-siswa.index', ['tahun_ajaran_id' => $rombel->tahun_ajaran_id, 'rombel_id' => $rombel->id])
            ->with('success', 'Berhasil menambahkan siswa ke rombel.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $validated = $request->validate([
            'tanggal_keluar' => ['required', 'date'],
        ]);

        $rombelSiswa = DB::transaction(function () use ($id, $validated) {
            $anggota = RombelSiswa::with('rombel')->lockForUpdate()->findOrFail($id);

            if ($anggota->tanggal_keluar !== null) {
                throw ValidationException::withMessages([
                    'tanggal_keluar' => 'Keanggotaan siswa ini sudah ditutup.',
                ]);
            }

            if ($anggota->tanggal_masuk->gt($validated['tanggal_keluar'])) {
                throw ValidationException::withMessages([
                    'tanggal_keluar' => 'Tanggal keluar tidak boleh sebelum tanggal masuk.',
                ]);
            }

            $anggota->update(['tanggal_keluar' => $validated['tanggal_keluar']]);

            return $anggota;
        });

        return redirect()->route('rombel-siswa.index', [
            'tahun_ajaran_id' => $rombelSiswa->rombel->tahun_ajaran_id,
            'rombel_id' => $rombelSiswa->rombel_id,
        ])->with('success', 'Keanggotaan siswa berhasil ditutup tanpa menghapus histori.');
    }
}
