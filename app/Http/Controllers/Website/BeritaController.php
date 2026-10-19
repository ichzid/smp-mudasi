<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Berita;
use App\Models\KategoriBerita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use DOMDocument;
use DOMNode;
use DOMElement;

class BeritaController extends Controller
{
    public function index()
    {
        $berita = Berita::with('kategori')->orderBy('created_at', 'desc')->get();
        return view('pages.website.berita.index', [
            'berita' => $berita,
            'title' => 'Berita & Informasi'
        ]);
    }

    public function create()
    {
        $kategori = KategoriBerita::all();
        return view('pages.website.berita.form', [
            'berita' => new Berita(),
            'kategori' => $kategori,
            'editorContent' => $this->sanitizeContent(old('isi', '')),
            'title' => 'Tambah Berita'
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'kategori_berita_id' => 'nullable|exists:kategori_berita,id',
            'isi' => 'required|string',
            'status' => 'required|in:draft,published',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $data = $request->only('judul', 'kategori_berita_id', 'isi', 'status');
        $data['isi'] = $this->sanitizeContent($data['isi']);
        $this->validateContent($data['isi']);
        $data['slug'] = $this->uniqueSlug($data['judul']);
        
        if ($request->status == 'published') {
            $data['diterbitkan_pada'] = now();
        }

        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('website/berita', 'public');
        }

        Berita::create($data);

        return redirect()->route('website.berita.index')
            ->with('success', 'Berita berhasil ditambahkan.');
    }

    public function edit(Berita $beritum)
    {
        $kategori = KategoriBerita::all();
        return view('pages.website.berita.form', [
            'berita' => $beritum,
            'kategori' => $kategori,
            'editorContent' => $this->sanitizeContent(old('isi', $beritum->isi ?? '')),
            'title' => 'Edit Berita'
        ]);
    }

    public function update(Request $request, Berita $beritum)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'kategori_berita_id' => 'nullable|exists:kategori_berita,id',
            'isi' => 'required|string',
            'status' => 'required|in:draft,published',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'hapus_gambar' => 'sometimes|boolean',
        ]);

        $data = $request->only('judul', 'kategori_berita_id', 'isi', 'status');
        $data['isi'] = $this->sanitizeContent($data['isi']);
        $this->validateContent($data['isi']);
        if ($data['judul'] !== $beritum->judul) {
            $data['slug'] = $this->uniqueSlug($data['judul'], $beritum->id);
        }

        if ($request->status == 'published' && $beritum->status == 'draft') {
            $data['diterbitkan_pada'] = now();
        } elseif ($request->status == 'draft') {
            $data['diterbitkan_pada'] = null;
        }

        $oldPath = $beritum->gambar;
        $newPath = null;
        if ($request->hasFile('gambar')) {
            $newPath = $request->file('gambar')->store('website/berita', 'public');
            $data['gambar'] = $newPath;
        } elseif ($request->boolean('hapus_gambar')) {
            $data['gambar'] = null;
        }

        try {
            $beritum->update($data);
        } catch (\Throwable $exception) {
            if ($newPath) {
                Storage::disk('public')->delete($newPath);
            }
            throw $exception;
        }

        if ($oldPath && $oldPath !== $beritum->gambar
            && preg_match('~^website/berita/[A-Za-z0-9]{40}\\.(?:jpe?g|png|webp)$~i', $oldPath)
            && ! Berita::where('gambar', $oldPath)->exists()) {
            Storage::disk('public')->delete($oldPath);
        }

        return redirect()->route('website.berita.index')
            ->with('success', 'Berita berhasil diperbarui.');
    }

    public function destroy(Berita $beritum)
    {
        if ($beritum->gambar && str_starts_with($beritum->gambar, 'website/')) {
            Storage::disk('public')->delete($beritum->gambar);
        }
        $beritum->delete();

        return redirect()->route('website.berita.index')
            ->with('success', 'Berita berhasil dihapus.');
    }

    private function uniqueSlug(string $judul, ?int $exceptId = null): string
    {
        $base = Str::slug($judul) ?: 'berita';
        $base = Str::limit($base, 240, '');
        $slug = $base;
        $suffix = 2;

        while (Berita::where('slug', $slug)->when($exceptId, fn ($query) => $query->whereKeyNot($exceptId))->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    private function sanitizeContent(string $content): string
    {
        if (trim($content) === '') {
            return '';
        }

        $html = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $html->loadHTML('<?xml encoding="utf-8" ?><div id="editor-content">'.$content.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $wrapper = $html->getElementById('editor-content');
        if (!$wrapper) {
            return '';
        }

        $allowedTags = ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'ul', 'ol', 'li', 'blockquote', 'h1', 'h2', 'h3', 'h4', 'a', 'img'];
        $allowedAttributes = ['a' => ['href', 'target', 'rel'], 'img' => ['src', 'alt']];
        $this->sanitizeNode($wrapper, $allowedTags, $allowedAttributes);

        $clean = '';
        foreach ($wrapper->childNodes as $node) {
            $clean .= $html->saveHTML($node);
        }

        return trim($clean);
    }

    private function sanitizeNode(DOMNode $node, array $allowedTags, array $allowedAttributes): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if (!$child instanceof DOMElement) {
                continue;
            }

            if (!in_array(strtolower($child->tagName), $allowedTags, true)) {
                while ($child->firstChild) {
                    $child->parentNode->insertBefore($child->firstChild, $child);
                }
                $child->parentNode->removeChild($child);
                $this->sanitizeNode($node, $allowedTags, $allowedAttributes);
                continue;
            }

            foreach (iterator_to_array($child->attributes) as $attribute) {
                if (!in_array(strtolower($attribute->name), $allowedAttributes[strtolower($child->tagName)] ?? [], true)) {
                    $child->removeAttribute($attribute->name);
                }
            }

            if ($child->tagName === 'a') {
                $href = $child->getAttribute('href');
                if (!preg_match('~^(https?://|mailto:|/)~i', $href)) {
                    $child->removeAttribute('href');
                }
                $child->setAttribute('rel', 'noopener noreferrer');
            }

            if ($child->tagName === 'img') {
                $src = $child->getAttribute('src');
                if (!preg_match('~^(https?://|/)~i', $src)) {
                    $child->parentNode->removeChild($child);
                    continue;
                }
            }

            $this->sanitizeNode($child, $allowedTags, $allowedAttributes);
        }
    }

    private function validateContent(string $content): void
    {
        if (trim(strip_tags($content)) === '' && !str_contains($content, '<img')) {
            throw ValidationException::withMessages(['isi' => 'Isi berita wajib diisi.']);
        }
    }
}
