<?php

use App\Models\KartuRfid;
use App\Models\Presensi;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\JadwalPresensiSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);
afterEach(fn () => Carbon::setTestNow());
function userPresensi(string $role): User
{
    return User::create(['name' => $role, 'username' => $role.uniqid(), 'email' => $role.uniqid().'@test.id', 'password' => Hash::make(fake()->password(12)), 'role' => $role]);
}
function dataPresensi(?User $wali = null): array
{
    $ta = DB::table('tahun_ajaran')->insertGetId(['nama' => '2026/2027', 'semester' => 1, 'is_aktif' => true]);
    $guru = DB::table('guru')->insertGetId(['nip' => uniqid(), 'nama_lengkap' => 'Wali', 'jenis_kelamin' => 'L', 'user_id' => $wali?->id]);
    $rombel = DB::table('rombel')->insertGetId(['nama' => 'A', 'tingkat' => '7', 'tahun_ajaran_id' => $ta, 'wali_guru_id' => $guru]);
    $siswa = DB::table('siswa')->insertGetId(['nis' => uniqid(), 'nama_lengkap' => 'Siswa', 'jenis_kelamin' => 'L', 'status' => 'aktif']);
    DB::table('rombel_siswa')->insert(['rombel_id' => $rombel, 'siswa_id' => $siswa, 'tanggal_masuk' => '2026-07-01']);
    KartuRfid::create(['siswa_id' => $siswa, 'kode_uid' => 'AB12CD34', 'status' => 'aktif', 'diterbitkan_pada' => '2026-07-01']);

    return compact('ta', 'rombel', 'siswa');
}

test('admin dapat memperbarui jadwal dan role lain ditolak', function () {
    $admin = userPresensi('admin');
    $tu = userPresensi('tu');
    $jadwal = DB::table('jadwal_presensi')->where('hari', 1)->first();
    $payload = ['hari' => 1, 'is_aktif' => 1, 'jam_mulai_masuk' => '06:00', 'batas_terlambat' => '07:15', 'jam_mulai_pulang' => '13:30'];
    $this->actingAs($tu)->put(route('pengaturan-presensi.update', $jadwal->id), $payload)->assertForbidden();
    $this->actingAs($admin)->put(route('pengaturan-presensi.update', $jadwal->id), $payload)->assertRedirect();
    expect(DB::table('jadwal_presensi')->where('hari', 1)->value('jam_mulai_pulang'))->toBe('13:30');
});
test('jadwal aktif dan urutan jam divalidasi', function () {
    $admin = userPresensi('admin');
    $id = DB::table('jadwal_presensi')->where('hari', 1)->value('id');
    $this->actingAs($admin)->put(route('pengaturan-presensi.update', $id), ['hari' => 1, 'is_aktif' => 1])->assertSessionHasErrors(['jam_mulai_masuk', 'batas_terlambat', 'jam_mulai_pulang']);
    $this->actingAs($admin)->put(route('pengaturan-presensi.update', $id), ['hari' => 1, 'is_aktif' => 1, 'jam_mulai_masuk' => '08:00', 'batas_terlambat' => '07:00', 'jam_mulai_pulang' => '06:00'])->assertSessionHasErrors(['batas_terlambat', 'jam_mulai_pulang']);
});
test('menonaktifkan jadwal tetap mempertahankan pengaturan jam', function () {
    $admin = userPresensi('admin');
    $id = DB::table('jadwal_presensi')->where('hari', 1)->value('id');
    $payload = ['hari' => 1, 'is_aktif' => 0, 'jam_mulai_masuk' => '06:00', 'batas_terlambat' => '07:15', 'jam_mulai_pulang' => '13:00', 'jam_akhir_pulang' => '14:00'];

    $this->actingAs($admin)->put(route('pengaturan-presensi.update', $id), $payload)->assertRedirect();

    $jadwal = DB::table('jadwal_presensi')->where('hari', 1)->first();
    expect((bool) $jadwal->is_aktif)->toBeFalse()
        ->and($jadwal->jam_mulai_masuk)->toBe('06:00')
        ->and($jadwal->batas_terlambat)->toBe('07:15')
        ->and($jadwal->jam_mulai_pulang)->toBe('13:00')
        ->and($jadwal->jam_akhir_pulang)->toBe('14:00');
});
test('seeder jadwal tidak menimpa jadwal yang telah diubah admin', function () {
    DB::table('jadwal_presensi')->where('hari', 1)->update(['jam_mulai_pulang' => '14:30:00', 'is_aktif' => false]);
    $this->seed(JadwalPresensiSeeder::class);
    expect(DB::table('jadwal_presensi')->where('hari', 1)->value('jam_mulai_pulang'))->toBe('14:30:00')->and((bool) DB::table('jadwal_presensi')->where('hari', 1)->value('is_aktif'))->toBeFalse()->and(DB::table('jadwal_presensi')->count())->toBe(7);
    DB::table('jadwal_presensi')->where('hari', 1)->update(['jam_mulai_pulang' => '13:00:00', 'is_aktif' => true]);
});
test('recent log kiosk tidak menampilkan label aktivitas yang ambigu', function () {
    $this->get(route('presensi.kiosk'))
        ->assertOk()
        ->assertDontSee("scan.action === 'masuk' ? 'MASUK' : 'PULANG'", false);
});
test('scan masuk hadir lalu terlalu awal tanpa mutasi', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-27 07:00', 'Asia/Jakarta'));
    $d = dataPresensi();
    $this->postJson(route('presensi.scan'), ['kode_uid' => 'ab12cd34'])->assertOk()->assertJsonPath('action', 'masuk')->assertJsonPath('data.status', 'hadir');
    $this->travel(1)->hour();
    $this->postJson(route('presensi.scan'), ['kode_uid' => 'AB12CD34'])->assertStatus(422)->assertJsonPath('action', 'terlalu_awal');
    expect(Presensi::first()->waktu_pulang)->toBeNull();
});
test('scan terlambat lalu pulang dan scan berikutnya lengkap', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-31 07:16', 'Asia/Jakarta'));
    $d = dataPresensi();
    $this->postJson(route('presensi.scan'), ['kode_uid' => 'AB12CD34'])->assertJsonPath('data.status', 'terlambat');
    Carbon::setTestNow(Carbon::parse('2026-07-31 12:00', 'Asia/Jakarta'));
    $this->postJson(route('presensi.scan'), ['kode_uid' => 'AB12CD34'])->assertOk()->assertJsonPath('action', 'pulang');
    $waktu = Presensi::first()->waktu_pulang;
    $this->travel(5)->minutes();
    $this->postJson(route('presensi.scan'), ['kode_uid' => 'AB12CD34'])->assertOk()->assertJsonPath('action', 'lengkap');
    expect(Presensi::count())->toBe(1)->and(Presensi::first()->waktu_pulang)->toBe($waktu);
});
test('hari tidak aktif menolak scan', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-02 07:00', 'Asia/Jakarta'));
    dataPresensi();
    $this->postJson(route('presensi.scan'), ['kode_uid' => 'AB12CD34'])->assertStatus(422)->assertJsonPath('type', 'error');
});
test('presensi manual menolak jadwal nonaktif', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-02 07:00', 'Asia/Jakarta'));
    $admin = userPresensi('admin');
    $d = dataPresensi();
    $payload = ['rombel_id' => $d['rombel'], 'tanggal' => '2026-08-02', 'presensi' => [$d['siswa'] => ['status' => 'hadir', 'waktu_masuk' => '07:00']]];

    $this->actingAs($admin)->post(route('presensi.store'), $payload)->assertSessionHasErrors('tanggal');
    expect(Presensi::count())->toBe(0);
});

test('presensi manual menolak tanggal masa depan berdasarkan WIB', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-27 23:30', 'Asia/Jakarta'));
    $admin = userPresensi('admin');
    $d = dataPresensi();
    $payload = ['rombel_id' => $d['rombel'], 'tanggal' => '2026-07-28', 'presensi' => [$d['siswa'] => ['status' => 'hadir', 'waktu_masuk' => '07:00']]];

    $this->actingAs($admin)->post(route('presensi.store'), $payload)->assertSessionHasErrors('tanggal');
    expect(Presensi::count())->toBe(0);
});

test('presensi manual hari ini menolak waktu masuk masa depan pada boundary WIB', function () {
    config(['app.timezone' => 'UTC']);
    Carbon::setTestNow(Carbon::parse('2026-07-27 17:30', 'UTC'));
    $admin = userPresensi('admin');
    $d = dataPresensi();
    $payload = ['rombel_id' => $d['rombel'], 'tanggal' => '2026-07-28', 'presensi' => [$d['siswa'] => ['status' => 'hadir', 'waktu_masuk' => '00:31']]];

    $this->actingAs($admin)->post(route('presensi.store'), $payload)->assertSessionHasErrors(['presensi.'.$d['siswa'].'.waktu_masuk']);
    expect(Presensi::count())->toBe(0);

    $payload['presensi'][$d['siswa']]['waktu_masuk'] = '00:30';
    $this->actingAs($admin)->post(route('presensi.store'), $payload)->assertRedirect()->assertSessionDoesntHaveErrors();
    expect(Presensi::first()->waktu_masuk)->toBe('00:30:00');
});

test('presensi manual hari ini menolak waktu pulang masa depan pada boundary WIB', function () {
    config(['app.timezone' => 'UTC']);
    Carbon::setTestNow(Carbon::parse('2026-07-28 06:00', 'UTC'));
    $admin = userPresensi('admin');
    $d = dataPresensi();
    $payload = ['rombel_id' => $d['rombel'], 'tanggal' => '2026-07-28', 'presensi' => [$d['siswa'] => ['status' => 'hadir', 'waktu_masuk' => '07:00', 'waktu_pulang' => '13:01']]];

    $this->actingAs($admin)->post(route('presensi.store'), $payload)->assertSessionHasErrors(['presensi.'.$d['siswa'].'.waktu_pulang']);
    expect(Presensi::count())->toBe(0);

    $payload['presensi'][$d['siswa']]['waktu_pulang'] = '13:00';
    $this->actingAs($admin)->post(route('presensi.store'), $payload)->assertRedirect()->assertSessionDoesntHaveErrors();
    expect(Presensi::first()->waktu_pulang)->toBe('13:00:00');
});

test('pulang cepat wajib alasan dan menyimpan pencatat', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-27 10:00', 'Asia/Jakarta'));
    $admin = userPresensi('admin');
    $d = dataPresensi();
    $base = ['rombel_id' => $d['rombel'], 'tanggal' => '2026-07-27', 'presensi' => [$d['siswa'] => ['status' => 'hadir', 'waktu_masuk' => '07:00', 'waktu_pulang' => '10:00', 'status_pulang' => 'tepat_waktu']]];
    $this->actingAs($admin)->post(route('presensi.store'), $base)->assertSessionHasErrors(['presensi.'.$d['siswa'].'.alasan_pulang_cepat']);
    $base['presensi'][$d['siswa']]['alasan_pulang_cepat'] = 'Sakit';
    $this->actingAs($admin)->post(route('presensi.store'), $base)->assertRedirect();
    $p = Presensi::first();
    expect($p->status_pulang)->toBe('pulang_cepat')->and($p->metode_pulang)->toBe('manual')->and($p->pulang_dicatat_oleh)->toBe($admin->id);
});
test('tepat pada batas jadwal pulang manual adalah tepat waktu tanpa alasan', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-27 13:00', 'Asia/Jakarta'));
    $admin = userPresensi('admin');
    $d = dataPresensi();
    $payload = ['rombel_id' => $d['rombel'], 'tanggal' => '2026-07-27', 'presensi' => [$d['siswa'] => ['status' => 'hadir', 'waktu_masuk' => '07:00', 'waktu_pulang' => '13:00', 'status_pulang' => 'pulang_cepat']]];
    $this->actingAs($admin)->post(route('presensi.store'), $payload)->assertRedirect()->assertSessionDoesntHaveErrors();
    $p = Presensi::first();
    expect($p->status_pulang)->toBe('tepat_waktu')->and($p->alasan_pulang_cepat)->toBeNull();
});
test('status pulang tanpa waktu pulang ditolak', function () {
    $admin = userPresensi('admin');
    $d = dataPresensi();
    $payload = ['rombel_id' => $d['rombel'], 'tanggal' => '2026-07-27', 'presensi' => [$d['siswa'] => ['status' => 'hadir', 'waktu_masuk' => '07:00', 'status_pulang' => 'tepat_waktu']]];
    $this->actingAs($admin)->post(route('presensi.store'), $payload)->assertSessionHasErrors(['presensi.'.$d['siswa'].'.status_pulang']);
    expect(Presensi::count())->toBe(0);
});
test('provenance rfid hanya berubah manual ketika waktunya dikoreksi', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-27 07:00', 'Asia/Jakarta'));
    $admin = userPresensi('admin');
    $d = dataPresensi();
    $this->postJson(route('presensi.scan'), ['kode_uid' => 'AB12CD34'])->assertOk();
    Carbon::setTestNow(Carbon::parse('2026-07-27 13:00', 'Asia/Jakarta'));
    $this->postJson(route('presensi.scan'), ['kode_uid' => 'AB12CD34'])->assertOk();

    $payload = ['rombel_id' => $d['rombel'], 'tanggal' => '2026-07-27', 'presensi' => [$d['siswa'] => ['status' => 'hadir', 'waktu_masuk' => '07:00', 'waktu_pulang' => '13:00', 'status_pulang' => 'tepat_waktu']]];
    $this->actingAs($admin)->post(route('presensi.store'), $payload)->assertRedirect();
    $p = Presensi::first();
    expect($p->metode_masuk)->toBe('rfid')->and($p->metode_pulang)->toBe('rfid');

    Carbon::setTestNow(Carbon::parse('2026-07-27 13:05', 'Asia/Jakarta'));
    $payload['presensi'][$d['siswa']]['waktu_masuk'] = '07:05';
    $payload['presensi'][$d['siswa']]['waktu_pulang'] = '13:05';
    $this->actingAs($admin)->post(route('presensi.store'), $payload)->assertRedirect();
    $p->refresh();
    expect($p->metode)->toBe('rfid')->and($p->metode_masuk)->toBe('manual')->and($p->metode_pulang)->toBe('manual')->and($p->waktu_scan)->toBe('07:00:00')->and($p->dicatat_oleh)->toBe($admin->id)->and($p->pulang_dicatat_oleh)->toBe($admin->id);
});
