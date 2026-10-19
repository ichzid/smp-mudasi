<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class KategoriBerita extends Model
{
    use HasFactory;

    protected $table = 'kategori_berita';

    protected $fillable = [
        'nama',
        'slug',
        'deskripsi'
    ];

    protected static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->nama);
            }
        });
    }

    public function berita()
    {
        return $this->hasMany(Berita::class, 'kategori_berita_id');
    }
}
