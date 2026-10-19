<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel konten website sekolah (hasil merge dari database sim_sekolah).
     * Catatan: tabel `users`, `migrations`, `password_resets`,
     * `personal_access_tokens`, dan `failed_jobs` TIDAK diimpor karena
     * sudah ada di aplikasi ini.
     */
    public function up(): void
    {
        // Kategori berita
        Schema::create('kategori_berita', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('slug')->unique();
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        // Berita / artikel
        Schema::create('berita', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_berita_id')
                ->nullable()
                ->constrained('kategori_berita')
                ->nullOnDelete();
            $table->string('judul');
            $table->string('slug')->unique();
            $table->longText('isi');
            $table->string('gambar')->nullable();
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->timestamp('diterbitkan_pada')->nullable();
            $table->timestamps();
        });

        // Ekstrakurikuler
        Schema::create('ekstrakurikuler', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('slug')->unique();
            $table->string('pembina')->nullable();
            $table->string('jadwal')->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('gambar')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        // Fasilitas sekolah
        Schema::create('fasilitas', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('slug')->unique();
            $table->text('deskripsi')->nullable();
            $table->text('gambar')->nullable();
            $table->unsignedInteger('urutan')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->index(['aktif', 'urutan']);
        });

        // Foto fasilitas (galeri foto per fasilitas)
        Schema::create('foto_fasilitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fasilitas_id')
                ->constrained('fasilitas')
                ->cascadeOnDelete();
            $table->string('judul');
            $table->text('gambar');
            $table->text('keterangan')->nullable();
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();

            $table->index(['fasilitas_id', 'urutan']);
        });

        // Galeri / album
        Schema::create('galeri', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('slug')->unique();
            $table->text('deskripsi')->nullable();
            $table->string('gambar_sampul')->nullable();
            $table->timestamps();
        });

        // Foto galeri
        Schema::create('foto_galeri', function (Blueprint $table) {
            $table->id();
            $table->foreignId('galeri_id')
                ->nullable()
                ->constrained('galeri')
                ->nullOnDelete();
            $table->string('judul');
            $table->string('gambar');
            $table->text('keterangan')->nullable();
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();
        });

        // Pengaturan / profil sekolah (single row)
        Schema::create('pengaturan_sekolah', function (Blueprint $table) {
            $table->id();
            $table->string('nama_sekolah');
            $table->string('slogan')->nullable();
            $table->string('logo')->nullable();
            $table->string('email')->nullable();
            $table->string('nomor_telepon')->nullable();
            $table->text('alamat')->nullable();
            $table->text('profil_singkat')->nullable();
            $table->string('nama_kepala_sekolah')->nullable();
            $table->string('foto_kepala_sekolah')->nullable();
            $table->text('sambutan_kepala_sekolah')->nullable();
            $table->unsignedInteger('jumlah_siswa')->default(0);
            $table->unsignedInteger('jumlah_guru')->default(0);
            $table->unsignedInteger('jumlah_prestasi')->default(0);
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('youtube')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaturan_sekolah');
        Schema::dropIfExists('foto_galeri');
        Schema::dropIfExists('galeri');
        Schema::dropIfExists('foto_fasilitas');
        Schema::dropIfExists('fasilitas');
        Schema::dropIfExists('ekstrakurikuler');
        Schema::dropIfExists('berita');
        Schema::dropIfExists('kategori_berita');
    }
};
