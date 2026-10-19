<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if ($this->hasCompositeIndex()) {
            if (DB::getDriverName() === 'mysql') {
                // MySQL membutuhkan indeks lain untuk foreign key sebelum indeks gabungan dihapus.
                Schema::table('foto_fasilitas', function (Blueprint $table) {
                    $table->index('fasilitas_id', 'foto_fasilitas_fasilitas_id_fk_idx');
                });
            }

            Schema::table('foto_fasilitas', function (Blueprint $table) {
                $table->dropIndex(['fasilitas_id', 'urutan']);
            });
        }

        if (Schema::hasColumn('foto_fasilitas', 'gambar') && ! Schema::hasColumn('foto_fasilitas', 'foto')) {
            Schema::table('foto_fasilitas', function (Blueprint $table) {
                $table->renameColumn('gambar', 'foto');
            });
        }

        $columns = array_values(array_filter(
            ['judul', 'keterangan', 'urutan'],
            fn (string $column) => Schema::hasColumn('foto_fasilitas', $column)
        ));

        if ($columns !== []) {
            Schema::table('foto_fasilitas', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('foto_fasilitas', 'foto') && ! Schema::hasColumn('foto_fasilitas', 'gambar')) {
            Schema::table('foto_fasilitas', function (Blueprint $table) {
                $table->renameColumn('foto', 'gambar');
            });
        }

        $columns = array_values(array_filter(
            ['judul', 'keterangan', 'urutan'],
            fn (string $column) => ! Schema::hasColumn('foto_fasilitas', $column)
        ));

        if ($columns !== []) {
            Schema::table('foto_fasilitas', function (Blueprint $table) use ($columns) {
                if (in_array('judul', $columns, true)) {
                    $table->string('judul')->default('');
                }
                if (in_array('keterangan', $columns, true)) {
                    $table->text('keterangan')->nullable();
                }
                if (in_array('urutan', $columns, true)) {
                    $table->unsignedInteger('urutan')->default(0);
                }
            });
        }

        if (! $this->hasCompositeIndex()) {
            Schema::table('foto_fasilitas', function (Blueprint $table) {
                $table->index(['fasilitas_id', 'urutan']);
            });
        }
    }

    private function hasCompositeIndex(): bool
    {
        return collect(Schema::getIndexes('foto_fasilitas'))
            ->contains(fn (array $index) => ($index['name'] ?? null) === 'foto_fasilitas_fasilitas_id_urutan_index');
    }
};
