<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengaturanSekolah extends Model
{
    use HasFactory;

    protected $table = 'pengaturan_sekolah';

    protected $fillable = [
        'nama_sekolah',
        'slogan',
        'logo',
        'email',
        'nomor_telepon',
        'alamat',
        'profil_singkat',
        'nama_kepala_sekolah',
        'foto_kepala_sekolah',
        'sambutan_kepala_sekolah',
        'jumlah_siswa',
        'jumlah_guru',
        'jumlah_prestasi',
        'facebook',
        'instagram',
        'youtube',
    ];
}
