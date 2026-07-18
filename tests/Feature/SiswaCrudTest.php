<?php

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
});

it('menjalankan CRUD siswa dengan NISN nullable dan menyimpan data wali', function () {
    $this->post(route('master.siswa.store'), [
        'nis' => '1001',
        'nisn' => null,
        'nama_lengkap' => 'Siswa MVP',
        'jenis_kelamin' => 'L',
        'nama_wali' => 'Wali Awal',
        'no_hp_wali' => '08123456789',
        'status' => 'aktif',
    ])->assertRedirect(route('master.siswa.index'));

    $siswa = Siswa::sole();
    expect($siswa->nisn)->toBeNull()
        ->and($siswa->nama_wali)->toBe('Wali Awal')
        ->and($siswa->no_hp_wali)->toBe('08123456789');

    $this->get(route('master.siswa.show', $siswa))->assertOk();

    $this->put(route('master.siswa.update', $siswa), [
        'nis' => '1001',
        'nisn' => null,
        'nama_lengkap' => 'Siswa Diperbarui',
        'jenis_kelamin' => 'L',
        'nama_wali' => 'Wali Baru',
        'no_hp_wali' => '08987654321',
        'status' => 'aktif',
    ])->assertRedirect(route('master.siswa.index'));

    $this->assertDatabaseHas('siswa', [
        'id' => $siswa->id,
        'nisn' => null,
        'nama_lengkap' => 'Siswa Diperbarui',
        'nama_wali' => 'Wali Baru',
        'no_hp_wali' => '08987654321',
    ]);

    $this->delete(route('master.siswa.destroy', $siswa))->assertRedirect(route('master.siswa.index'));
    $this->assertDatabaseMissing('siswa', ['id' => $siswa->id]);
});

it('mengizinkan lebih dari satu siswa tanpa NISN', function () {
    foreach ([['1001', 'Siswa Satu'], ['1002', 'Siswa Dua']] as [$nis, $nama]) {
        $this->post(route('master.siswa.store'), [
            'nis' => $nis,
            'nisn' => null,
            'nama_lengkap' => $nama,
            'jenis_kelamin' => 'P',
            'status' => 'aktif',
        ])->assertSessionHasNoErrors();
    }

    expect(Siswa::whereNull('nisn')->count())->toBe(2);
});
