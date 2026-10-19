<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Fasilitas;
use App\Models\FotoFasilitas;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FotoFasilitasController extends Controller
{
    public function destroy(Fasilitas $fasilitas, $fotoFasilitas)
    {
        $foto = $fasilitas->foto()->findOrFail($fotoFasilitas);
        $path = $foto->foto;
        DB::transaction(function () use ($foto, $fasilitas, $path) {
            $foto->delete();
            if ($fasilitas->gambar === $path) {
                $fasilitas->update(['gambar' => $fasilitas->foto()->value('foto')]);
            }
        });

        $this->deleteOwnedFile($path);

        return redirect()->route('website.fasilitas.edit', $fasilitas)->with('success', 'Foto fasilitas berhasil dihapus.');
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
