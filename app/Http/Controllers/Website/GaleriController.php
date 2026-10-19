<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\FotoGaleri;
use App\Models\Galeri;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class GaleriController extends Controller
{
    public function index()
    {
        return view('pages.website.galeri.index', [
            'galeri' => Galeri::withCount('foto')->latest()->get(),
        ]);
    }

    public function create()
    {
        return view('pages.website.galeri.form', [
            'galeri' => new Galeri(),
            'title' => 'Tambah Album Galeri',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'gambar_baru' => 'nullable|array|max:20',
            'gambar_baru.*' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $data['slug'] = $this->uniqueSlug($data['judul']);
        $files = $data['gambar_baru'] ?? [];
        unset($data['gambar_baru']);
        $paths = [];
        try {
            DB::transaction(function () use ($data, $files, &$paths) {
                $galeri = Galeri::create($data);
                $this->appendPhotos($galeri, $files, $paths);
            });
        } catch (Throwable $e) {
            Storage::disk('public')->delete($paths);
            throw $e;
        }

        return redirect()->route('website.galeri.index')->with('success', 'Album galeri berhasil ditambahkan.');
    }

    public function edit(Galeri $galeri)
    {
        $galeri->load('foto');

        return view('pages.website.galeri.form', [
            'galeri' => $galeri,
            'title' => 'Edit Album Galeri',
        ]);
    }

    public function update(Request $request, Galeri $galeri)
    {
        $data = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'gambar_baru' => 'nullable|array|max:20',
            'gambar_baru.*' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'hapus_gambar_utama' => 'sometimes|boolean',
        ]);

        if ($data['judul'] !== $galeri->judul) {
            $data['slug'] = $this->uniqueSlug($data['judul'], $galeri->id);
        }
        $files = $data['gambar_baru'] ?? [];
        $hapusGambarUtama = (bool) ($data['hapus_gambar_utama'] ?? false);
        unset($data['gambar_baru'], $data['hapus_gambar_utama']);
        $oldPath = $galeri->gambar_sampul;
        $paths = [];
        try {
            DB::transaction(function () use ($galeri, $data, $files, $hapusGambarUtama, &$paths) {
                if ($hapusGambarUtama && $galeri->gambar_sampul && ! $galeri->foto()->where('foto', $galeri->gambar_sampul)->exists()) {
                    $data['gambar_sampul'] = $galeri->foto()->value('foto');
                }
                $galeri->update($data);
                $this->appendPhotos($galeri, $files, $paths);
            });
        } catch (Throwable $e) {
            Storage::disk('public')->delete($paths);
            throw $e;
        }

        if ($hapusGambarUtama && $oldPath !== $galeri->gambar_sampul) {
            $this->deleteOwnedFile($oldPath);
        }

        return redirect()->route('website.galeri.index')->with('success', 'Album galeri berhasil diperbarui.');
    }

    public function destroy(Galeri $galeri)
    {
        $paths = $galeri->foto()->pluck('foto')->all();
        $paths[] = $galeri->gambar_sampul;
        $galeri->delete();

        foreach ($paths as $path) {
            $this->deleteOwnedFile($path);
        }

        return redirect()->route('website.galeri.index')->with('success', 'Album galeri dan foto-fotonya berhasil dihapus.');
    }

    private function appendPhotos(Galeri $galeri, array $files, array &$paths): void
    {
        foreach ($files as $file) {
            $path = $file->store('website/galeri', 'public');
            if ($path === false) {
                throw new \RuntimeException('Gagal menyimpan foto galeri.');
            }
            $paths[] = $path;
            if (! $galeri->gambar_sampul) {
                $galeri->update(['gambar_sampul' => $path]);
            }
            $galeri->foto()->create(['foto' => $path]);
        }
    }

    private function uniqueSlug(string $judul, ?int $exceptId = null): string
    {
        $base = Str::limit(Str::slug($judul) ?: 'galeri', 240, '');
        $slug = $base;
        $suffix = 2;
        while (Galeri::where('slug', $slug)->when($exceptId, fn ($query) => $query->whereKeyNot($exceptId))->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    private function deleteOwnedFile(?string $path): void
    {
        if ($path && preg_match('~^website/galeri/[A-Za-z0-9]{40}\\.(?:jpe?g|png|webp)$~i', $path)
            && ! Galeri::where('gambar_sampul', $path)->exists()
            && ! FotoGaleri::where('foto', $path)->exists()) {
            Storage::disk('public')->delete($path);
        }
    }
}
