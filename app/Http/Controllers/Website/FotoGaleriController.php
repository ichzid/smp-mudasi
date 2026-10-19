<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\FotoGaleri;
use App\Models\Galeri;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FotoGaleriController extends Controller
{
    public function destroy(Galeri $galeri, $fotoGaleri)
    {
        $foto = $galeri->foto()->findOrFail($fotoGaleri);
        $path = $foto->foto;
        DB::transaction(function () use ($foto, $galeri, $path) {
            $foto->delete();
            if ($galeri->gambar_sampul === $path) {
                $galeri->update(['gambar_sampul' => $galeri->foto()->value('foto')]);
            }
        });

        if ($path && preg_match('~^website/galeri/[A-Za-z0-9]{40}\\.(?:jpe?g|png|webp)$~i', $path)
            && ! Galeri::where('gambar_sampul', $path)->exists()
            && ! FotoGaleri::where('foto', $path)->exists()) {
            Storage::disk('public')->delete($path);
        }

        return redirect()->route('website.galeri.edit', $galeri)->with('success', 'Foto galeri berhasil dihapus.');
    }
}
