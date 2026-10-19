<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Galeri extends Model
{
    protected $table = 'galeri';

    protected $fillable = ['judul', 'slug', 'deskripsi', 'gambar_sampul'];

    public function foto(): HasMany
    {
        return $this->hasMany(FotoGaleri::class)->orderBy('id');
    }
}
