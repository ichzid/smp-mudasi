<?php

use App\Models\KartuRfid;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

it('tidak mendaftarkan route show kartu RFID', function () {
    expect(Route::getRoutes()->getByName('kartu-rfid.show'))->toBeNull();
    $this->get('/kartu-rfid/999')->assertMethodNotAllowed();
});
