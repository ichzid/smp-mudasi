<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

it('melindungi seluruh route laporan dengan auth dan role laporan', function () {
    foreach (['laporan.index', 'laporan.csv', 'laporan.print'] as $name) {
        $route = Route::getRoutes()->getByName($name);

        expect($route)->not->toBeNull()
            ->and($route->gatherMiddleware())->toContain('auth', 'role:admin,tu,wali_kelas');
    }
});

it('laporan harian dan bulanan menyertakan anggota tanpa presensi', function () {
    $admin = User::create(['name' => 'Admin', 'username' => 'admin_laporan', 'email' => 'laporan@test.id', 'password' => Hash::make(fake()->password(12)), 'role' => 'admin']);
    $ta = DB::table('tahun_ajaran')->insertGetId(['nama' => '2026/2027', 'semester' => 1, 'is_aktif' => true]);
    $rombel = DB::table('rombel')->insertGetId(['nama' => 'A', 'tingkat' => '7', 'tahun_ajaran_id' => $ta]);
    $siswa = DB::table('siswa')->insertGetId(['nis' => '1001', 'nama_lengkap' => 'Anggota Tanpa Presensi', 'jenis_kelamin' => 'L', 'status' => 'aktif']);
    DB::table('rombel_siswa')->insert(['rombel_id' => $rombel, 'siswa_id' => $siswa, 'tanggal_masuk' => '2026-07-01']);

    $params = ['tahun_ajaran_id' => $ta, 'rombel_id' => $rombel];
    $this->actingAs($admin)->get(route('laporan.index', $params + ['tab' => 'harian', 'tanggal' => '2026-07-27']))
        ->assertOk()->assertSee('Anggota Tanpa Presensi');
    $this->actingAs($admin)->get(route('laporan.index', $params + ['tab' => 'bulanan', 'bulan' => '2026-07']))
        ->assertOk()->assertSee('Anggota Tanpa Presensi')->assertSee('<td class="text-center">0</td>', false);
});
