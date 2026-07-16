<?php

namespace App\Http\Controllers;

use App\Models\RombelSiswa;
use App\Models\Rombel;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RombelSiswaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tahun_ajaran_id = $request->input('tahun_ajaran_id');
        $rombel_id = $request->input('rombel_id');
        
        $tahun_ajarans = TahunAjaran::latest()->get();
        $rombels = collect();
        $rombel_siswas = collect();

        if ($tahun_ajaran_id) {
            $rombels = Rombel::where('tahun_ajaran_id', $tahun_ajaran_id)->get();
        }

        if ($rombel_id) {
            $rombel_siswas = RombelSiswa::with(['siswa', 'rombel.tahunAjaran'])
                                        ->where('rombel_id', $rombel_id)
                                        ->orderBy(Siswa::select('nama_lengkap')->whereColumn('siswas.id', 'rombel_siswas.siswa_id'))
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
        
        // Cari siswa yang belum memiliki rombel di tahun ajaran ini
        $siswa_terdaftar = RombelSiswa::whereHas('rombel', function($q) use ($rombel) {
            $q->where('tahun_ajaran_id', $rombel->tahun_ajaran_id);
        })->pluck('siswa_id');

        $siswas = Siswa::whereNotIn('id', $siswa_terdaftar)->orderBy('nama_lengkap')->get();

        return view('pages.rombel-siswa.create', compact('rombel', 'siswas'), ['title' => 'Tambah Anggota Rombel']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'rombel_id' => 'required|exists:rombels,id',
            'siswa_id' => 'required|array',
            'siswa_id.*' => 'exists:siswas,id',
            'tanggal_masuk' => 'required|date'
        ]);

        $rombel = Rombel::findOrFail($validated['rombel_id']);
        
        DB::beginTransaction();
        try {
            foreach ($validated['siswa_id'] as $siswaId) {
                // Double check if student is already in a rombel this academic year
                $exists = RombelSiswa::whereHas('rombel', function($q) use ($rombel) {
                                            $q->where('tahun_ajaran_id', $rombel->tahun_ajaran_id);
                                        })
                                        ->where('siswa_id', $siswaId)
                                        ->exists();
                
                if (!$exists) {
                    RombelSiswa::create([
                        'rombel_id' => $rombel->id,
                        'siswa_id' => $siswaId,
                        'tanggal_masuk' => $validated['tanggal_masuk'],
                    ]);
                }
            }
            DB::commit();
            return redirect()->route('rombel-siswa.index', ['tahun_ajaran_id' => $rombel->tahun_ajaran_id, 'rombel_id' => $rombel->id])
                             ->with('success', 'Berhasil menambahkan siswa ke rombel.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan saat menyimpan data.');
        }
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
    public function destroy(string $id)
    {
        $rombelSiswa = RombelSiswa::with('rombel')->findOrFail($id);
        $rombelId = $rombelSiswa->rombel_id;
        $taId = $rombelSiswa->rombel->tahun_ajaran_id;
        
        $rombelSiswa->delete();

        return redirect()->route('rombel-siswa.index', ['tahun_ajaran_id' => $taId, 'rombel_id' => $rombelId])
                         ->with('success', 'Siswa berhasil dikeluarkan dari rombel.');
    }
}
