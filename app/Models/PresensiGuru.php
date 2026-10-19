<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PresensiGuru extends Model
{
    protected $table = 'presensi_guru';

    protected $fillable = ['guru_id', 'tanggal', 'waktu_masuk', 'status', 'metode_masuk', 'waktu_pulang', 'status_pulang', 'metode_pulang'];

    protected function casts(): array
    {
        return ['tanggal' => 'date:Y-m-d'];
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }
}
