<?php

use App\Helpers\MenuHelper;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

function laporanGuruUser(string $role): User
{
    return User::create(['name' => $role, 'username' => 'laporan_guru_'.$role, 'email' => $role.'@test.id', 'password' => Hash::make('password123'), 'role' => $role]);
}

function laporanGuruCreate(string $name, bool $aktif = true, ?string $identity = null): int
{
    return DB::table('guru')->insertGetId(['nama_lengkap' => $name, 'nomor_indentitas' => $identity, 'jenis_kelamin' => 'L', 'aktif' => $aktif]);
}

function laporanGuruRecord(int $guruId, string $tanggal, string $status, ?string $pulang = null): void
{
    DB::table('presensi_guru')->insert(['guru_id' => $guruId, 'tanggal' => $tanggal, 'waktu_masuk' => '07:05:00', 'status' => $status, 'metode_masuk' => 'rfid', 'waktu_pulang' => $pulang, 'status_pulang' => $pulang ? 'tepat_waktu' : null, 'metode_pulang' => $pulang ? 'rfid' : null]);
}

it('melindungi seluruh rute laporan guru dan menyembunyikan menu dari wali kelas', function () {
    foreach (['laporan-guru.index', 'laporan-guru.csv', 'laporan-guru.print'] as $name) {
        expect(Route::getRoutes()->getByName($name)->gatherMiddleware())->toContain('auth', 'role:admin,tu');
        $this->get(route($name))->assertRedirect();
    }
    $wali = laporanGuruUser('wali_kelas');
    $this->actingAs($wali);
    foreach (['laporan-guru.index', 'laporan-guru.csv', 'laporan-guru.print'] as $name) {
        $this->get(route($name))->assertForbidden();
    }
    expect(collect(MenuHelper::getMenuGroups()[2]['items'][0]['subItems'])->pluck('name'))->not->toContain('Laporan Presensi Guru');
    $this->actingAs(laporanGuruUser('tu'))->get(route('laporan-guru.index'))->assertOk();
    expect(collect(MenuHelper::getMenuGroups()[2]['items'][0]['subItems'])->pluck('name'))->toContain('Laporan Presensi Guru');
});

it('menampilkan guru aktif tanpa scan dan status yang tersimpan pada laporan harian', function () {
    $this->travelTo(\Carbon\Carbon::parse('2026-07-28 10:00:00', 'Asia/Jakarta'));
    $admin = laporanGuruUser('admin');
    $a = laporanGuruCreate('Ani', true, 'G001');
    laporanGuruCreate('Budi Belum Scan', true, 'G002');
    laporanGuruCreate('Guru Nonaktif', false);
    laporanGuruRecord($a, '2026-07-27', 'terlambat', '14:00:00');
    $this->actingAs($admin)->get(route('laporan-guru.index', ['tanggal' => '2026-07-27']))
        ->assertOk()->assertSee('G001')->assertSee('Budi Belum Scan')->assertSee('Belum tercatat')
        ->assertSee('Terlambat')->assertSee('07:05:00')->assertSee('14:00:00')->assertDontSee('Guru Nonaktif')
        ->assertDontSee('Alpa');
    laporanGuruRecord($a, '2026-07-29', 'hadir');
    $this->get(route('laporan-guru.index', ['tanggal' => '2026-07-29']))->assertOk()->assertDontSee('Hadir');
});

it('menghitung hanya catatan bulan yang tidak di masa depan tanpa membuat alpa', function () {
    $this->travelTo(\Carbon\Carbon::parse('2026-07-28 10:00:00', 'Asia/Jakarta'));
    $admin = laporanGuruUser('admin');
    $a = laporanGuruCreate('Ani');
    laporanGuruCreate('Budi Tanpa Scan');
    laporanGuruRecord($a, '2026-07-01', 'hadir', '14:00:00');
    laporanGuruRecord($a, '2026-07-02', 'terlambat');
    laporanGuruRecord($a, '2026-07-03', 'hadir');
    laporanGuruRecord($a, '2026-07-29', 'hadir');
    laporanGuruRecord($a, '2026-08-01', 'terlambat');
    $this->actingAs($admin)->get(route('laporan-guru.index', ['tab' => 'bulanan', 'bulan' => '2026-07']))
        ->assertOk()->assertSee('Budi Tanpa Scan')->assertDontSee('Alpa')
        ->assertViewHas('rows', function ($rows) {
            return $rows->first()->hadir === 2 && $rows->first()->terlambat === 1
                && $rows->first()->belum_pulang === 2
                && $rows->last()->hadir === 0 && $rows->last()->terlambat === 0
                && $rows->last()->belum_pulang === 0;
        });
    $this->get(route('laporan-guru.index', ['tab' => 'bulanan', 'bulan' => '2026-08']))
        ->assertOk()->assertDontSee('<td class="p-3 text-center">1</td>', false);
});

it('mengekspor CSV dan mencetak data dari filter yang sama', function () {
    $this->travelTo(\Carbon\Carbon::parse('2026-07-28 10:00:00', 'Asia/Jakarta'));
    $admin = laporanGuruUser('admin');
    $a = laporanGuruCreate('Ani', true, 'G001');
    laporanGuruCreate('Budi Belum Scan');
    laporanGuruRecord($a, '2026-07-27', 'terlambat');
    $this->actingAs($admin);
    $params = ['tab' => 'harian', 'tanggal' => '2026-07-27'];
    $csv = $this->get(route('laporan-guru.csv', $params))->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
    $content = $csv->streamedContent();
    expect($content)->toStartWith("\xEF\xBB\xBF")
        ->toContain('"ID Guru";"Nomor Identitas";Nama;"Status Masuk"')
        ->toContain('G001;Ani;Terlambat;07:05:00')
        ->toContain('"Budi Belum Scan";"Belum tercatat"');
    $this->get(route('laporan-guru.print', $params))->assertOk()->assertSee('G001')->assertSee('Budi Belum Scan')->assertSee('Belum tercatat')->assertSee('Terlambat');
    $monthly = ['tab' => 'bulanan', 'bulan' => '2026-07'];
    expect($this->get(route('laporan-guru.csv', $monthly))->assertOk()->streamedContent())->toContain('Hadir;Terlambat;"Belum Pulang"')->toContain('Ani;0;1;1');
    $this->get(route('laporan-guru.print', $monthly))->assertOk()->assertSee('Belum Pulang')->assertSee('Ani');
});

it('memvalidasi tanggal dan bulan pada semua format laporan', function () {
    $this->actingAs(laporanGuruUser('admin'));
    foreach (['laporan-guru.index', 'laporan-guru.csv', 'laporan-guru.print'] as $name) {
        $this->get(route($name, ['tanggal' => '2026-02-30']))->assertSessionHasErrors('tanggal');
        $this->get(route($name, ['tab' => 'bulanan', 'bulan' => '2026-13']))->assertSessionHasErrors('bulan');
        $this->get(route($name, ['tab' => 'lain']))->assertSessionHasErrors('tab');
    }
});
