<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $table = 'siswa';

    protected $fillable = [
        'nis',
        'nisn',
        'nama_lengkap',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'nama_wali',
        'no_hp_wali',
        'foto_url',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
        ];
    }

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
