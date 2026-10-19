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
    $rombelA = Rombel::create(['nama' => '7 A', 'tingkat' => '7', 'tahun_ajaran_id' => $tahun->id]);
    $rombelB = Rombel::create(['nama' => '7 B', 'tingkat' => '7', 'tahun_ajaran_id' => $tahun->id]);
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

it('mempertahankan anggota tahunan saat semester aktif berganti dan menolak overlap lintas semester', function () {
    [$tahun, $rombelA, $rombelB, $siswa] = dataRombelSiswa();
    RombelSiswa::create(['rombel_id' => $rombelA->id, 'siswa_id' => $siswa->id, 'tanggal_masuk' => '2026-07-01']);
    $tahun->update(['semester' => 2]);

    $this->get(route('rombel-siswa.index', ['tahun_ajaran_id' => $tahun->id, 'rombel_id' => $rombelA->id]))
        ->assertOk()->assertSee('Siswa Mutasi');
    $this->post(route('rombel-siswa.store'), [
        'rombel_id' => $rombelB->id, 'siswa_id' => [$siswa->id], 'tanggal_masuk' => '2027-01-10',
    ])->assertSessionHasErrors('siswa_id');
    expect(RombelSiswa::count())->toBe(1);
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
