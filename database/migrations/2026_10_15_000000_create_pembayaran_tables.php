<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarif_pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->restrictOnDelete();
            $table->string('jenis', 3);
            $table->unsignedBigInteger('nominal');
            $table->timestamps();
            $table->unique(['tahun_ajaran_id', 'jenis']);
        });

        Schema::create('tagihan_pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->restrictOnDelete();
            $table->foreignId('tarif_id')->constrained('tarif_pembayaran')->restrictOnDelete();
            $table->char('periode_key', 7);
            $table->unsignedBigInteger('nominal');
            $table->timestamps();
            $table->unique(['siswa_id', 'tarif_id', 'periode_key']);
        });

        Schema::create('transaksi_pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tagihan_id')->constrained('tagihan_pembayaran')->restrictOnDelete();
            $table->unsignedBigInteger('nominal');
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->timestamp('dibayar_pada');
            $table->string('kode_kuitansi')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_pembayaran');
        Schema::dropIfExists('tagihan_pembayaran');
        Schema::dropIfExists('tarif_pembayaran');
    }
};
