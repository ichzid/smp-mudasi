<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KartuRfid extends Model
{
    protected $table = 'kartu_rfid';

    protected $fillable = [
        'siswa_id',
        'kode_uid',
        'status',
        'diterbitkan_pada',
    ];

    protected $casts = [
        'diterbitkan_pada' => 'datetime',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
