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
        Schema::table('guru', function (Blueprint $table) {
            // Rename kolom identitas
            $table->renameColumn('nip', 'nomor_indentitas');
        });

        // MySQL tidak ikut me-rename index saat kolom di-rename
        if ($this->hasIndex('guru', 'guru_nip_unique')) {
            Schema::table('guru', function (Blueprint $table) {
                $table->dropUnique('guru_nip_unique');
            });
        }

        // Index unique sudah dibuat ulang oleh perintah change() di bawah
        if ($this->hasIndex('guru', 'guru_nomor_indentitas_unique')) {
            Schema::table('guru', function (Blueprint $table) {
                $table->dropUnique('guru_nomor_indentitas_unique');
            });
        }

        Schema::table('guru', function (Blueprint $table) {
            // Sesuaikan tipe kolom identitas & foto
            $table->string('nomor_indentitas', 30)->nullable()->change();
            $table->text('foto')->nullable()->change();

            // Kolom baru
            $table->date('tanggal_lahir')->nullable()->after('jenis_kelamin');
            $table->string('jabatan')->default('Guru')->after('tanggal_lahir');
            $table->string('bidang_tugas')->nullable()->after('jabatan');
            $table->text('biografi_singkat')->nullable()->after('bidang_tugas');
            $table->unsignedInteger('urutan')->default(0)->after('biografi_singkat');
            $table->boolean('aktif')->default(true)->after('urutan');
        });

        Schema::table('guru', function (Blueprint $table) {
            $table->unique('nomor_indentitas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if ($this->hasIndex('guru', 'guru_nomor_indentitas_unique')) {
            Schema::table('guru', function (Blueprint $table) {
                $table->dropUnique('guru_nomor_indentitas_unique');
            });
        }

        Schema::table('guru', function (Blueprint $table) {
            $table->dropColumn([
                'tanggal_lahir',
                'jabatan',
                'bidang_tugas',
                'biografi_singkat',
                'urutan',
                'aktif',
            ]);
        });

        Schema::table('guru', function (Blueprint $table) {
            $table->text('foto')->nullable()->change();
            $table->string('nomor_indentitas')->nullable()->change();
            $table->renameColumn('nomor_indentitas', 'nip');
        });

        Schema::table('guru', function (Blueprint $table) {
            $table->unique('nip');
        });
    }

    /**
     * Cek keberadaan index pada tabel.
     */
    private function hasIndex(string $table, string $index): bool
    {
        return collect(Schema::getIndexes($table))
            ->contains(fn (array $idx) => ($idx['name'] ?? null) === $index);
    }
};
