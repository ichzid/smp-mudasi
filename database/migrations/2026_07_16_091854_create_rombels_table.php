<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rombel', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // contoh: "7 A"
            $table->string('tingkat'); // 7 / 8 / 9
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->cascadeOnDelete();
            $table->foreignId('wali_guru_id')->nullable()->constrained('guru')->nullOnDelete();
            $table->timestamps();

            // Memastikan 1 guru hanya menjadi wali untuk 1 rombel pada tahun ajaran yang sama
            $table->unique(['tahun_ajaran_id', 'wali_guru_id'], 'unique_wali_per_ta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rombel');
    }
};
