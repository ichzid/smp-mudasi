<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SiswaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $siswas = Siswa::latest()->get();

        return view('pages.master.siswa.index', compact('siswas'), ['title' => 'Data Siswa']);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.master.siswa.create', ['title' => 'Tambah Data Siswa']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nisn' => 'nullable|string|unique:siswa,nisn',
            'nis' => 'required|string|unique:siswa,nis',
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'nullable|string',
            'tanggal_lahir' => 'nullable|date',
            'alamat' => 'nullable|string',
            'nama_wali' => 'nullable|string',
            'no_hp_wali' => 'nullable|string',
            'foto_url' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status' => 'required|in:aktif,lulus,pindah,keluar',
        ]);

        if ($request->hasFile('foto_url')) {
            $fotoPath = $request->file('foto_url')->store('foto_siswa', 'public');
            $validated['foto_url'] = $fotoPath;
        }

        Siswa::create($validated);

        return redirect()->route('master.siswa.index')->with('success', 'Data siswa berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $siswa = Siswa::findOrFail($id);

        return view('pages.master.siswa.show', compact('siswa'), ['title' => 'Detail Siswa']);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $siswa = Siswa::findOrFail($id);

        return view('pages.master.siswa.edit', compact('siswa'), ['title' => 'Edit Data Siswa']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $siswa = Siswa::findOrFail($id);

        $validated = $request->validate([
            'nisn' => 'nullable|string|unique:siswa,nisn,'.$siswa->id,
            'nis' => 'required|string|unique:siswa,nis,'.$siswa->id,
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'nullable|string',
            'tanggal_lahir' => 'nullable|date',
            'alamat' => 'nullable|string',
            'nama_wali' => 'nullable|string',
            'no_hp_wali' => 'nullable|string',
            'foto_url' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status' => 'required|in:aktif,lulus,pindah,keluar',
        ]);

        if ($request->hasFile('foto_url')) {
            if ($siswa->foto_url && Storage::disk('public')->exists($siswa->foto_url)) {
                Storage::disk('public')->delete($siswa->foto_url);
            }
            $fotoPath = $request->file('foto_url')->store('foto_siswa', 'public');
            $validated['foto_url'] = $fotoPath;
        }

        $siswa->update($validated);

        return redirect()->route('master.siswa.index')->with('success', 'Data siswa berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $siswa = Siswa::findOrFail($id);

        if ($siswa->foto_url && Storage::disk('public')->exists($siswa->foto_url)) {
            Storage::disk('public')->delete($siswa->foto_url);
        }

        $siswa->delete();

        return redirect()->route('master.siswa.index')->with('success', 'Data siswa berhasil dihapus.');
    }
}
