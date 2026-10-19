<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('foto_galeri')->whereNull('galeri_id')->exists()
            || DB::table('foto_galeri')->whereNotIn('galeri_id', DB::table('galeri')->select('id'))->exists()) {
            throw new RuntimeException('Migrasi foto_galeri dibatalkan: terdapat foto tanpa album (galeri_id NULL atau tidak valid). Hubungkan foto ke album sebelum migrasi.');
        }

        if (DB::getDriverName() === 'sqlite') {
            $this->rebuild(true);

            return;
        }

        Schema::table('foto_galeri', fn (Blueprint $table) => $table->dropForeign('foto_galeri_galeri_id_foreign'));
        if (! Schema::hasColumn('foto_galeri', 'foto')) {
            Schema::table('foto_galeri', fn (Blueprint $table) => $table->renameColumn('gambar', 'foto'));
        } elseif (Schema::hasColumn('foto_galeri', 'gambar')) {
            DB::table('foto_galeri')->where(fn ($query) => $query->whereNull('foto')->orWhere('foto', ''))->update(['foto' => DB::raw('gambar')]);
            Schema::table('foto_galeri', fn (Blueprint $table) => $table->dropColumn('gambar'));
        }
        Schema::table('foto_galeri', function (Blueprint $table) {
            $table->dropColumn(['judul', 'keterangan', 'urutan']);
            $table->unsignedBigInteger('galeri_id')->nullable(false)->change();
            $table->text('foto')->nullable(false)->change();
            $table->foreign('galeri_id')->references('id')->on('galeri')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->rebuild(false);

            return;
        }

        Schema::table('foto_galeri', fn (Blueprint $table) => $table->dropForeign('foto_galeri_galeri_id_foreign'));
        Schema::table('foto_galeri', function (Blueprint $table) {
            $table->unsignedBigInteger('galeri_id')->nullable()->change();
            $table->renameColumn('foto', 'gambar');
            $table->string('judul')->default('');
            $table->text('keterangan')->nullable();
            $table->unsignedInteger('urutan')->default(0);
            $table->foreign('galeri_id')->references('id')->on('galeri')->nullOnDelete();
        });
    }

    private function rebuild(bool $up): void
    {
        $hasFoto = Schema::hasColumn('foto_galeri', 'foto');
        $hasGambar = Schema::hasColumn('foto_galeri', 'gambar');
        Schema::disableForeignKeyConstraints();
        try {
            Schema::create('foto_galeri_rebuild', function (Blueprint $table) use ($up) {
                $table->id();
                $table->foreignId('galeri_id')->nullable(! $up)->constrained('galeri')->onDelete($up ? 'cascade' : 'set null');
                if (! $up) {
                    $table->string('judul')->default('');
                }
                $table->text($up ? 'foto' : 'gambar');
                if (! $up) {
                    $table->text('keterangan')->nullable();
                    $table->unsignedInteger('urutan')->default(0);
                }
                $table->timestamps();
            });

            $source = $up ? ($hasFoto && $hasGambar ? "coalesce(nullif(foto, ''), gambar)" : ($hasFoto ? 'foto' : 'gambar')) : 'foto';
            $destination = $up ? 'foto' : 'gambar';
            DB::statement("insert into foto_galeri_rebuild (id, galeri_id, $destination, created_at, updated_at) select id, galeri_id, $source, created_at, updated_at from foto_galeri");
            Schema::drop('foto_galeri');
            Schema::rename('foto_galeri_rebuild', 'foto_galeri');
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
};
