<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Presensi extends Model
{
    protected $table = 'presensi';

    protected $fillable = [
        'siswa_id', 'rombel_id', 'tanggal', 'waktu_scan', 'metode', 'waktu_masuk', 'status', 'metode_masuk', 'dicatat_oleh',
        'waktu_pulang', 'status_pulang', 'metode_pulang', 'alasan_pulang_cepat', 'catatan_pulang', 'pulang_dicatat_oleh',
    ];

    protected function casts(): array
    {
        return ['tanggal' => 'date:Y-m-d'];
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function rombel()
    {
        return $this->belongsTo(Rombel::class);
    }

    public function pencatat()
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

    public function pencatatPulang()
    {
        return $this->belongsTo(User::class, 'pulang_dicatat_oleh');
    }

    public function getDurasiSekolahAttribute(): ?string
    {
        if (! $this->waktu_masuk || ! $this->waktu_pulang) {
            return null;
        }
        $menit = Carbon::createFromFormat('H:i:s', $this->waktu_masuk)->diffInMinutes(Carbon::createFromFormat('H:i:s', $this->waktu_pulang));

        return sprintf('%d jam %d menit', intdiv($menit, 60), $menit % 60);
    }
}
