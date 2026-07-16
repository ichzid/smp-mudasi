<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $table = 'siswa';

    protected $fillable = [
        'nis',
        'nisn',
        'nama',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'foto_url',
        'status',
    ];

    public function kartuRfid()
    {
        return $this->hasOne(KartuRfid::class);
    }

    public function rombel()
    {
        return $this->belongsToMany(Rombel::class, 'rombel_siswa')
            ->withPivot('tanggal_masuk', 'tanggal_keluar')
            ->withTimestamps();
    }

    public function presensi()
    {
        return $this->hasMany(Presensi::class);
    }
}
