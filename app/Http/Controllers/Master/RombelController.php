<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Rombel;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RombelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tahun_ajarans = TahunAjaran::latest()->get();
        $tahun_ajaran_id = $request->input('tahun_ajaran_id')
            ?: optional($tahun_ajarans->firstWhere('is_aktif', true))->id;

        $query = Rombel::with(['tahunAjaran', 'waliGuru'])->latest();

        if ($tahun_ajaran_id) {
            $query->where('tahun_ajaran_id', $tahun_ajaran_id);
        }

        $rombels = $tahun_ajaran_id ? $query->get() : collect();

        return view('pages.master.rombel.index', compact('rombels', 'tahun_ajarans', 'tahun_ajaran_id'), ['title' => 'Data Kelas']);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tahun_ajarans = TahunAjaran::where('is_aktif', true)->latest()->get();
        abort_if($tahun_ajarans->isEmpty(), 403, 'Tidak ada periode aktif untuk menambah kelas.');
        $gurus = Guru::orderBy('nama_lengkap')->get();

        return view('pages.master.rombel.create', compact('tahun_ajarans', 'gurus'), ['title' => 'Tambah Data Kelas']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tahun_ajaran_id' => ['required', Rule::exists('tahun_ajaran', 'id')->where('is_aktif', true)],
            'tingkat' => 'required|in:7,8,9',
            'nama' => 'required|string|max:50',
            'wali_guru_id' => [
                'required',
                'exists:guru,id',
                function ($attribute, $value, $fail) use ($request) {
                    $exists = Rombel::where('tahun_ajaran_id', $request->tahun_ajaran_id)
                        ->whereNull('semester')
                        ->where('wali_guru_id', $value)
                        ->exists();
                    if ($exists) {
                        $fail('Guru ini sudah menjadi wali kelas di kelas lain pada tahun ajaran yang dipilih.');
                    }
                },
            ],
        ]);

        Rombel::create($validated + ['semester' => null]);

        return redirect()->route('master.rombel.index')->with('success', 'Data kelas berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $rombel = Rombel::with(['tahunAjaran', 'waliGuru'])->findOrFail($id);

        return view('pages.master.rombel.show', compact('rombel'), ['title' => 'Detail Data Kelas']);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $rombel = Rombel::with('tahunAjaran')->findOrFail($id);
        abort_unless($rombel->tahunAjaran->is_aktif && $rombel->semester === null, 403, 'Kelas arsip atau snapshot historis tidak dapat diedit.');
        $tahun_ajarans = TahunAjaran::where('is_aktif', true)->get();
        $gurus = Guru::orderBy('nama_lengkap')->get();

        return view('pages.master.rombel.edit', compact('rombel', 'tahun_ajarans', 'gurus'), ['title' => 'Edit Data Kelas']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $rombel = Rombel::with('tahunAjaran')->findOrFail($id);
        abort_unless($rombel->tahunAjaran->is_aktif && $rombel->semester === null, 403, 'Kelas arsip atau snapshot historis tidak dapat diedit.');

        $validated = $request->validate([
            'tahun_ajaran_id' => ['required', Rule::in([$rombel->tahun_ajaran_id])],
            'tingkat' => 'required|in:7,8,9',
            'nama' => 'required|string|max:50',
            'wali_guru_id' => [
                'required',
                'exists:guru,id',
                function ($attribute, $value, $fail) use ($request, $id) {
                    $exists = Rombel::where('tahun_ajaran_id', $request->tahun_ajaran_id)
                        ->whereNull('semester')
                        ->where('wali_guru_id', $value)
                        ->where('id', '!=', $id)
                        ->exists();
                    if ($exists) {
                        $fail('Guru ini sudah menjadi wali kelas di kelas lain pada tahun ajaran yang dipilih.');
                    }
                },
            ],
        ]);

        $rombel->update($validated);

        return redirect()->route('master.rombel.index')->with('success', 'Data kelas berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $rombel = Rombel::with('tahunAjaran')->findOrFail($id);
        abort_unless($rombel->tahunAjaran->is_aktif && $rombel->semester === null, 403, 'Kelas arsip atau snapshot historis tidak dapat dihapus.');
        abort_if($rombel->siswa()->exists(), 403, 'Kelas yang memiliki riwayat anggota tidak dapat dihapus.');
        $rombel->delete();

        return redirect()->route('master.rombel.index')->with('success', 'Data kelas berhasil dihapus.');
    }
}
