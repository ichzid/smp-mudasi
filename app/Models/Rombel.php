<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rombel extends Model
{
    protected $table = 'rombel';

    protected $fillable = [
        'nama',
        'tingkat',
        'tahun_ajaran_id',
        'wali_guru_id',
    ];

    public function getLabelAttribute(): string
    {
        return 'Kelas '.$this->nama;
    }

    public function getKelasLabelAttribute(): string
    {
        return 'Kelas '.$this->nama;
    }

    public function getSelectLabelAttribute(): string
    {
        return 'Tingkat '.$this->tingkat.' - Kelas '.$this->nama;
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function waliGuru()
    {
        return $this->belongsTo(Guru::class, 'wali_guru_id');
    }

    public function siswa()
    {
        return $this->belongsToMany(Siswa::class, 'rombel_siswa')
            ->withPivot('tanggal_masuk', 'tanggal_keluar')
            ->withTimestamps();
    }
}
