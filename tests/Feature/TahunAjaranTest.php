<?php

use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
});

it('mempertahankan tepat satu tahun ajaran aktif saat store dan update', function () {
    $lama = TahunAjaran::create(['nama' => '2025/2026', 'semester' => 1, 'is_aktif' => true]);

    $this->post(route('master.tahun-ajaran.store'), [
        'nama' => '2025/2026',
        'semester' => 2,
        'is_aktif' => true,
    ])->assertSessionHasNoErrors();

    $baru = TahunAjaran::where('semester', 2)->sole();
    expect(TahunAjaran::where('is_aktif', true)->count())->toBe(1)
        ->and($lama->fresh()->is_aktif)->toBeFalse()
        ->and($baru->is_aktif)->toBeTrue();

    $this->put(route('master.tahun-ajaran.update', $lama), [
        'nama' => '2025/2026',
        'semester' => 1,
        'is_aktif' => true,
    ])->assertSessionHasNoErrors();

    expect(TahunAjaran::where('is_aktif', true)->count())->toBe(1)
        ->and($lama->fresh()->is_aktif)->toBeTrue()
        ->and($baru->fresh()->is_aktif)->toBeFalse();
});

it('menolak kombinasi nama dan semester duplikat serta menjaganya di database', function () {
    TahunAjaran::create(['nama' => '2026/2027', 'semester' => 1, 'is_aktif' => false]);

    $this->post(route('master.tahun-ajaran.store'), [
        'nama' => '2026/2027',
        'semester' => 1,
        'is_aktif' => false,
    ])->assertSessionHasErrors('nama');

    expect(fn () => DB::table('tahun_ajaran')->insert([
        'nama' => '2026/2027',
        'semester' => 1,
        'is_aktif' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]))->toThrow(QueryException::class);
});
