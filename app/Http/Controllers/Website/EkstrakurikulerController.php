<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Ekstrakurikuler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class EkstrakurikulerController extends Controller
{
    public function index()
    {
        return view('pages.website.ekstrakurikuler.index', [
            'ekstrakurikuler' => Ekstrakurikuler::orderByDesc('created_at')->orderByDesc('id')->get(),
        ]);
    }

    public function create()
    {
        return view('pages.website.ekstrakurikuler.form', [
            'ekstrakurikuler' => new Ekstrakurikuler(),
            'title' => 'Tambah Ekstrakurikuler',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'pembina' => 'nullable|string|max:255',
            'jadwal' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'aktif' => 'required|boolean',
        ]);

        $data['slug'] = $this->uniqueSlug($data['nama']);
        unset($data['gambar']);
        $path = null;
        try {
            if ($request->hasFile('gambar')) {
                $path = $request->file('gambar')->store('website/ekstrakurikuler', 'public');
                $data['gambar'] = $path;
            }
            Ekstrakurikuler::create($data);
        } catch (Throwable $e) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }
            throw $e;
        }

        return redirect()->route('website.ekstrakurikuler.index')->with('success', 'Ekstrakurikuler berhasil ditambahkan.');
    }

    public function edit(Ekstrakurikuler $ekstrakurikuler)
    {
        return view('pages.website.ekstrakurikuler.form', [
            'ekstrakurikuler' => $ekstrakurikuler,
            'title' => 'Edit Ekstrakurikuler',
        ]);
    }

    public function update(Request $request, Ekstrakurikuler $ekstrakurikuler)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'pembina' => 'nullable|string|max:255',
            'jadwal' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'hapus_gambar' => 'sometimes|boolean',
            'aktif' => 'required|boolean',
        ]);

        if ($data['nama'] !== $ekstrakurikuler->nama) {
            $data['slug'] = $this->uniqueSlug($data['nama'], $ekstrakurikuler->id);
        }
        unset($data['gambar'], $data['hapus_gambar']);
        $oldPath = $ekstrakurikuler->gambar;
        $path = null;
        try {
            if ($request->hasFile('gambar')) {
                $path = $request->file('gambar')->store('website/ekstrakurikuler', 'public');
                $data['gambar'] = $path;
            } elseif ($request->boolean('hapus_gambar')) {
                $data['gambar'] = null;
            }
            $ekstrakurikuler->update($data);
        } catch (Throwable $e) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }
            throw $e;
        }
        if ($oldPath !== $ekstrakurikuler->gambar) {
            $this->deleteOwnedFile($oldPath);
        }

        return redirect()->route('website.ekstrakurikuler.index')->with('success', 'Ekstrakurikuler berhasil diperbarui.');
    }

    public function destroy(Ekstrakurikuler $ekstrakurikuler)
    {
        $path = $ekstrakurikuler->gambar;
        $ekstrakurikuler->delete();
        $this->deleteOwnedFile($path);

        return redirect()->route('website.ekstrakurikuler.index')->with('success', 'Ekstrakurikuler berhasil dihapus.');
    }

    private function uniqueSlug(string $nama, ?int $exceptId = null): string
    {
        $base = Str::limit(Str::slug($nama) ?: 'ekstrakurikuler', 240, '');
        $slug = $base;
        $suffix = 2;
        while (Ekstrakurikuler::where('slug', $slug)->when($exceptId, fn ($query) => $query->whereKeyNot($exceptId))->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    private function deleteOwnedFile(?string $path): void
    {
        if ($path && preg_match('~^website/ekstrakurikuler/[A-Za-z0-9]{40}\\.(?:jpe?g|png|webp)$~i', $path)
            && ! Ekstrakurikuler::where('gambar', $path)->exists()) {
            Storage::disk('public')->delete($path);
        }
    }
}
