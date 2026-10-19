<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ekstrakurikuler extends Model
{
    protected $table = 'ekstrakurikuler';

    protected $fillable = ['nama', 'slug', 'pembina', 'jadwal', 'deskripsi', 'gambar', 'aktif'];

    protected $casts = ['aktif' => 'boolean'];
}
