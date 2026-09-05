<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_presensi', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('hari')->unique();
            $table->time('jam_mulai_masuk')->nullable();
            $table->time('batas_terlambat')->nullable();
            $table->time('jam_mulai_pulang')->nullable();
            $table->time('jam_akhir_pulang')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        $now = now();
        foreach ([1 => '13:00:00', 2 => '13:00:00', 3 => '13:00:00', 4 => '13:00:00', 5 => '12:00:00', 6 => '11:00:00'] as $hari => $pulang) {
            DB::table('jadwal_presensi')->insert([
                'hari' => $hari, 'jam_mulai_masuk' => '06:00:00', 'batas_terlambat' => '07:15:00',
                'jam_mulai_pulang' => $pulang, 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
        DB::table('jadwal_presensi')->insert(['hari' => 7, 'is_aktif' => false, 'created_at' => $now, 'updated_at' => $now]);
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_presensi');
    }
};
