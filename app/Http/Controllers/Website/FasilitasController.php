<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Fasilitas;
use App\Models\FotoFasilitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class FasilitasController extends Controller
{
    public function index()
    {
        return view('pages.website.fasilitas.index', [
            'fasilitas' => Fasilitas::withCount('foto')->orderBy('urutan')->orderBy('id')->get(),
        ]);
    }

    public function create()
    {
        return view('pages.website.fasilitas.form', [
            'fasilitas' => new Fasilitas(),
            'title' => 'Tambah Fasilitas',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'gambar_baru' => 'nullable|array|max:20',
            'gambar_baru.*' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'urutan' => 'required|integer|min:0|max:4294967295',
            'aktif' => 'required|boolean',
        ]);

        $data['slug'] = $this->uniqueSlug($data['nama']);
        $files = $data['gambar_baru'] ?? [];
        unset($data['gambar_baru']);
        $paths = [];
        try {
            DB::transaction(function () use ($data, $files, &$paths) {
                $fasilitas = Fasilitas::create($data);
                $this->appendPhotos($fasilitas, $files, $paths);
            });
        } catch (Throwable $e) {
            Storage::disk('public')->delete($paths);
            throw $e;
        }

        return redirect()->route('website.fasilitas.index')->with('success', 'Fasilitas berhasil ditambahkan.');
    }

    public function edit(Fasilitas $fasilitas)
    {
        $fasilitas->load('foto');

        return view('pages.website.fasilitas.form', [
            'fasilitas' => $fasilitas,
            'title' => 'Edit Fasilitas',
        ]);
    }

    public function update(Request $request, Fasilitas $fasilitas)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'gambar_baru' => 'nullable|array|max:20',
            'gambar_baru.*' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'hapus_gambar_utama' => 'sometimes|boolean',
            'urutan' => 'required|integer|min:0|max:4294967295',
            'aktif' => 'required|boolean',
        ]);

        if ($data['nama'] !== $fasilitas->nama) {
            $data['slug'] = $this->uniqueSlug($data['nama'], $fasilitas->id);
        }
        $files = $data['gambar_baru'] ?? [];
        $hapusGambarUtama = (bool) ($data['hapus_gambar_utama'] ?? false);
        unset($data['gambar_baru'], $data['hapus_gambar_utama']);
        $oldPath = $fasilitas->gambar;
        $paths = [];
        try {
            DB::transaction(function () use ($fasilitas, $data, $files, $hapusGambarUtama, &$paths) {
                if ($hapusGambarUtama && $fasilitas->gambar && ! $fasilitas->foto()->where('foto', $fasilitas->gambar)->exists()) {
                    $data['gambar'] = $fasilitas->foto()->value('foto');
                }
                $fasilitas->update($data);
                $this->appendPhotos($fasilitas, $files, $paths);
            });
        } catch (Throwable $e) {
            Storage::disk('public')->delete($paths);
            throw $e;
        }

        if ($hapusGambarUtama && $oldPath !== $fasilitas->gambar
            && ! Fasilitas::where('gambar', $oldPath)->exists()
            && ! FotoFasilitas::where('foto', $oldPath)->exists()) {
            $this->deleteOwnedFile($oldPath);
        }

        return redirect()->route('website.fasilitas.index')->with('success', 'Fasilitas berhasil diperbarui.');
    }

    public function destroy(Fasilitas $fasilitas)
    {
        $paths = $fasilitas->foto()->pluck('foto')->all();
        $paths[] = $fasilitas->gambar;
        $fasilitas->delete();

        foreach ($paths as $path) {
            $this->deleteOwnedFile($path);
        }

        return redirect()->route('website.fasilitas.index')->with('success', 'Fasilitas berhasil dihapus.');
    }

    private function appendPhotos(Fasilitas $fasilitas, array $files, array &$paths): void
    {
        foreach ($files as $file) {
            $path = $file->store('website/fasilitas', 'public');
            if ($path === false) {
                throw new \RuntimeException('Gagal menyimpan foto fasilitas.');
            }
            $paths[] = $path;

            if (! $fasilitas->gambar) {
                $fasilitas->update(['gambar' => $path]);
            }

            $fasilitas->foto()->create(['foto' => $path]);
        }
    }

    private function uniqueSlug(string $nama, ?int $exceptId = null): string
    {
        $base = Str::limit(Str::slug($nama) ?: 'fasilitas', 240, '');
        $slug = $base;
        $suffix = 2;
        while (Fasilitas::where('slug', $slug)->when($exceptId, fn ($query) => $query->whereKeyNot($exceptId))->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    private function deleteOwnedFile(?string $path): void
    {
        if ($path && preg_match('~^website/fasilitas/[A-Za-z0-9]{40}\\.(?:jpe?g|png|webp)$~i', $path)
            && ! Fasilitas::where('gambar', $path)->exists()
            && ! FotoFasilitas::where('foto', $path)->exists()) {
            Storage::disk('public')->delete($path);
        }
    }
}
