<?php

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

function dataRombelSiswa(): array
{
    $tahun = TahunAjaran::create(['nama' => '2026/2027', 'semester' => 1, 'is_aktif' => true]);
    $rombelA = Rombel::create(['nama' => 'VII A', 'tingkat' => 'VII', 'tahun_ajaran_id' => $tahun->id]);
    $rombelB = Rombel::create(['nama' => 'VII B', 'tingkat' => 'VII', 'tahun_ajaran_id' => $tahun->id]);
    $siswa = Siswa::create(['nis' => '3001', 'nama_lengkap' => 'Siswa Mutasi', 'jenis_kelamin' => 'P', 'status' => 'aktif']);

    return [$tahun, $rombelA, $rombelB, $siswa];
}

it('menutup keanggotaan lama dan mempertahankan histori saat mutasi', function () {
    [, $rombelA, $rombelB, $siswa] = dataRombelSiswa();
    $lama = RombelSiswa::create([
        'rombel_id' => $rombelA->id,
        'siswa_id' => $siswa->id,
        'tanggal_masuk' => '2026-07-01',
    ]);

    $this->delete(route('rombel-siswa.destroy', $lama), [
        'tanggal_keluar' => '2026-07-15',
    ])->assertSessionHasNoErrors();

    $this->post(route('rombel-siswa.store'), [
        'rombel_id' => $rombelB->id,
        'siswa_id' => [$siswa->id],
        'tanggal_masuk' => '2026-07-16',
    ])->assertSessionHasNoErrors();

    expect(RombelSiswa::count())->toBe(2)
        ->and($lama->fresh()->tanggal_keluar->toDateString())->toBe('2026-07-15');
    $this->assertDatabaseHas('rombel_siswa', [
        'rombel_id' => $rombelB->id,
        'siswa_id' => $siswa->id,
        'tanggal_masuk' => '2026-07-16 00:00:00',
        'tanggal_keluar' => null,
    ]);
});

it('menolak keanggotaan yang overlap pada tahun ajaran yang sama', function () {
    [, $rombelA, $rombelB, $siswa] = dataRombelSiswa();
    RombelSiswa::create([
        'rombel_id' => $rombelA->id,
        'siswa_id' => $siswa->id,
        'tanggal_masuk' => '2026-07-01',
        'tanggal_keluar' => '2026-07-20',
    ]);

    $this->post(route('rombel-siswa.store'), [
        'rombel_id' => $rombelB->id,
        'siswa_id' => [$siswa->id],
        'tanggal_masuk' => '2026-07-20',
    ])->assertSessionHasErrors('siswa_id');

    expect(RombelSiswa::count())->toBe(1);
});
