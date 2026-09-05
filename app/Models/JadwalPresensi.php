<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

class JadwalPresensi extends Model
{
    protected $table = 'jadwal_presensi';

    protected $fillable = ['hari', 'jam_mulai_masuk', 'batas_terlambat', 'jam_mulai_pulang', 'jam_akhir_pulang', 'is_aktif'];

    protected function casts(): array
    {
        return ['hari' => 'integer', 'is_aktif' => 'boolean'];
    }

    public function getNamaHariAttribute(): string
    {
        return [1 => 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'][$this->hari];
    }

    public static function untukTanggal(CarbonInterface $tanggal): ?self
    {
        return static::where('hari', $tanggal->isoWeekday())->first();
    }
}
