<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rombel_siswa', function (Blueprint $table) {
            $table->unique(
                ['rombel_id', 'siswa_id', 'tanggal_masuk'],
                'rombel_siswa_identik_unique'
            );
            $table->index(
                ['siswa_id', 'tanggal_keluar'],
                'rombel_siswa_status_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('rombel_siswa', function (Blueprint $table) {
            $table->dropUnique('rombel_siswa_identik_unique');
            $table->dropIndex('rombel_siswa_status_idx');
        });
    }
};
