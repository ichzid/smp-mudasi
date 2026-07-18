<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
            'nama' => [
                'required',
                'string',
                'max:50',
                Rule::unique('tahun_ajaran')->where(fn ($query) => $query->where('semester', $request->input('semester'))),
            ],
            'semester' => 'required|in:1,2',
            'is_aktif' => 'required|boolean',
        ]);

        DB::beginTransaction();
        try {
            TahunAjaran::query()->lockForUpdate()->get();

            if ($validated['is_aktif']) {
                TahunAjaran::where('is_aktif', true)->update(['is_aktif' => false]);
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
            'nama' => [
                'required',
                'string',
                'max:50',
                Rule::unique('tahun_ajaran')->where(fn ($query) => $query->where('semester', $request->input('semester')))->ignore($tahunAjaran->id),
            ],
            'semester' => 'required|in:1,2',
            'is_aktif' => 'required|boolean',
        ]);

        DB::beginTransaction();
        try {
            $tahunAjarans = TahunAjaran::query()->lockForUpdate()->get();
            $tahunAjaran = $tahunAjarans->firstWhere('id', (int) $id) ?? TahunAjaran::findOrFail($id);

            if ($validated['is_aktif']) {
                TahunAjaran::where('id', '!=', $id)->where('is_aktif', true)->update(['is_aktif' => false]);
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
        
        if ($tahunAjaran->is_aktif) {
            return redirect()->route('master.tahun-ajaran.index')->with('error', 'Tidak dapat menghapus tahun ajaran yang sedang aktif.');
        }

        $tahunAjaran->delete();
        return redirect()->route('master.tahun-ajaran.index')->with('success', 'Tahun ajaran berhasil dihapus.');
    }
}
