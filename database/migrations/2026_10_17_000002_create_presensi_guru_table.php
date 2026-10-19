<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presensi_guru', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('guru')->restrictOnDelete();
            $table->date('tanggal');
            $table->time('waktu_masuk');
            $table->string('status');
            $table->string('metode_masuk');
            $table->time('waktu_pulang')->nullable();
            $table->string('status_pulang')->nullable();
            $table->string('metode_pulang')->nullable();
            $table->timestamps();

            $table->unique(['guru_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presensi_guru');
    }
};
