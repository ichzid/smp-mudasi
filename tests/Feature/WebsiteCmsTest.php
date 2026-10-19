<?php

use App\Models\Berita;
use App\Models\Ekstrakurikuler;
use App\Models\Fasilitas;
use App\Models\Galeri;
use App\Models\KategoriBerita;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('merender halaman CMS dan mengarahkan tamu ke login', function () {
    $this->get(route('website.berita.index'))->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create(['role' => 'admin']));
    expect(\Illuminate\Support\Facades\Route::has('website.kategori-berita.show'))->toBeFalse()
        ->and(\Illuminate\Support\Facades\Route::has('website.fasilitas.foto.store'))->toBeFalse()
        ->and(\Illuminate\Support\Facades\Route::has('website.fasilitas.foto.update'))->toBeFalse()
        ->and(\Illuminate\Support\Facades\Route::has('website.galeri.foto.store'))->toBeFalse()
        ->and(\Illuminate\Support\Facades\Route::has('website.galeri.foto.update'))->toBeFalse();

    foreach (['pengaturan.edit', 'berita.index', 'berita.create', 'kategori-berita.index', 'kategori-berita.create', 'fasilitas.index', 'fasilitas.create', 'ekstrakurikuler.index', 'ekstrakurikuler.create', 'galeri.index', 'galeri.create'] as $name) {
        $this->get(route('website.'.$name))->assertOk();
    }

    $kategori = KategoriBerita::create(['nama' => 'Kegiatan', 'slug' => 'kegiatan']);
    $berita = Berita::create(['judul' => 'Kegiatan Sekolah', 'slug' => 'kegiatan-sekolah', 'isi' => '<p>Isi berita</p>']);
    $fasilitas = Fasilitas::create(['nama' => 'Perpustakaan', 'slug' => 'perpustakaan']);
    $ekstrakurikuler = Ekstrakurikuler::create(['nama' => 'Pramuka', 'slug' => 'pramuka']);
    $galeri = Galeri::create(['judul' => 'Sekolah', 'slug' => 'sekolah']);

    foreach (['kategori-berita' => $kategori, 'berita' => $berita, 'fasilitas' => $fasilitas, 'ekstrakurikuler' => $ekstrakurikuler, 'galeri' => $galeri] as $name => $model) {
        $this->get(route('website.'.$name.'.edit', $model))->assertOk();
    }

    $fasilitas->foto()->create(['foto' => 'website/fasilitas/contoh.jpg']);
    $galeri->foto()->create(['foto' => 'website/galeri/contoh.jpg']);
    $this->get(route('website.fasilitas.edit', $fasilitas))->assertOk()
        ->assertDontSee('Judul Foto')->assertDontSee('Urutan Foto');
    $this->get(route('website.galeri.edit', $galeri))->assertOk();
});

it('memiliki lima kolom foto fasilitas dan foreign key cascade', function () {
    expect(Schema::getColumnListing('foto_fasilitas'))->toEqualCanonicalizing(['id', 'fasilitas_id', 'foto', 'created_at', 'updated_at']);

    $fasilitas = Fasilitas::create(['nama' => 'Aula', 'slug' => 'aula']);
    $foto = $fasilitas->foto()->create(['foto' => 'website/fasilitas/contoh.jpg']);
    $fasilitas->delete();
    expect(DB::table('foto_fasilitas')->where('id', $foto->id)->exists())->toBeFalse();
});

it('mempertahankan path foto lama ketika migrasi dijalankan dan dibalik', function () {
    $migration = require database_path('migrations/2026_10_14_000000_simplify_foto_fasilitas_table.php');
    $migration->down();

    $fasilitas = Fasilitas::create(['nama' => 'Aula', 'slug' => 'aula']);
    $path = 'website/fasilitas/foto-lama.jpg';
    $id = DB::table('foto_fasilitas')->insertGetId([
        'fasilitas_id' => $fasilitas->id, 'judul' => 'Foto lama', 'gambar' => $path,
        'keterangan' => 'Arsip', 'urutan' => 4,
    ]);

    $migration->up();

    expect(Schema::getColumnListing('foto_fasilitas'))->toEqualCanonicalizing(['id', 'fasilitas_id', 'foto', 'created_at', 'updated_at'])
        ->and(DB::table('foto_fasilitas')->where('id', $id)->value('foto'))->toBe($path);
});

it('menyimpan dua foto fasilitas dengan foto pertama sebagai gambar utama', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create(['role' => 'admin']));

    $this->post(route('website.fasilitas.store'), [
        'nama' => 'Laboratorium', 'urutan' => 0, 'aktif' => 1,
        'gambar_baru' => [UploadedFile::fake()->image('ruang-praktikum.jpg'), UploadedFile::fake()->image('alat-belajar.png')],
    ])->assertRedirect(route('website.fasilitas.index'));

    $fasilitas = Fasilitas::where('nama', 'Laboratorium')->firstOrFail();
    $foto = $fasilitas->foto()->get();
    expect($foto)->toHaveCount(2)
        ->and($fasilitas->gambar)->toBe($foto[0]->foto)
        ->and($foto[0]->id)->toBeLessThan($foto[1]->id);
    Storage::disk('public')->assertExists($foto->pluck('foto')->all());
    $this->get(route('website.fasilitas.edit', $fasilitas))->assertOk()
        ->assertDontSee('Gambar utama Laboratorium')
        ->assertSee('form="hapus-foto-'.$foto[0]->id.'"', false);
});

it('menggunakan host permintaan untuk URL foto fasilitas yang diunggah', function () {
    Storage::fake('public');
    config()->set('app.url', 'http://localhost');
    $this->withServerVariables(['HTTP_HOST' => 'smp_mudasi.test'])
        ->actingAs(User::factory()->create(['role' => 'admin']));

    $host = 'http://smp_mudasi.test';
    $this->post($host.route('website.fasilitas.store', [], false), [
        'nama' => 'Laboratorium', 'urutan' => 0, 'aktif' => 1,
        'gambar_baru' => [UploadedFile::fake()->image('utama.jpg'), UploadedFile::fake()->image('kedua.jpg')],
    ])->assertRedirect(route('website.fasilitas.index'));

    $fasilitas = Fasilitas::where('nama', 'Laboratorium')->firstOrFail();
    $foto = $fasilitas->foto()->get();
    foreach ($foto as $item) {
        $this->get($host.route('website.fasilitas.edit', $fasilitas, false))->assertOk()
            ->assertSee('src="http://smp_mudasi.test/storage/'.$item->foto.'"', false)
            ->assertDontSee('src="http://localhost/storage/'.$item->foto.'"', false);
    }
    $this->get($host.route('website.fasilitas.index', [], false))->assertOk()
        ->assertSee('src="http://smp_mudasi.test/storage/'.$fasilitas->gambar.'"', false)
        ->assertDontSee('src="http://localhost/storage/'.$fasilitas->gambar.'"', false);
});

it('menambahkan dua foto pada edit tanpa mengganti gambar utama dan foto lama', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    Storage::disk('public')->put('website/fasilitas/utama.jpg', 'utama');
    Storage::disk('public')->put('website/fasilitas/lama.jpg', 'lama');
    $fasilitas = Fasilitas::create(['nama' => 'Perpustakaan', 'slug' => 'perpustakaan', 'gambar' => 'website/fasilitas/utama.jpg']);
    $lama = $fasilitas->foto()->create(['foto' => 'website/fasilitas/lama.jpg']);

    $this->put(route('website.fasilitas.update', $fasilitas), [
        'nama' => 'Perpustakaan Baru', 'urutan' => 1, 'aktif' => 1,
        'gambar_baru' => [UploadedFile::fake()->image('koleksi-buku.jpg'), UploadedFile::fake()->image('ruang-baca.webp')],
    ])->assertRedirect(route('website.fasilitas.index'));

    $fasilitas->refresh();
    $foto = $fasilitas->foto()->get();
    expect($fasilitas->gambar)->toBe('website/fasilitas/utama.jpg')
        ->and($foto)->toHaveCount(3)
        ->and($foto[0]->foto)->toBe($lama->foto);
    Storage::disk('public')->assertExists($foto->pluck('foto')->push($fasilitas->gambar)->all());
    $this->get(route('website.fasilitas.edit', $fasilitas))->assertOk()
        ->assertSee('Gambar utama Perpustakaan Baru')
        ->assertSee('Foto fasilitas '.$lama->id);
});

it('menghapus gambar utama legacy tanpa menghapus foto galeri dan berkas yang masih dipakai', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    $legacyPath = 'website/fasilitas/'.str_repeat('a', 40).'.jpg';
    Storage::disk('public')->put($legacyPath, 'utama');
    $fasilitas = Fasilitas::create(['nama' => 'Aula', 'slug' => 'aula', 'gambar' => $legacyPath]);
    $foto = $fasilitas->foto()->create(['foto' => 'website/fasilitas/hilang.jpg']);

    $this->get(route('website.fasilitas.edit', $fasilitas))->assertOk()
        ->assertSee('id="hapus-foto-'.$foto->id.'"', false)
        ->assertSee('form="hapus-foto-'.$foto->id.'"', false)
        ->assertSee(route('website.fasilitas.foto.destroy', [$fasilitas, $foto]))
        ->assertSee('Foto tidak tersedia')
        ->assertSee('id="hapus_gambar_utama"', false);

    $this->put(route('website.fasilitas.update', $fasilitas), [
        'nama' => 'Aula', 'urutan' => 0, 'aktif' => 1, 'hapus_gambar_utama' => 1,
    ])->assertRedirect(route('website.fasilitas.index'));

    expect($fasilitas->fresh()->gambar)->toBe($foto->foto)
        ->and($fasilitas->foto()->count())->toBe(1);
    Storage::disk('public')->assertMissing($legacyPath);

    $sharedPath = 'website/fasilitas/'.str_repeat('b', 40).'.png';
    Storage::disk('public')->put($sharedPath, 'dipakai bersama');
    $fasilitas->update(['gambar' => $sharedPath]);
    $lain = Fasilitas::create(['nama' => 'Ruang Lain', 'slug' => 'ruang-lain', 'gambar' => $sharedPath]);
    $this->put(route('website.fasilitas.update', $fasilitas), [
        'nama' => 'Aula', 'urutan' => 0, 'aktif' => 1, 'hapus_gambar_utama' => 1,
    ])->assertRedirect(route('website.fasilitas.index'));
    expect($lain->fresh()->gambar)->toBe($sharedPath);
    Storage::disk('public')->assertExists($sharedPath);

    $this->put(route('website.fasilitas.update', $lain), [
        'nama' => 'Ruang Lain', 'urutan' => 0, 'aktif' => 1, 'hapus_gambar_utama' => 1,
    ])->assertRedirect(route('website.fasilitas.index'));
    expect($lain->fresh()->gambar)->toBeNull();
    Storage::disk('public')->assertMissing($sharedPath);
});

it('mengalihkan gambar utama ke foto berikutnya saat foto utama dihapus', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    $this->post(route('website.fasilitas.store'), [
        'nama' => 'Laboratorium', 'urutan' => 0, 'aktif' => 1,
        'gambar_baru' => [UploadedFile::fake()->image('utama.jpg'), UploadedFile::fake()->image('kedua.jpg')],
    ])->assertRedirect(route('website.fasilitas.index'));

    $fasilitas = Fasilitas::where('nama', 'Laboratorium')->firstOrFail();
    $foto = $fasilitas->foto()->get();
    $oldPath = $foto[0]->foto;
    $this->delete(route('website.fasilitas.foto.destroy', [$fasilitas, $foto[0]]))
        ->assertRedirect(route('website.fasilitas.edit', $fasilitas));

    $fasilitas->refresh();
    expect($fasilitas->gambar)->toBe($foto[1]->foto);
    Storage::disk('public')->assertExists($fasilitas->gambar);
    Storage::disk('public')->assertMissing($oldPath);

    $this->delete(route('website.fasilitas.foto.destroy', [$fasilitas, $foto[1]]))
        ->assertRedirect(route('website.fasilitas.edit', $fasilitas));
    expect($fasilitas->fresh()->gambar)->toBeNull();
    Storage::disk('public')->assertMissing($foto[1]->foto);
});

it('menghapus foto yang berkas legacynya hilang tanpa menghapus referensi lain', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    $path = 'website/fasilitas/'.str_repeat('c', 40).'.jpg';
    $fasilitas = Fasilitas::create(['nama' => 'Aula', 'slug' => 'aula', 'gambar' => $path]);
    $foto = $fasilitas->foto()->create(['foto' => $path]);
    $lain = Fasilitas::create(['nama' => 'Kelas', 'slug' => 'kelas', 'gambar' => $path]);

    $this->delete(route('website.fasilitas.foto.destroy', [$fasilitas, $foto]))
        ->assertRedirect(route('website.fasilitas.edit', $fasilitas));
    expect($fasilitas->fresh()->gambar)->toBeNull()
        ->and($lain->fresh()->gambar)->toBe($path)
        ->and($fasilitas->foto()->count())->toBe(0);
    Storage::disk('public')->assertMissing($path);
});

it('menolak foto tidak valid tanpa mengubah data atau berkas fasilitas', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    Storage::disk('public')->put('website/fasilitas/utama.jpg', 'utama');
    $fasilitas = Fasilitas::create(['nama' => 'Aula', 'slug' => 'aula', 'gambar' => 'website/fasilitas/utama.jpg']);

    $this->from(route('website.fasilitas.edit', $fasilitas))
        ->put(route('website.fasilitas.update', $fasilitas), [
            'nama' => 'Aula berubah', 'urutan' => 0, 'aktif' => 1,
            'gambar_baru' => [UploadedFile::fake()->image('benar.jpg'), UploadedFile::fake()->create('salah.pdf', 10, 'application/pdf')],
        ])->assertRedirect(route('website.fasilitas.edit', $fasilitas))->assertSessionHasErrors('gambar_baru.1');

    expect($fasilitas->fresh()->nama)->toBe('Aula')
        ->and($fasilitas->foto()->count())->toBe(0);
    Storage::disk('public')->assertExists('website/fasilitas/utama.jpg');
    expect(Storage::disk('public')->allFiles('website/fasilitas'))->toBe(['website/fasilitas/utama.jpg']);

    $this->post(route('website.fasilitas.store'), [
        'nama' => 'Baru', 'urutan' => 0, 'aktif' => 1,
        'gambar_baru' => [UploadedFile::fake()->image('terlalu-besar.jpg')->size(2049)],
    ])->assertSessionHasErrors('gambar_baru.0');
    expect(Fasilitas::count())->toBe(1);
    expect(Storage::disk('public')->allFiles('website/fasilitas'))->toBe(['website/fasilitas/utama.jpg']);
});

it('memigrasikan foto galeri tanpa kehilangan ID atau path dan menolak foto tanpa album', function () {
    $migration = require database_path('migrations/2026_10_14_000001_simplify_foto_galeri_table.php');
    expect(Schema::getColumnListing('foto_galeri'))->toEqualCanonicalizing(['id', 'galeri_id', 'foto', 'created_at', 'updated_at']);
    $galeri = Galeri::create(['judul' => 'Arsip', 'slug' => 'arsip']);
    $foto = $galeri->foto()->create(['foto' => 'arsip/foto-lama.jpg']);
    $migration->down();
    expect(DB::table('foto_galeri')->where('id', $foto->id)->value('gambar'))->toBe('arsip/foto-lama.jpg');
    expect(Schema::getColumnListing('foto_galeri'))->toEqualCanonicalizing(['id', 'galeri_id', 'judul', 'gambar', 'keterangan', 'urutan', 'created_at', 'updated_at']);
    $galeriLain = Galeri::create(['judul' => 'Sementara', 'slug' => 'sementara']);
    $fotoLain = DB::table('foto_galeri')->insertGetId(['galeri_id' => $galeriLain->id, 'judul' => 'Sementara', 'gambar' => 'sementara.jpg']);
    $galeriLain->delete();
    expect(DB::table('foto_galeri')->where('id', $fotoLain)->value('galeri_id'))->toBeNull();
    DB::table('foto_galeri')->where('id', $fotoLain)->delete();
    DB::table('foto_galeri')->insert(['galeri_id' => null, 'judul' => 'Tanpa album', 'gambar' => 'arsip/yatim.jpg']);
    expect(fn () => $migration->up())->toThrow(RuntimeException::class, 'foto tanpa album');
    expect(DB::table('foto_galeri')->count())->toBe(2);
    DB::table('foto_galeri')->whereNull('galeri_id')->delete();
    $migration->up();
    expect(DB::table('foto_galeri')->where('id', $foto->id)->value('foto'))->toBe('arsip/foto-lama.jpg');
    $galeri->delete();
    expect(DB::table('foto_galeri')->where('id', $foto->id)->exists())->toBeFalse();
});

it('memigrasikan kolom foto dan gambar bersamaan sambil mempertahankan path foto yang sudah terisi', function () {
    $migration = require database_path('migrations/2026_10_14_000001_simplify_foto_galeri_table.php');
    $migration->down();
    Schema::table('foto_galeri', fn (\Illuminate\Database\Schema\Blueprint $table) => $table->text('foto')->nullable());
    $galeri = Galeri::create(['judul' => 'Arsip', 'slug' => 'arsip']);
    $id = DB::table('foto_galeri')->insertGetId(['galeri_id' => $galeri->id, 'judul' => 'Foto', 'gambar' => 'lama.jpg']);
    $id2 = DB::table('foto_galeri')->insertGetId(['galeri_id' => $galeri->id, 'judul' => 'Foto lain', 'gambar' => 'lama2.jpg', 'foto' => 'baru.jpg']);
    $migration->up();
    expect(DB::table('foto_galeri')->where('id', $id)->value('foto'))->toBe('lama.jpg')
        ->and(DB::table('foto_galeri')->where('id', $id2)->value('foto'))->toBe('baru.jpg');
});

it('menyimpan banyak foto galeri, menambah foto, dan memperbarui sampul saat foto dihapus', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    $this->post(route('website.galeri.store'), [
        'judul' => 'Kegiatan', 'gambar_baru' => [UploadedFile::fake()->image('satu.jpg'), UploadedFile::fake()->image('dua.png')],
    ])->assertRedirect(route('website.galeri.index'));
    $galeri = Galeri::where('judul', 'Kegiatan')->firstOrFail();
    $foto = $galeri->foto()->get();
    expect($foto)->toHaveCount(2)->and($galeri->gambar_sampul)->toBe($foto[0]->foto);
    $this->get(route('website.galeri.edit', $galeri))->assertOk()
        ->assertSee('form="hapus-foto-'.$foto[0]->id.'"', false)
        ->assertDontSee('Judul Foto')->assertDontSee('Urutan Foto')->assertDontSee('name="gambar_sampul"', false);
    $this->put(route('website.galeri.update', $galeri), [
        'judul' => 'Kegiatan Baru', 'gambar_baru' => [UploadedFile::fake()->image('tiga.webp')],
    ])->assertRedirect(route('website.galeri.index'));
    expect($galeri->fresh()->gambar_sampul)->toBe($foto[0]->foto)->and($galeri->foto()->count())->toBe(3);
    $this->delete(route('website.galeri.foto.destroy', [$galeri, $foto[0]]))->assertRedirect(route('website.galeri.edit', $galeri));
    expect($galeri->fresh()->gambar_sampul)->toBe($foto[1]->foto);
    Storage::disk('public')->assertMissing($foto[0]->foto);
    $this->delete(route('website.galeri.foto.destroy', [$galeri, $foto[1]]));
    $terakhir = $galeri->foto()->first();
    expect($galeri->fresh()->gambar_sampul)->toBe($terakhir->foto);
    $this->delete(route('website.galeri.foto.destroy', [$galeri, $terakhir]));
    expect($galeri->fresh()->gambar_sampul)->toBeNull();
    Storage::disk('public')->assertMissing($terakhir->foto);
});

it('membatasi penghapusan foto pada album terkait dan menjaga foto yang dipakai album lain', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    $path = 'website/galeri/'.str_repeat('c', 40).'.jpg';
    Storage::disk('public')->put($path, 'bersama');
    $galeri = Galeri::create(['judul' => 'Satu', 'slug' => 'satu', 'gambar_sampul' => $path]);
    $lain = Galeri::create(['judul' => 'Dua', 'slug' => 'dua', 'gambar_sampul' => $path]);
    $foto = $galeri->foto()->create(['foto' => $path]);
    $this->delete(route('website.galeri.foto.destroy', [$lain, $foto]))->assertNotFound();
    expect($galeri->foto()->count())->toBe(1);
    $this->delete(route('website.galeri.foto.destroy', [$galeri, $foto]))->assertRedirect(route('website.galeri.edit', $galeri));
    expect($galeri->fresh()->gambar_sampul)->toBeNull();
    Storage::disk('public')->assertExists($path);
    $this->delete(route('website.galeri.destroy', $lain));
    Storage::disk('public')->assertMissing($path);
});

it('menjaga sampul legacy dan berkas yang masih direferensikan', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    $path = 'website/galeri/'.str_repeat('a', 40).'.jpg';
    Storage::disk('public')->put($path, 'sampul lama');
    $galeri = Galeri::create(['judul' => 'Arsip', 'slug' => 'arsip', 'gambar_sampul' => $path]);
    $lain = Galeri::create(['judul' => 'Lain', 'slug' => 'lain', 'gambar_sampul' => $path]);
    $foto = $galeri->foto()->create(['foto' => 'arsip/foto.jpg']);
    $this->get(route('website.galeri.edit', $galeri))->assertOk()->assertSee('id="hapus_gambar_utama"', false)
        ->assertSee('Foto tidak tersedia');
    $this->put(route('website.galeri.update', $galeri), ['judul' => 'Arsip', 'hapus_gambar_utama' => 1]);
    expect($galeri->fresh()->gambar_sampul)->toBe($foto->foto);
    Storage::disk('public')->assertExists($path);
    $this->put(route('website.galeri.update', $lain), ['judul' => 'Lain', 'hapus_gambar_utama' => 1]);
    Storage::disk('public')->assertMissing($path);
    $shared = 'website/galeri/'.str_repeat('b', 40).'.png';
    Storage::disk('public')->put($shared, 'bersama');
    $galeri->foto()->create(['foto' => $shared]);
    $lain->update(['gambar_sampul' => $shared]);
    $this->delete(route('website.galeri.destroy', $galeri))->assertRedirect(route('website.galeri.index'));
    expect($galeri->foto()->count())->toBe(0);
    Storage::disk('public')->assertExists($shared);
    $this->delete(route('website.galeri.destroy', $lain));
    Storage::disk('public')->assertMissing($shared);
});

it('menolak upload galeri tidak valid tanpa mengubah album atau berkas', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    $galeri = Galeri::create(['judul' => 'Arsip', 'slug' => 'arsip']);
    $this->put(route('website.galeri.update', $galeri), [
        'judul' => 'Berubah', 'gambar_baru' => [UploadedFile::fake()->image('benar.jpg'), UploadedFile::fake()->create('salah.pdf', 10, 'application/pdf')],
    ])->assertSessionHasErrors('gambar_baru.1');
    expect($galeri->fresh()->judul)->toBe('Arsip')->and($galeri->foto()->count())->toBe(0);
    $this->post(route('website.galeri.store'), ['judul' => 'Baru', 'gambar_baru' => [UploadedFile::fake()->image('besar.jpg')->size(2049)]])
        ->assertSessionHasErrors('gambar_baru.0');
    $this->post(route('website.galeri.store'), ['judul' => 'Baru', 'gambar_baru' => array_fill(0, 21, UploadedFile::fake()->image('foto.jpg'))])
        ->assertSessionHasErrors('gambar_baru');
    expect(Galeri::count())->toBe(1)->and(Storage::disk('public')->allFiles('website/galeri'))->toBe([]);
});

it('menggunakan host aktif untuk foto album dan path legacy', function () {
    Storage::fake('public');
    config()->set('app.url', 'http://localhost');
    $this->withServerVariables(['HTTP_HOST' => 'smp_mudasi.test'])->actingAs(User::factory()->create(['role' => 'admin']));
    $host = 'http://smp_mudasi.test';
    $this->post($host.route('website.galeri.store', [], false), [
        'judul' => 'Kegiatan', 'gambar_baru' => [UploadedFile::fake()->image('satu.jpg')],
    ])->assertRedirect(route('website.galeri.index'));
    $galeri = Galeri::where('judul', 'Kegiatan')->firstOrFail();
    $path = $galeri->gambar_sampul;
    $this->get($host.route('website.galeri.index', [], false))->assertSee('src="'.$host.'/storage/'.$path.'"', false);
    $this->get($host.route('website.galeri.edit', $galeri, false))->assertSee('src="'.$host.'/storage/'.$path.'"', false)
        ->assertDontSee('src="http://localhost/storage/'.$path.'"', false);
    $galeri->update(['gambar_sampul' => 'arsip/sampul.jpg']);
    $this->get($host.route('website.galeri.index', [], false))->assertSee('src="'.$host.'/arsip/sampul.jpg"', false);
});

it('menambah, mengganti, dan menghapus thumbnail berita tanpa menyisakan berkas', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create(['role' => 'admin']));

    $this->get(route('website.berita.create'))->assertOk()
        ->assertSee('id="tambah-thumbnail"', false)
        ->assertSee('name="gambar"', false)
        ->assertDontSee('name="gambar[]"', false);

    $this->post(route('website.berita.store'), [
        'judul' => 'Berita Sekolah', 'isi' => '<p>Isi berita</p>', 'status' => 'draft',
        'gambar' => UploadedFile::fake()->image('pertama.jpg'),
    ])->assertRedirect(route('website.berita.index'));

    $berita = Berita::where('judul', 'Berita Sekolah')->firstOrFail();
    $pertama = $berita->gambar;
    Storage::disk('public')->assertExists($pertama);
    $this->get(route('website.berita.edit', $berita))->assertOk()
        ->assertSee('id="thumbnail-lama"', false)
        ->assertSee('id="hapus_gambar"', false);

    $this->put(route('website.berita.update', $berita), [
        'judul' => 'Berita Sekolah', 'isi' => '<p>Isi berita</p>', 'status' => 'draft',
        'gambar' => UploadedFile::fake()->image('pengganti.png'),
    ])->assertRedirect(route('website.berita.index'));

    $pengganti = $berita->fresh()->gambar;
    expect($pengganti)->not->toBe($pertama);
    Storage::disk('public')->assertMissing($pertama);
    Storage::disk('public')->assertExists($pengganti);

    $this->put(route('website.berita.update', $berita), [
        'judul' => 'Berita Sekolah', 'isi' => '<p>Isi berita</p>', 'status' => 'draft',
        'hapus_gambar' => '1',
    ])->assertRedirect(route('website.berita.index'));
    expect($berita->fresh()->gambar)->toBeNull();
    Storage::disk('public')->assertMissing($pengganti);
});

it('menjaga thumbnail berita pada validasi gagal dan berkas yang masih direferensikan', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    $path = UploadedFile::fake()->image('bersama.jpg')->store('website/berita', 'public');
    $berita = Berita::create(['judul' => 'Berita Satu', 'slug' => 'berita-satu', 'isi' => '<p>Isi</p>', 'gambar' => $path]);
    $lain = Berita::create(['judul' => 'Berita Dua', 'slug' => 'berita-dua', 'isi' => '<p>Isi</p>', 'gambar' => $path]);

    $this->put(route('website.berita.update', $berita), [
        'judul' => 'Berita Satu', 'isi' => '<p>Isi</p>', 'status' => 'draft',
        'gambar' => UploadedFile::fake()->create('salah.pdf', 10, 'application/pdf'), 'hapus_gambar' => '1',
    ])->assertSessionHasErrors('gambar');
    $this->put(route('website.berita.update', $berita), [
        'judul' => 'Berita Satu', 'isi' => '<p>Isi</p>', 'status' => 'draft', 'hapus_gambar' => 'tidak',
    ])->assertSessionHasErrors('hapus_gambar');
    expect($berita->fresh()->gambar)->toBe($path);
    expect(Storage::disk('public')->allFiles('website/berita'))->toBe([$path]);

    $this->put(route('website.berita.update', $berita), [
        'judul' => 'Berita Satu', 'isi' => '<p>Isi</p>', 'status' => 'draft', 'hapus_gambar' => '1',
    ])->assertRedirect(route('website.berita.index'));
    expect($berita->fresh()->gambar)->toBeNull()
        ->and($lain->fresh()->gambar)->toBe($path);
    Storage::disk('public')->assertExists($path);

    $legacyPath = 'website/berita/contoh-demo.jpg';
    Storage::disk('public')->put($legacyPath, 'demo');
    $lain->update(['gambar' => $legacyPath]);
    $this->put(route('website.berita.update', $lain), [
        'judul' => 'Berita Dua', 'isi' => '<p>Isi</p>', 'status' => 'draft', 'hapus_gambar' => '1',
    ])->assertRedirect(route('website.berita.index'));
    expect($lain->fresh()->gambar)->toBeNull();
    Storage::disk('public')->assertExists($legacyPath);
});

it('menggunakan host permintaan untuk thumbnail berita yang diunggah dan mempertahankan URL legacy', function () {
    Storage::fake('public');
    config()->set('app.url', 'http://localhost');
    $this->withServerVariables(['HTTP_HOST' => 'smp_mudasi.test'])
        ->actingAs(User::factory()->create(['role' => 'admin']));
    $host = 'http://smp_mudasi.test';

    $this->post($host.route('website.berita.store', [], false), [
        'judul' => 'Berita Foto', 'isi' => '<p>Isi</p>', 'status' => 'draft',
        'gambar' => UploadedFile::fake()->image('berita.jpg'),
    ])->assertRedirect(route('website.berita.index'));
    $berita = Berita::where('judul', 'Berita Foto')->firstOrFail();
    foreach (['website.berita.edit', 'website.berita.index'] as $route) {
        $url = $route === 'website.berita.edit' ? route($route, $berita, false) : route($route, [], false);
        $this->get($host.$url)->assertOk()
            ->assertSee('src="'.$host.'/storage/'.$berita->gambar.'"', false)
            ->assertDontSee('src="http://localhost/storage/'.$berita->gambar.'"', false);
    }

    $berita->update(['gambar' => 'images/berita/contoh.jpg']);
    $this->get($host.route('website.berita.edit', $berita, false))->assertOk()
        ->assertSee('src="'.$host.'/images/berita/contoh.jpg"', false);
});

it('mengunggah, mengganti, dan menghapus satu gambar ekstrakurikuler', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create(['role' => 'admin']));

    $this->get(route('website.ekstrakurikuler.create'))->assertOk()
        ->assertSee('id="tambah-gambar"', false)
        ->assertSee('name="gambar"', false)
        ->assertSee('accept="image/jpeg,image/png,image/webp"', false)
        ->assertDontSee('name="gambar[]"', false);

    $this->post(route('website.ekstrakurikuler.store'), [
        'nama' => 'Pramuka', 'aktif' => 1,
        'gambar' => UploadedFile::fake()->image('pertama.jpg'),
    ])->assertRedirect(route('website.ekstrakurikuler.index'));

    $ekstra = Ekstrakurikuler::where('nama', 'Pramuka')->firstOrFail();
    $pertama = $ekstra->gambar;
    Storage::disk('public')->assertExists($pertama);
    $this->get(route('website.ekstrakurikuler.edit', $ekstra))->assertOk()
        ->assertSee('id="gambar-lama"', false)
        ->assertSee('id="hapus_gambar"', false)
        ->assertSee('id="hapus-gambar-baru"', false);

    $this->put(route('website.ekstrakurikuler.update', $ekstra), [
        'nama' => 'Pramuka', 'aktif' => 1, 'hapus_gambar' => 1,
        'gambar' => UploadedFile::fake()->image('pengganti.png'),
    ])->assertRedirect(route('website.ekstrakurikuler.index'));

    $pengganti = $ekstra->fresh()->gambar;
    expect($pengganti)->not->toBe($pertama);
    Storage::disk('public')->assertMissing($pertama);
    Storage::disk('public')->assertExists($pengganti);

    $this->put(route('website.ekstrakurikuler.update', $ekstra), [
        'nama' => 'Pramuka', 'aktif' => 1, 'hapus_gambar' => 1,
    ])->assertRedirect(route('website.ekstrakurikuler.index'));
    expect($ekstra->fresh()->gambar)->toBeNull();
    Storage::disk('public')->assertMissing($pengganti);
});

it('menjaga gambar ekstrakurikuler jika validasi gagal atau masih dipakai', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    $path = UploadedFile::fake()->image('bersama.jpg')->store('website/ekstrakurikuler', 'public');
    $ekstra = Ekstrakurikuler::create(['nama' => 'Pramuka', 'slug' => 'pramuka', 'gambar' => $path]);
    $lain = Ekstrakurikuler::create(['nama' => 'PMR', 'slug' => 'pmr', 'gambar' => $path]);

    $this->from(route('website.ekstrakurikuler.edit', $ekstra))
        ->put(route('website.ekstrakurikuler.update', $ekstra), [
            'nama' => 'Pramuka Baru', 'aktif' => 1, 'hapus_gambar' => 1,
            'gambar' => UploadedFile::fake()->create('salah.pdf', 10, 'application/pdf'),
        ])->assertRedirect(route('website.ekstrakurikuler.edit', $ekstra))->assertSessionHasErrors('gambar');
    $this->put(route('website.ekstrakurikuler.update', $ekstra), [
        'nama' => 'Pramuka', 'aktif' => 1, 'hapus_gambar' => 'tidak',
    ])->assertSessionHasErrors('hapus_gambar');
    expect($ekstra->fresh()->gambar)->toBe($path)
        ->and($ekstra->nama)->toBe('Pramuka');
    expect(Storage::disk('public')->allFiles('website/ekstrakurikuler'))->toBe([$path]);

    $this->put(route('website.ekstrakurikuler.update', $ekstra), [
        'nama' => 'Pramuka', 'aktif' => 1, 'hapus_gambar' => 1,
    ])->assertRedirect(route('website.ekstrakurikuler.index'));
    expect($ekstra->fresh()->gambar)->toBeNull()
        ->and($lain->fresh()->gambar)->toBe($path);
    Storage::disk('public')->assertExists($path);

    $this->put(route('website.ekstrakurikuler.update', $lain), [
        'nama' => 'PMR', 'aktif' => 1, 'hapus_gambar' => 1,
    ])->assertRedirect(route('website.ekstrakurikuler.index'));
    Storage::disk('public')->assertMissing($path);

    $legacyPath = 'website/ekstrakurikuler/contoh-demo.jpg';
    Storage::disk('public')->put($legacyPath, 'arsip');
    $lain->update(['gambar' => $legacyPath]);
    $this->put(route('website.ekstrakurikuler.update', $lain), [
        'nama' => 'PMR', 'aktif' => 1, 'hapus_gambar' => 1,
    ])->assertRedirect(route('website.ekstrakurikuler.index'));
    expect($lain->fresh()->gambar)->toBeNull();
    Storage::disk('public')->assertExists($legacyPath);
});

it('menggunakan host permintaan untuk gambar ekstrakurikuler dan URL legacy', function () {
    Storage::fake('public');
    config()->set('app.url', 'http://localhost');
    $this->withServerVariables(['HTTP_HOST' => 'smp_mudasi.test'])
        ->actingAs(User::factory()->create(['role' => 'admin']));
    $host = 'http://smp_mudasi.test';

    $this->post($host.route('website.ekstrakurikuler.store', [], false), [
        'nama' => 'Pramuka', 'aktif' => 1,
        'gambar' => UploadedFile::fake()->image('pramuka.webp'),
    ])->assertRedirect(route('website.ekstrakurikuler.index'));
    $ekstra = Ekstrakurikuler::where('nama', 'Pramuka')->firstOrFail();
    foreach (['website.ekstrakurikuler.edit', 'website.ekstrakurikuler.index'] as $route) {
        $url = $route === 'website.ekstrakurikuler.edit' ? route($route, $ekstra, false) : route($route, [], false);
        $this->get($host.$url)->assertOk()
            ->assertSee('src="'.$host.'/storage/'.$ekstra->gambar.'"', false)
            ->assertDontSee('src="http://localhost/storage/'.$ekstra->gambar.'"', false);
    }

    $ekstra->update(['gambar' => 'images/ekstrakurikuler/contoh.jpg']);
    foreach (['website.ekstrakurikuler.edit', 'website.ekstrakurikuler.index'] as $route) {
        $url = $route === 'website.ekstrakurikuler.edit' ? route($route, $ekstra, false) : route($route, [], false);
        $this->get($host.$url)->assertOk()
            ->assertSee('src="'.$host.'/images/ekstrakurikuler/contoh.jpg"', false)
            ->assertSee('Foto tidak tersedia');
    }
});
