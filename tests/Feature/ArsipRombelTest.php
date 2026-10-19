<?php

use App\Models\Guru;
use App\Models\Rombel;
use App\Models\RombelSiswa;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
});

it('menampilkan arsip tanpa aksi mutasi dan memilih periode aktif secara bawaan', function () {
    $archive = TahunAjaran::create(['nama' => '2025/2026', 'semester' => 1, 'is_aktif' => false]);
    $active = TahunAjaran::create(['nama' => '2026/2027', 'semester' => 1, 'is_aktif' => true]);
    $oldClass = Rombel::create(['nama' => 'Arsip 7', 'tingkat' => '7', 'tahun_ajaran_id' => $archive->id]);
    Rombel::create(['nama' => 'Aktif 7', 'tingkat' => '7', 'tahun_ajaran_id' => $active->id]);

    $this->get(route('master.rombel.index'))->assertOk()->assertSee('Aktif 7')->assertDontSee('Arsip 7');
    $this->get(route('master.rombel.index', ['tahun_ajaran_id' => $archive->id]))
        ->assertOk()->assertSee('Arsip 7')->assertSee('Arsip')
        ->assertDontSee(route('master.rombel.edit', $oldClass->id))
        ->assertDontSee('method="POST" class="inline-block"')
        ->assertDontSee(route('master.rombel.create'));
    $this->get(route('master.rombel.show', $oldClass))->assertOk()->assertSee('Arsip')
        ->assertDontSee(route('master.rombel.edit', $oldClass));
    $this->get(route('rombel-siswa.index', ['tahun_ajaran_id' => $archive->id, 'rombel_id' => $oldClass->id]))
        ->assertOk()->assertSee('Arsip')->assertDontSee(route('rombel-siswa.create', ['rombel_id' => $oldClass->id]));
});

it('menampilkan snapshot historis tanpa mengizinkan perubahan meskipun tahunnya aktif', function () {
    $tahun = TahunAjaran::create(['nama' => '2026/2027', 'semester' => 2, 'is_aktif' => true]);
    $snapshot = Rombel::create(['nama' => '7 A', 'tingkat' => '7', 'tahun_ajaran_id' => $tahun->id, 'semester' => 1]);
    $siswa = Siswa::create(['nis' => '90002', 'nama_lengkap' => 'Murid Arsip', 'jenis_kelamin' => 'P', 'status' => 'aktif']);
    $anggota = RombelSiswa::create(['rombel_id' => $snapshot->id, 'siswa_id' => $siswa->id, 'tanggal_masuk' => '2026-07-15']);

    $this->get(route('master.rombel.index'))->assertOk()->assertSee('Snapshot Ganjil')
        ->assertDontSee(route('master.rombel.edit', $snapshot));
    $this->get(route('rombel-siswa.index', ['tahun_ajaran_id' => $tahun->id, 'rombel_id' => $snapshot->id]))
        ->assertOk()->assertSee('Murid Arsip')->assertDontSee(route('rombel-siswa.create', ['rombel_id' => $snapshot->id]));
    $this->get(route('master.rombel.edit', $snapshot))->assertForbidden();
    $this->delete(route('master.rombel.destroy', $snapshot))->assertForbidden();
    $this->post(route('rombel-siswa.store'), ['rombel_id' => $snapshot->id, 'siswa_id' => [$siswa->id], 'tanggal_masuk' => '2027-01-01'])->assertForbidden();
    $this->delete(route('rombel-siswa.destroy', $anggota), ['tanggal_keluar' => '2027-01-01'])->assertForbidden();
});

it('menolak pembuatan kelas pada arsip dan tanpa periode aktif', function () {
    $archive = TahunAjaran::create(['nama' => '2025/2026', 'semester' => 1, 'is_aktif' => false]);
    $this->get(route('master.rombel.create'))->assertForbidden();
    $this->post(route('master.rombel.store'), ['tahun_ajaran_id' => $archive->id, 'tingkat' => '7', 'nama' => 'Baru'])
        ->assertSessionHasErrors('tahun_ajaran_id');
    $active = TahunAjaran::create(['nama' => '2026/2027', 'semester' => 1, 'is_aktif' => true]);
    $this->post(route('master.rombel.store'), ['tahun_ajaran_id' => $archive->id, 'tingkat' => '7', 'nama' => 'Baru'])
        ->assertSessionHasErrors('tahun_ajaran_id');
    $this->get(route('master.rombel.create'))->assertOk();
    expect(Rombel::count())->toBe(0);
});

it('menolak edit hapus dan pemindahan kelas arsip tetapi mengizinkan kelas aktif', function () {
    $archive = TahunAjaran::create(['nama' => '2025/2026', 'semester' => 1, 'is_aktif' => false]);
    $active = TahunAjaran::create(['nama' => '2026/2027', 'semester' => 1, 'is_aktif' => true]);
    $guru = Guru::create(['nama_lengkap' => 'Wali Kelas', 'jenis_kelamin' => 'L']);
    $oldClass = Rombel::create(['nama' => 'Lama', 'tingkat' => '7', 'tahun_ajaran_id' => $archive->id]);
    $newClass = Rombel::create(['nama' => 'Baru', 'tingkat' => '7', 'tahun_ajaran_id' => $active->id]);
    $payload = ['tahun_ajaran_id' => $active->id, 'tingkat' => '7', 'nama' => 'Diubah', 'wali_guru_id' => $guru->id];

    $this->get(route('master.rombel.edit', $oldClass))->assertForbidden();
    $this->put(route('master.rombel.update', $oldClass), $payload)->assertForbidden();
    $this->delete(route('master.rombel.destroy', $oldClass))->assertForbidden();
    $this->get(route('master.rombel.edit', $newClass))->assertOk()->assertSee('Periode aktif');
    $this->put(route('master.rombel.update', $newClass), array_merge($payload, ['tahun_ajaran_id' => $archive->id]))
        ->assertSessionHasErrors('tahun_ajaran_id');
    $this->put(route('master.rombel.update', $newClass), $payload)->assertSessionHasNoErrors();
    expect($newClass->fresh()->nama)->toBe('Diubah')
        ->and($newClass->fresh()->tahun_ajaran_id)->toBe($active->id)
        ->and($oldClass->fresh()->nama)->toBe('Lama');
    $this->delete(route('master.rombel.destroy', $newClass))->assertSessionHasNoErrors();
    $this->post(route('master.rombel.store'), $payload)->assertSessionHasNoErrors();
    expect(Rombel::where('tahun_ajaran_id', $active->id)->count())->toBe(1);
    $currentClass = Rombel::where('tahun_ajaran_id', $active->id)->firstOrFail();
    $active->update(['is_aktif' => false]);
    $this->get(route('master.rombel.edit', $currentClass))->assertForbidden();
    $this->put(route('master.rombel.update', $currentClass), $payload)->assertForbidden();
    $this->delete(route('master.rombel.destroy', $currentClass))->assertForbidden();
});

it('menolak tambah dan tutup anggota arsip serta mengizinkan anggota periode aktif', function () {
    $archive = TahunAjaran::create(['nama' => '2025/2026', 'semester' => 1, 'is_aktif' => false]);
    $active = TahunAjaran::create(['nama' => '2026/2027', 'semester' => 1, 'is_aktif' => true]);
    $oldClass = Rombel::create(['nama' => 'Lama', 'tingkat' => '7', 'tahun_ajaran_id' => $archive->id]);
    $newClass = Rombel::create(['nama' => 'Baru', 'tingkat' => '7', 'tahun_ajaran_id' => $active->id]);
    $student = Siswa::create(['nis' => '90001', 'nama_lengkap' => 'Murid', 'jenis_kelamin' => 'P', 'status' => 'aktif']);
    $oldMember = RombelSiswa::create(['rombel_id' => $oldClass->id, 'siswa_id' => $student->id, 'tanggal_masuk' => '2025-07-15']);
    $payload = ['siswa_id' => [$student->id], 'tanggal_masuk' => '2026-07-15'];

    $this->get(route('rombel-siswa.create', ['rombel_id' => $oldClass->id]))->assertForbidden();
    $this->post(route('rombel-siswa.store'), array_merge($payload, ['rombel_id' => $oldClass->id]))->assertForbidden();
    $this->delete(route('rombel-siswa.destroy', $oldMember), ['tanggal_keluar' => '2026-07-15'])->assertForbidden();
    $this->get(route('rombel-siswa.create', ['rombel_id' => $newClass->id]))->assertOk();
    $this->post(route('rombel-siswa.store'), array_merge($payload, ['rombel_id' => $newClass->id]))->assertSessionHasNoErrors();
    $newMember = RombelSiswa::where('rombel_id', $newClass->id)->firstOrFail();
    $this->delete(route('rombel-siswa.destroy', $newMember), ['tanggal_keluar' => '2026-07-16'])->assertSessionHasNoErrors();
    expect($oldMember->fresh()->tanggal_keluar)->toBeNull()
        ->and($newMember->fresh()->tanggal_keluar)->not->toBeNull();
    $active->update(['is_aktif' => false]);
    $this->get(route('rombel-siswa.create', ['rombel_id' => $newClass->id]))->assertForbidden();
    $this->post(route('rombel-siswa.store'), array_merge($payload, ['rombel_id' => $newClass->id]))->assertForbidden();
    $this->delete(route('rombel-siswa.destroy', $newMember), ['tanggal_keluar' => '2026-07-17'])->assertForbidden();
});
