<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    protected $table = 'guru';

    protected $fillable = [
        'nomor_indentitas',
        'nama_lengkap',
        'jenis_kelamin',
        'tanggal_lahir',
        'jabatan',
        'bidang_tugas',
        'biografi_singkat',
        'urutan',
        'aktif',
        'no_hp',
        'foto',
        'user_id',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'aktif' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rombel()
    {
        return $this->hasMany(Rombel::class, 'wali_guru_id');
    }

    public function kartuRfid()
    {
        return $this->hasOne(KartuRfid::class);
    }
}
