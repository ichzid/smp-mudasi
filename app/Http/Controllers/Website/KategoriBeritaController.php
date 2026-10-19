<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\KategoriBerita;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KategoriBeritaController extends Controller
{
    public function index()
    {
        return view('pages.website.kategori-berita.index', [
            'kategori' => KategoriBerita::withCount('berita')->orderBy('nama')->get(),
        ]);
    }

    public function create()
    {
        return view('pages.website.kategori-berita.form', [
            'kategori' => new KategoriBerita(),
            'title' => 'Tambah Kategori Berita',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        $data['slug'] = $this->uniqueSlug($data['nama']);
        KategoriBerita::create($data);

        return redirect()->route('website.kategori-berita.index')->with('success', 'Kategori berita berhasil ditambahkan.');
    }

    public function edit(KategoriBerita $kategoriBeritum)
    {
        return view('pages.website.kategori-berita.form', [
            'kategori' => $kategoriBeritum,
            'title' => 'Edit Kategori Berita',
        ]);
    }

    public function update(Request $request, KategoriBerita $kategoriBeritum)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        if ($data['nama'] !== $kategoriBeritum->nama) {
            $data['slug'] = $this->uniqueSlug($data['nama'], $kategoriBeritum->id);
        }

        $kategoriBeritum->update($data);

        return redirect()->route('website.kategori-berita.index')->with('success', 'Kategori berita berhasil diperbarui.');
    }

    public function destroy(KategoriBerita $kategoriBeritum)
    {
        $kategoriBeritum->delete();

        return redirect()->route('website.kategori-berita.index')->with('success', 'Kategori berita berhasil dihapus.');
    }

    private function uniqueSlug(string $nama, ?int $exceptId = null): string
    {
        $base = Str::slug($nama) ?: 'kategori';
        $base = Str::limit($base, 240, '');
        $slug = $base;
        $suffix = 2;

        while (KategoriBerita::where('slug', $slug)->when($exceptId, fn ($query) => $query->whereKeyNot($exceptId))->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
