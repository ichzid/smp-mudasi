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

function periodeSalin(): array
{
    $source = TahunAjaran::create(['nama' => '2025/2026', 'semester' => 2, 'is_aktif' => false]);
    $target = TahunAjaran::create(['nama' => '2026/2027', 'semester' => 1, 'is_aktif' => true]);
    $class = Rombel::create(['nama' => '7 A', 'tingkat' => '7', 'tahun_ajaran_id' => $source->id, 'semester' => 2]);

    return [$source, $target, $class];
}

function kirimSalin(TahunAjaran $source, TahunAjaran $target)
{
    return test()->post(route('master.rombel.salin.store'), [
        'source_id' => $source->id, 'target_id' => $target->id,
    ]);
}

it('menyalin struktur kelas tahunan tanpa memindah anggota dan menjaga snapshot', function () {
    [$source, $target, $class] = periodeSalin();
    Rombel::create(['nama' => '7 A', 'tingkat' => '7', 'tahun_ajaran_id' => $source->id, 'semester' => 1]);
    Rombel::create(['nama' => '8 B', 'tingkat' => '8', 'tahun_ajaran_id' => $source->id]);
    $student = Siswa::create(['nis' => '4100', 'nama_lengkap' => 'Siswa', 'jenis_kelamin' => 'P', 'status' => 'aktif']);
    RombelSiswa::create(['rombel_id' => $class->id, 'siswa_id' => $student->id, 'tanggal_masuk' => '2025-07-15']);

    $this->get(route('master.rombel.salin.create'))->assertOk()->assertSee('2025/2026')->assertSee('2026/2027');
    kirimSalin($source, $target)->assertSessionHasNoErrors()->assertRedirect(route('master.rombel.index'));
    expect(Rombel::where('tahun_ajaran_id', $target->id)->count())->toBe(2)
        ->and(Rombel::where('tahun_ajaran_id', $target->id)->whereNull('semester')->count())->toBe(2)
        ->and(RombelSiswa::count())->toBe(1)
        ->and($class->fresh()->semester)->toBe(2);
});

it('menolak penyalinan ulang dan tujuan yang sudah memiliki kelas', function () {
    [$source, $target] = periodeSalin();
    Rombel::create(['nama' => 'Manual', 'tingkat' => '7', 'tahun_ajaran_id' => $target->id]);
    kirimSalin($source, $target)->assertSessionHasErrors('target_id');
    Rombel::where('tahun_ajaran_id', $target->id)->delete();
    kirimSalin($source, $target)->assertSessionHasNoErrors();
    kirimSalin($source, $target)->assertSessionHasErrors('target_id');
});

it('menolak sumber bukan tahun sebelumnya dan tujuan nonaktif', function () {
    [$source, $target] = periodeSalin();
    $other = TahunAjaran::create(['nama' => '2024/2025', 'semester' => 1, 'is_aktif' => false]);
    kirimSalin($other, $target)->assertSessionHasErrors('target_id');
    kirimSalin($target, $target)->assertSessionHasErrors('target_id');
    $target->update(['is_aktif' => false]);
    kirimSalin($source, $target)->assertSessionHasErrors('target_id');
    expect(Rombel::count())->toBe(1);
});

it('menolak sumber kosong dan wali kelas ganda secara atomik', function () {
    [$source, $target, $class] = periodeSalin();
    $class->delete();
    kirimSalin($source, $target)->assertSessionHasErrors('source_id');
    $guru = \App\Models\Guru::create(['nama_lengkap' => 'Wali', 'jenis_kelamin' => 'L']);
    Rombel::create(['nama' => '7 A', 'tingkat' => '7', 'wali_guru_id' => $guru->id, 'tahun_ajaran_id' => $source->id]);
    Rombel::create(['nama' => '8 B', 'tingkat' => '8', 'wali_guru_id' => $guru->id, 'tahun_ajaran_id' => $source->id]);
    kirimSalin($source, $target)->assertSessionHasErrors('source_id');
    expect(Rombel::where('tahun_ajaran_id', $target->id)->count())->toBe(0);
});
