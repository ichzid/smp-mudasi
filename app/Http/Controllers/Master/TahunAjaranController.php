<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TahunAjaranController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tahunAjarans = TahunAjaran::orderBy('nama', 'desc')->orderBy('semester', 'desc')->get();
        return view('pages.master.tahun-ajaran.index', compact('tahunAjarans'), ['title' => 'Data Tahun Ajaran']);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.master.tahun-ajaran.create', ['title' => 'Tambah Tahun Ajaran']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tahun_mulai' => 'required|integer|min:2020|max:2100',
            'tahun_selesai' => 'required|integer|min:2020|max:2100|gte:tahun_mulai',
            'semester' => 'required|in:ganjil,genap',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        DB::beginTransaction();
        try {
            if ($validated['status'] == 'aktif') {
                TahunAjaran::where('status', 'aktif')->update(['status' => 'nonaktif']);
            }

            TahunAjaran::create($validated);
            DB::commit();

            return redirect()->route('master.tahun-ajaran.index')->with('success', 'Tahun ajaran berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
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
        $tahunAjaran = TahunAjaran::findOrFail($id);
        return view('pages.master.tahun-ajaran.edit', compact('tahunAjaran'), ['title' => 'Edit Tahun Ajaran']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $tahunAjaran = TahunAjaran::findOrFail($id);

        $validated = $request->validate([
            'tahun_mulai' => 'required|integer|min:2020|max:2100',
            'tahun_selesai' => 'required|integer|min:2020|max:2100|gte:tahun_mulai',
            'semester' => 'required|in:ganjil,genap',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        DB::beginTransaction();
        try {
            if ($validated['status'] == 'aktif' && $tahunAjaran->status != 'aktif') {
                TahunAjaran::where('id', '!=', $id)->where('status', 'aktif')->update(['status' => 'nonaktif']);
            }

            $tahunAjaran->update($validated);
            DB::commit();

            return redirect()->route('master.tahun-ajaran.index')->with('success', 'Tahun ajaran berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $tahunAjaran = TahunAjaran::findOrFail($id);
        
        if ($tahunAjaran->status == 'aktif') {
            return redirect()->route('master.tahun-ajaran.index')->with('error', 'Tidak dapat menghapus tahun ajaran yang sedang aktif.');
        }

        $tahunAjaran->delete();
        return redirect()->route('master.tahun-ajaran.index')->with('success', 'Tahun ajaran berhasil dihapus.');
    }
}
