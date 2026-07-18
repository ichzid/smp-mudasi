<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RombelSiswa extends Model
{
    protected $table = 'rombel_siswa';

    protected $fillable = [
        'rombel_id',
        'siswa_id',
        'tanggal_masuk',
        'tanggal_keluar',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_masuk' => 'date',
            'tanggal_keluar' => 'date',
        ];
    }

    public function rombel()
    {
        return $this->belongsTo(Rombel::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
