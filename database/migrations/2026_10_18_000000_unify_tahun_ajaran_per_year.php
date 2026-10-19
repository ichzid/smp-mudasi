<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Semester pada rombel hanya menandai snapshot historis; NULL berarti rombel annual.
        Schema::table('rombel', function (Blueprint $table) {
            $table->tinyInteger('semester')->nullable();
        });
        Schema::table('tarif_pembayaran', function (Blueprint $table) {
            $table->tinyInteger('semester')->nullable();
        });

        // Simpan semester asal sebelum FK tahun ajaran diubah.
        foreach (DB::table('tahun_ajaran')->select('id', 'semester')->get() as $tahun) {
            DB::table('rombel')->where('tahun_ajaran_id', $tahun->id)->update(['semester' => $tahun->semester]);
            DB::table('tarif_pembayaran')->where('tahun_ajaran_id', $tahun->id)->update(['semester' => $tahun->semester]);
        }

        // MySQL dapat memakai unique lama sebagai indeks pendukung FK.
        Schema::table('rombel', function (Blueprint $table) {
            $table->index('tahun_ajaran_id', 'rombel_tahun_ajaran_fk_idx');
            $table->dropUnique('unique_wali_per_ta');
        });
        Schema::table('tarif_pembayaran', function (Blueprint $table) {
            $table->index('tahun_ajaran_id', 'tarif_tahun_ajaran_fk_idx');
            $table->dropUnique('tarif_pembayaran_tahun_ajaran_id_jenis_unique');
        });
        Schema::table('tahun_ajaran', function (Blueprint $table) {
            $table->dropUnique('tahun_ajaran_nama_semester_unique');
        });

        DB::transaction(function () {
            foreach (DB::table('tahun_ajaran')->select('nama')->distinct()->get() as $year) {
                $rows = DB::table('tahun_ajaran')->where('nama', $year->nama)
                    ->orderByDesc('is_aktif')->orderByDesc('semester')->orderBy('id')->get();
                $survivor = $rows->first();

                foreach ($rows->skip(1) as $old) {
                    // Jangan gabungkan rombel atau tarif: ID mereka dirujuk oleh presensi,
                    // keanggotaan, tagihan, dan transaksi yang harus tetap utuh.
                    DB::table('rombel')->where('tahun_ajaran_id', $old->id)
                        ->update(['tahun_ajaran_id' => $survivor->id]);
                    DB::table('tarif_pembayaran')->where('tahun_ajaran_id', $old->id)
                        ->update(['tahun_ajaran_id' => $survivor->id]);
                    DB::table('tahun_ajaran')->where('id', $old->id)->delete();
                }

                // Jika kelas hanya pernah dibuat untuk satu semester, lanjutkan kelas
                // dan keanggotaannya sebagai kelas tahunan tanpa mengganti ID historis.
                $semesters = DB::table('rombel')->where('tahun_ajaran_id', $survivor->id)
                    ->distinct()->pluck('semester');
                if ($semesters->count() === 1) {
                    DB::table('rombel')->where('tahun_ajaran_id', $survivor->id)
                        ->update(['semester' => null]);
                }
            }
        });

        Schema::table('tahun_ajaran', function (Blueprint $table) {
            $table->unique('nama', 'tahun_ajaran_nama_unique');
        });
        Schema::table('rombel', function (Blueprint $table) {
            $table->unique(['tahun_ajaran_id', 'semester', 'wali_guru_id'], 'unique_wali_per_ta_semester');
        });
        Schema::table('tarif_pembayaran', function (Blueprint $table) {
            $table->tinyInteger('semester')->nullable(false)->change();
            $table->unique(['tahun_ajaran_id', 'semester', 'jenis'], 'tarif_ta_semester_jenis_unique');
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Penggabungan tahun ajaran tidak dapat dibalik tanpa mengubah kembali ID historis yang telah dihapus.');
    }
};
