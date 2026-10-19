<?php

use App\Models\Guru;
use App\Models\KartuRfid;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
});

function buatSiswaRfid(string $nis): Siswa
{
    return Siswa::create([
        'nis' => $nis,
        'nama_lengkap' => "Siswa {$nis}",
        'jenis_kelamin' => 'L',
        'status' => 'aktif',
    ]);
}

it('menormalisasi UID menjadi uppercase saat simpan dan update', function () {
    $siswa = buatSiswaRfid('2001');

    $this->post(route('kartu-rfid.store'), [
        'siswa_id' => $siswa->id,
        'kode_uid' => 'ab12cd',
        'status' => 'aktif',
    ])->assertSessionHasNoErrors();

    $kartu = KartuRfid::sole();
    expect($kartu->kode_uid)->toBe('AB12CD');

    $this->put(route('kartu-rfid.update', $kartu), [
        'kode_uid' => 'ef34gh',
        'status' => 'nonaktif',
    ])->assertSessionHasNoErrors();

    expect($kartu->fresh()->kode_uid)->toBe('EF34GH');
});

it('menolak UID duplikat tanpa membedakan input huruf kecil', function () {
    KartuRfid::create([
        'siswa_id' => buatSiswaRfid('2002')->id,
        'kode_uid' => 'ABC123',
        'status' => 'aktif',
        'diterbitkan_pada' => now(),
    ]);

    $this->post(route('kartu-rfid.store'), [
        'siswa_id' => buatSiswaRfid('2003')->id,
        'kode_uid' => 'abc123',
        'status' => 'aktif',
    ])->assertSessionHasErrors('kode_uid');

    expect(KartuRfid::count())->toBe(1);
});

function buatGuruRfid(string $nama, bool $aktif = true): Guru
{
    return Guru::create([
        'nama_lengkap' => $nama,
        'jenis_kelamin' => 'L',
        'aktif' => $aktif,
    ]);
}

it('mendaftarkan kartu guru dan menampilkan pemilik pada daftar dan edit', function () {
    $guru = buatGuruRfid('Guru RFID');

    $this->post(route('kartu-rfid.store'), [
        'guru_id' => $guru->id,
        'kode_uid' => 'teach123',
        'status' => 'aktif',
    ])->assertSessionHasNoErrors();

    $kartu = KartuRfid::sole();
    expect($kartu->guru_id)->toBe($guru->id);
    expect($kartu->siswa_id)->toBeNull();
    expect($kartu->kode_uid)->toBe('TEACH123');
    expect($guru->kartuRfid->id)->toBe($kartu->id);

    $this->get(route('kartu-rfid.index'))->assertOk()->assertSee('Guru RFID')->assertSee('Guru · Identitas:');
    $this->get(route('kartu-rfid.edit', $kartu))->assertOk()->assertSee('Guru RFID');
    $this->get(route('kartu-rfid.create'))->assertOk()->assertDontSee('Guru RFID');

    $this->put(route('kartu-rfid.update', $kartu), [
        'guru_id' => buatGuruRfid('Guru Lain')->id,
        'siswa_id' => buatSiswaRfid('2011')->id,
        'kode_uid' => 'new1234',
        'status' => 'nonaktif',
    ])->assertSessionHasNoErrors();
    expect($kartu->fresh()->guru_id)->toBe($guru->id);
    expect($kartu->fresh()->siswa_id)->toBeNull();
});

it('menolak pemilik kosong, ganda, tidak aktif, dan yang sudah memiliki kartu', function () {
    $guru = buatGuruRfid('Guru Aktif');
    $siswa = buatSiswaRfid('2012');
    $this->post(route('kartu-rfid.store'), [
        'kode_uid' => 'NONE1234', 'status' => 'aktif',
    ])->assertSessionHasErrors(['siswa_id', 'guru_id']);
    $this->post(route('kartu-rfid.store'), [
        'guru_id' => $guru->id, 'siswa_id' => $siswa->id, 'kode_uid' => 'BOTH1234', 'status' => 'aktif',
    ])->assertSessionHasErrors(['siswa_id', 'guru_id']);
    $this->post(route('kartu-rfid.store'), [
        'guru_id' => buatGuruRfid('Guru Nonaktif', false)->id, 'kode_uid' => 'OFF1234', 'status' => 'aktif',
    ])->assertSessionHasErrors('guru_id');

    KartuRfid::create(['guru_id' => $guru->id, 'kode_uid' => 'OWN1234', 'status' => 'aktif', 'diterbitkan_pada' => now()]);
    $this->post(route('kartu-rfid.store'), [
        'guru_id' => $guru->id, 'kode_uid' => 'OTHER1234', 'status' => 'aktif',
    ])->assertSessionHasErrors('guru_id');
    $this->post(route('kartu-rfid.store'), [
        'siswa_id' => $siswa->id, 'kode_uid' => 'own1234', 'status' => 'aktif',
    ])->assertSessionHasErrors('kode_uid');
    expect(KartuRfid::count())->toBe(1);
});

it('menerapkan tepat satu pemilik di database', function () {
    $guru = buatGuruRfid('Guru Database');
    $siswa = buatSiswaRfid('2013');
    foreach ([[null, null], [$siswa->id, $guru->id]] as [$siswaId, $guruId]) {
        expect(fn () => DB::table('kartu_rfid')->insert([
            'siswa_id' => $siswaId, 'guru_id' => $guruId,
            'kode_uid' => 'INVALID'.($siswaId ?? '0'), 'status' => 'aktif', 'diterbitkan_pada' => now(),
        ]))->toThrow(\Illuminate\Database\QueryException::class);
    }
});

it('menolak UID tidak valid dan UID duplikat ketika update', function () {
    $kartu = KartuRfid::create(['guru_id' => buatGuruRfid('Guru Update')->id, 'kode_uid' => 'UPDATE1', 'status' => 'aktif', 'diterbitkan_pada' => now()]);
    KartuRfid::create(['siswa_id' => buatSiswaRfid('2014')->id, 'kode_uid' => 'DUPL1234', 'status' => 'aktif', 'diterbitkan_pada' => now()]);
    foreach (['abc', 'bad uid', 'dupl1234'] as $uid) {
        $this->put(route('kartu-rfid.update', $kartu), ['kode_uid' => $uid, 'status' => 'aktif'])
            ->assertSessionHasErrors('kode_uid');
    }
    expect($kartu->fresh()->kode_uid)->toBe('UPDATE1');
});

it('tidak mendaftarkan route show kartu RFID', function () {
    expect(Route::getRoutes()->getByName('kartu-rfid.show'))->toBeNull();
    $this->get('/kartu-rfid/999')->assertMethodNotAllowed();
});
