<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JadwalPresensiSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([1 => '13:00:00', 2 => '13:00:00', 3 => '13:00:00', 4 => '13:00:00', 5 => '12:00:00', 6 => '11:00:00', 7 => null] as $hari => $pulang) {
            DB::table('jadwal_presensi')->insertOrIgnore([
                'hari' => $hari,
                'jam_mulai_masuk' => $hari === 7 ? null : '06:00:00',
                'batas_terlambat' => $hari === 7 ? null : '07:15:00',
                'jam_mulai_pulang' => $pulang,
                'is_aktif' => $hari !== 7,
                'updated_at' => now(),
                'created_at' => now(),
            ]);
        }
    }
}
