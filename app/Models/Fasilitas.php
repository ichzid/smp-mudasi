<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fasilitas extends Model
{
    protected $table = 'fasilitas';

    protected $fillable = ['nama', 'slug', 'deskripsi', 'gambar', 'urutan', 'aktif'];

    protected $casts = ['aktif' => 'boolean', 'urutan' => 'integer'];

    public function foto(): HasMany
    {
        return $this->hasMany(FotoFasilitas::class)->orderBy('id');
    }
}
