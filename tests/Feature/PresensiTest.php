<?php

use App\Models\KartuRfid;
use App\Models\Presensi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function userPresensi(string $role): User
{
    return User::create([
        'name' => $role,
        'email' => $role.uniqid().'@example.test',
        'password' => Hash::make('password'),
        'role' => $role,
    ]);
}

function dataPresensi(?User $wali = null): array
{
    $tahunId = DB::table('tahun_ajaran')->insertGetId([
        'nama' => '2026/2027', 'semester' => 1, 'is_aktif' => true,
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $guruId = DB::table('guru')->insertGetId([
        'nip' => uniqid(), 'nama_lengkap' => 'Wali', 'jenis_kelamin' => 'L',
        'user_id' => $wali?->id, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $rombelId = DB::table('rombel')->insertGetId([
        'nama' => 'VII A', 'tingkat' => 'VII', 'tahun_ajaran_id' => $tahunId,
        'wali_guru_id' => $guruId, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $siswaId = DB::table('siswa')->insertGetId([
        'nis' => uniqid(), 'nisn' => uniqid(), 'nama_lengkap' => 'Siswa Aktif',
        'jenis_kelamin' => 'L', 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now(),
    ]);
    DB::table('rombel_siswa')->insert([
        'rombel_id' => $rombelId, 'siswa_id' => $siswaId, 'tanggal_masuk' => now()->subMonth()->toDateString(),
        'tanggal_keluar' => null, 'created_at' => now(), 'updated_at' => now(),
    ]);

    return compact('tahunId', 'rombelId', 'siswaId');
}

test('presensi manual menolak key siswa yang bukan seluruh anggota aktif rombel', function () {
    $admin = userPresensi('admin');
    $data = dataPresensi();

    $this->actingAs($admin)->post(route('presensi.store'), [
        'rombel_id' => $data['rombelId'],
        'tanggal' => now()->toDateString(),
        'presensi' => [999 => 'hadir'],
    ])->assertSessionHasErrors('presensi');

    expect(Presensi::count())->toBe(0);
});

test('koreksi manual record rfid mempertahankan metode dan waktu scan', function () {
    $admin = userPresensi('admin');
    $data = dataPresensi();
    $presensi = Presensi::create([
        'siswa_id' => $data['siswaId'], 'rombel_id' => $data['rombelId'],
        'tanggal' => now()->toDateString(), 'waktu_scan' => '06:55:00',
        'status' => 'hadir', 'metode' => 'rfid', 'dicatat_oleh' => null,
    ]);

    $this->actingAs($admin)->post(route('presensi.store'), [
        'rombel_id' => $data['rombelId'], 'tanggal' => now()->toDateString(),
        'presensi' => [$data['siswaId'] => 'izin'],
    ])->assertRedirect();

    $presensi->refresh();
    expect($presensi->status)->toBe('izin')
        ->and($presensi->metode)->toBe('rfid')
        ->and($presensi->waktu_scan)->toBe('06:55:00')
        ->and($presensi->dicatat_oleh)->toBe($admin->id);
});

test('wali kelas tidak dapat membuka rombel wali lain', function () {
    $wali = userPresensi('wali_kelas');
    $data = dataPresensi();

    $this->actingAs($wali)->get(route('presensi.index', [
        'tahun_ajaran_id' => $data['tahunId'], 'rombel_id' => $data['rombelId'],
    ]))->assertForbidden();
});

test('scan uid dinormalisasi dan scan kedua tidak mengubah record pertama', function () {
    $data = dataPresensi();
    KartuRfid::create([
        'siswa_id' => $data['siswaId'], 'kode_uid' => 'AB12CD34',
        'status' => 'aktif', 'diterbitkan_pada' => now()->toDateString(),
    ]);

    $this->postJson(route('presensi.scan'), ['kode_uid' => 'ab12cd34'])->assertOk()
        ->assertJsonPath('data.pesan', 'Berhasil melakukan presensi.');
    $pertama = Presensi::first();

    $this->travel(10)->minutes();
    $this->postJson(route('presensi.scan'), ['kode_uid' => 'AB12CD34'])->assertOk()
        ->assertJsonPath('data.pesan', 'Anda sudah melakukan presensi hari ini.');

    $pertama->refresh();
    expect(Presensi::count())->toBe(1)
        ->and($pertama->waktu_scan)->toBe($pertama->getOriginal('waktu_scan'))
        ->and($pertama->metode)->toBe('rfid');
});
