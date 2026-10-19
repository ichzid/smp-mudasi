<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GuruController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $gurus = Guru::orderBy('nama_lengkap')->get();

        return view('pages.master.guru.index', compact('gurus'), ['title' => 'Data Guru']);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.master.guru.create', ['title' => 'Tambah Data Guru']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomor_indentitas' => 'nullable|string|max:30|unique:guru,nomor_indentitas',
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'tanggal_lahir' => 'nullable|date',
            'jabatan' => 'required|string|max:255',
            'bidang_tugas' => 'nullable|string|max:255',
            'biografi_singkat' => 'nullable|string',
            'urutan' => 'nullable|integer|min:0',
            'aktif' => 'nullable|boolean',
            'no_hp' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'foto.image' => 'File yang dipilih harus berupa gambar.',
            'foto.mimes' => 'Foto harus berformat JPG, JPEG, atau PNG.',
            'foto.max' => 'Ukuran foto tidak boleh lebih dari 2 MB.',
        ]);

        $validated['aktif'] = $request->boolean('aktif');

        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('foto_guru', 'public');
            $validated['foto'] = $fotoPath;
        }

        Guru::create($validated);

        return redirect()->route('master.guru.index')->with('success', 'Data guru berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $guru = Guru::findOrFail($id);

        return view('pages.master.guru.show', compact('guru'), ['title' => 'Detail Guru']);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $guru = Guru::findOrFail($id);

        return view('pages.master.guru.edit', compact('guru'), ['title' => 'Edit Data Guru']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $guru = Guru::findOrFail($id);

        $validated = $request->validate([
            'nomor_indentitas' => 'nullable|string|max:30|unique:guru,nomor_indentitas,'.$guru->id,
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'tanggal_lahir' => 'nullable|date',
            'jabatan' => 'required|string|max:255',
            'bidang_tugas' => 'nullable|string|max:255',
            'biografi_singkat' => 'nullable|string',
            'urutan' => 'nullable|integer|min:0',
            'aktif' => 'nullable|boolean',
            'no_hp' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'foto.image' => 'File yang dipilih harus berupa gambar.',
            'foto.mimes' => 'Foto harus berformat JPG, JPEG, atau PNG.',
            'foto.max' => 'Ukuran foto tidak boleh lebih dari 2 MB.',
        ]);

        $validated['aktif'] = $request->boolean('aktif');

        if ($request->hasFile('foto')) {
            if ($guru->foto && Storage::disk('public')->exists($guru->foto)) {
                Storage::disk('public')->delete($guru->foto);
            }
            $fotoPath = $request->file('foto')->store('foto_guru', 'public');
            $validated['foto'] = $fotoPath;
        }

        $guru->update($validated);

        return redirect()->route('master.guru.index')->with('success', 'Data guru berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $guru = Guru::findOrFail($id);

        // Cek jika guru menjadi wali kelas (opsional tapi disarankan)
        if ($guru->rombel()->exists()) {
            return redirect()->route('master.guru.index')->with('error', 'Guru tidak dapat dihapus karena sedang menjadi Wali Kelas.');
        }

        if ($guru->foto && Storage::disk('public')->exists($guru->foto)) {
            Storage::disk('public')->delete($guru->foto);
        }

        $guru->delete();

        return redirect()->route('master.guru.index')->with('success', 'Data guru berhasil dihapus.');
    }
}
