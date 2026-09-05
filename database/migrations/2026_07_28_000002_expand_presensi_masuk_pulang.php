<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presensi', function (Blueprint $table) {
            $table->time('waktu_masuk')->nullable();
            $table->string('metode_masuk')->nullable();
            $table->time('waktu_pulang')->nullable();
            $table->string('status_pulang')->nullable();
            $table->string('metode_pulang')->nullable();
            $table->string('alasan_pulang_cepat')->nullable();
            $table->text('catatan_pulang')->nullable();
            $table->foreignId('pulang_dicatat_oleh')->nullable()->constrained('users')->nullOnDelete();
        });

        DB::table('presensi')->whereNull('waktu_masuk')->update(['waktu_masuk' => DB::raw('waktu_scan')]);
        DB::table('presensi')->whereNull('metode_masuk')->update(['metode_masuk' => DB::raw('metode')]);
    }

    public function down(): void
    {
        Schema::table('presensi', function (Blueprint $table) {
            $table->dropForeign(['pulang_dicatat_oleh']);
            $table->dropColumn(['waktu_masuk', 'metode_masuk', 'waktu_pulang', 'status_pulang', 'metode_pulang', 'alasan_pulang_cepat', 'catatan_pulang', 'pulang_dicatat_oleh']);
        });
    }
};
