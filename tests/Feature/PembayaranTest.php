<?php

use App\Models\KartuRfid;
use App\Models\Siswa;
use App\Models\TagihanPembayaran;
use App\Models\TarifPembayaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
});

function dataPembayaran(): array
{
    $tahun = DB::table('tahun_ajaran')->insertGetId(['nama' => '2026/2027', 'semester' => 1, 'is_aktif' => true]);
    $siswa = Siswa::create(['nis' => '2026001', 'nama_lengkap' => 'Ani', 'jenis_kelamin' => 'P', 'status' => 'aktif']);
    KartuRfid::create(['siswa_id' => $siswa->id, 'kode_uid' => 'ABCD1234', 'status' => 'aktif', 'diterbitkan_pada' => '2026-07-01']);
    $spp = TarifPembayaran::create(['tahun_ajaran_id' => $tahun, 'semester' => 1, 'jenis' => 'spp', 'nominal' => 200000]);
    $uts = TarifPembayaran::create(['tahun_ajaran_id' => $tahun, 'semester' => 1, 'jenis' => 'uts', 'nominal' => 300000]);

    return compact('tahun', 'siswa', 'spp', 'uts');
}

test('tarif unik per semester dan jenis, perubahan tidak mengubah snapshot tagihan', function () {
    $d = dataPembayaran();
    $this->post(route('pembayaran.tarif.store'), ['tahun_ajaran_id' => $d['tahun'], 'semester' => 1, 'jenis' => 'uas', 'nominal' => 250000])->assertRedirect();
    $this->post(route('pembayaran.tarif.store'), ['tahun_ajaran_id' => $d['tahun'], 'semester' => 1, 'jenis' => 'spp', 'nominal' => 999])->assertSessionHasErrors('jenis');
    $this->post(route('pembayaran.kasir.bayar'), ['siswa_id' => $d['siswa']->id, 'tarif_id' => $d['spp']->id, 'periode_key' => '2026-07', 'nominal' => 100000])->assertRedirect();
    $this->put(route('pembayaran.tarif.update', $d['spp']), ['nominal' => 250000])->assertRedirect();
    expect(TagihanPembayaran::first()->nominal)->toBe(200000);
});

test('semester ganjil dan genap pada tahun ajaran sama menjaga periode, kuitansi, dan status ujian historis', function () {
    $d = dataPembayaran();
    $this->post(route('pembayaran.tarif.store'), [
        'tahun_ajaran_id' => $d['tahun'], 'semester' => 2, 'jenis' => 'spp', 'nominal' => 220000,
    ])->assertRedirect(route('pembayaran.tarif.index'));
    $this->post(route('pembayaran.tarif.store'), [
        'tahun_ajaran_id' => $d['tahun'], 'semester' => 2, 'jenis' => 'uts', 'nominal' => 320000,
    ])->assertRedirect(route('pembayaran.tarif.index'));
    $genapSpp = TarifPembayaran::where('tahun_ajaran_id', $d['tahun'])->where('semester', 2)->where('jenis', 'spp')->firstOrFail();
    $genapUts = TarifPembayaran::where('tahun_ajaran_id', $d['tahun'])->where('semester', 2)->where('jenis', 'uts')->firstOrFail();
    $this->post(route('pembayaran.tarif.store'), [
        'tahun_ajaran_id' => $d['tahun'], 'semester' => 2, 'jenis' => 'spp', 'nominal' => 230000,
    ])->assertSessionHasErrors('jenis');
    $this->post(route('pembayaran.tarif.store'), [
        'tahun_ajaran_id' => $d['tahun'], 'jenis' => 'uas', 'nominal' => 250000,
    ])->assertSessionHasErrors('semester');

    $this->postJson(route('pembayaran.kasir.bayar'), [
        'siswa_id' => $d['siswa']->id, 'tarif_id' => $d['spp']->id, 'periode_key' => '2026-12', 'nominal' => 200000,
    ])->assertCreated();
    $ganjilReceipt = TagihanPembayaran::where('tarif_id', $d['spp']->id)->firstOrFail()->transaksi()->firstOrFail();
    DB::table('tahun_ajaran')->where('id', $d['tahun'])->update(['semester' => 2]);
    $this->get(route('pembayaran.tarif.index'))->assertOk()->assertSee('Semester tarif');
    $this->get(route('pembayaran.kasir.index'))->assertOk()
        ->assertSee('data-semester="1"', false)->assertSee('data-semester="2"', false)
        ->assertSee('SPP — 2026/2027 Ganjil')->assertSee('SPP — 2026/2027 Genap');
    $this->get(route('pembayaran.kasir.kuitansi', $ganjilReceipt))->assertOk()->assertSee('2026/2027 — Ganjil');
    $this->postJson(route('pembayaran.kasir.cari'), [
        'kode_uid' => 'ABCD1234', 'tarif_id' => $d['spp']->id, 'periode_key' => '2026-07',
    ])->assertOk();
    $this->postJson(route('pembayaran.kasir.cari'), [
        'kode_uid' => 'ABCD1234', 'tarif_id' => $d['spp']->id, 'periode_key' => '2027-01',
    ])->assertUnprocessable()->assertJsonValidationErrors('periode_key');
    $this->postJson(route('pembayaran.kasir.bayar'), [
        'siswa_id' => $d['siswa']->id, 'tarif_id' => $genapSpp->id, 'periode_key' => '2026-12', 'nominal' => 100000,
    ])->assertUnprocessable()->assertJsonValidationErrors('periode_key');
    $this->postJson(route('pembayaran.kasir.bayar'), [
        'siswa_id' => $d['siswa']->id, 'tarif_id' => $genapSpp->id, 'periode_key' => '2027-01', 'nominal' => 220000,
    ])->assertCreated();
    $genapReceipt = TagihanPembayaran::where('tarif_id', $genapSpp->id)->firstOrFail()->transaksi()->firstOrFail();
    $this->get(route('pembayaran.kasir.kuitansi', $genapReceipt))->assertOk()->assertSee('2026/2027 — Genap');

    $this->postJson(route('pembayaran.kasir.bayar'), [
        'siswa_id' => $d['siswa']->id, 'tarif_id' => $d['uts']->id, 'periode_key' => 'UTS', 'nominal' => 300000,
    ])->assertCreated();
    $this->get(route('pembayaran.ujian.index'))->assertOk()
        ->assertSee('UTS — 2026/2027 Ganjil')->assertSee('UTS — 2026/2027 Genap');
    $this->postJson(route('pembayaran.ujian.cek'), ['kode_uid' => 'ABCD1234', 'tarif_id' => $d['uts']->id])
        ->assertOk()->assertJsonPath('tarif.semester', 1)->assertJsonPath('tagihan.status', 'lunas');
    $this->postJson(route('pembayaran.ujian.cek'), ['kode_uid' => 'ABCD1234', 'tarif_id' => $genapUts->id])
        ->assertOk()->assertJsonPath('tarif.semester', 2)->assertJsonPath('tagihan.status', 'belum_bayar');
    expect(TagihanPembayaran::count())->toBe(3);
});

test('pencarian UID aktif, nama dan NIS menampilkan status tanpa membuat tagihan', function () {
    $d = dataPembayaran();
    $this->postJson(route('pembayaran.kasir.cari'), ['kode_uid' => 'abcd1234', 'tarif_id' => $d['spp']->id, 'periode_key' => '2026-07'])
        ->assertOk()->assertJsonPath('data.0.siswa.nis', '2026001')->assertJsonPath('data.0.tagihan.status', 'belum_bayar');
    $this->getJson(route('pembayaran.kasir.cari', ['q' => 'Ani']))->assertOk()->assertJsonPath('data.0.siswa.id', $d['siswa']->id);
    $this->getJson(route('pembayaran.kasir.cari', ['q' => '2026001']))->assertOk()->assertJsonCount(1, 'data');
    expect(TagihanPembayaran::count())->toBe(0);
    $d['siswa']->kartuRfid()->update(['status' => 'nonaktif']);
    $this->postJson(route('pembayaran.kasir.cari'), ['kode_uid' => 'ABCD1234'])->assertUnprocessable();
});

test('angsuran SPP menolak kelebihan bayar dan pembayaran ulang setelah lunas', function () {
    $d = dataPembayaran();
    $payload = ['siswa_id' => $d['siswa']->id, 'tarif_id' => $d['spp']->id, 'periode_key' => '2026-07'];
    $this->postJson(route('pembayaran.kasir.bayar'), $payload + ['nominal' => 100000])->assertCreated();
    $tagihan = TagihanPembayaran::first();
    $this->postJson(route('pembayaran.kasir.bayar'), ['tagihan_id' => $tagihan->id, 'nominal' => 100001])->assertUnprocessable();
    $this->postJson(route('pembayaran.kasir.bayar'), ['tagihan_id' => $tagihan->id, 'nominal' => 100000])->assertCreated();
    $this->postJson(route('pembayaran.kasir.bayar'), ['tagihan_id' => $tagihan->id, 'nominal' => 1])->assertUnprocessable();
    expect($tagihan->transaksi()->count())->toBe(2)->and($tagihan->transaksi()->sum('nominal'))->toBe(200000);
});

test('serialisasi penagihan melalui kunci siswa dan tagihan mencegah pembayaran ganda', function () {
    $d = dataPembayaran();
    $payload = ['siswa_id' => $d['siswa']->id, 'tarif_id' => $d['uts']->id, 'periode_key' => 'UTS', 'nominal' => 300000];
    $this->postJson(route('pembayaran.kasir.bayar'), $payload)->assertCreated();
    $this->postJson(route('pembayaran.kasir.bayar'), $payload)->assertUnprocessable();
    expect(TagihanPembayaran::count())->toBe(1)->and(DB::table('transaksi_pembayaran')->count())->toBe(1);
});

test('pemeriksaan ujian hanya membaca status, tanpa menulis presensi maupun tagihan', function () {
    $d = dataPembayaran();
    $request = ['kode_uid' => 'abcd1234', 'tarif_id' => $d['uts']->id];
    $this->postJson(route('pembayaran.ujian.cek'), $request)->assertOk()->assertJsonPath('tagihan.status', 'belum_bayar');
    $this->postJson(route('pembayaran.kasir.bayar'), ['siswa_id' => $d['siswa']->id, 'tarif_id' => $d['uts']->id, 'periode_key' => 'UTS', 'nominal' => 100000])->assertCreated();
    $this->postJson(route('pembayaran.ujian.cek'), $request)->assertJsonPath('tagihan.status', 'sebagian')->assertJsonPath('tagihan.sisa', 200000);
    $this->postJson(route('pembayaran.kasir.bayar'), ['siswa_id' => $d['siswa']->id, 'tarif_id' => $d['uts']->id, 'periode_key' => 'UTS', 'nominal' => 200000])->assertCreated();
    $this->postJson(route('pembayaran.ujian.cek'), $request)->assertJsonPath('tagihan.status', 'lunas');
    $this->postJson(route('pembayaran.ujian.cek'), ['kode_uid' => 'abcd1234', 'tarif_id' => $d['spp']->id])->assertUnprocessable();
    expect(DB::table('presensi')->count())->toBe(0)->and(TagihanPembayaran::count())->toBe(1);
});

test('siswa nonaktif dan bulan di luar semester ditolak', function () {
    $d = dataPembayaran();
    $payload = ['siswa_id' => $d['siswa']->id, 'tarif_id' => $d['spp']->id, 'periode_key' => '2027-01', 'nominal' => 100000];
    $this->postJson(route('pembayaran.kasir.bayar'), $payload)->assertUnprocessable();
    $d['siswa']->update(['status' => 'lulus']);
    $this->postJson(route('pembayaran.kasir.bayar'), array_merge($payload, ['periode_key' => '2026-07']))->assertUnprocessable();
    $this->postJson(route('pembayaran.ujian.cek'), ['kode_uid' => 'ABCD1234', 'tarif_id' => $d['uts']->id])->assertUnprocessable();
});

test('halaman keuangan dan kuitansi tampil melalui route produksi untuk admin dan TU', function () {
    $d = dataPembayaran();
    foreach (['admin', 'tu'] as $role) {
        $this->actingAs(User::factory()->create(['role' => $role]));
        $this->get(route('pembayaran.tarif.index'))->assertOk()->assertSee('Tarif Pembayaran');
        $this->get(route('pembayaran.kasir.index'))->assertOk()->assertSee('Konfirmasi Pembayaran');
        $this->get(route('pembayaran.ujian.index'))->assertOk()->assertSee('Pemeriksaan Pembayaran Ujian');
    }
    $this->postJson(route('pembayaran.kasir.bayar'), [
        'siswa_id' => $d['siswa']->id, 'tarif_id' => $d['spp']->id, 'periode_key' => '2026-07', 'nominal' => 100000,
    ])->assertCreated()->assertJsonPath('success', true);
    $transaksi = TagihanPembayaran::first()->transaksi()->first();
    $this->get(route('pembayaran.kasir.kuitansi', $transaksi))->assertOk()
        ->assertSee($transaksi->kode_kuitansi)->assertSee($d['siswa']->nama_lengkap)->assertSee('100.000');
    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->get(route('pembayaran.kasir.kuitansi', $transaksi))->assertOk();
});

test('TU dapat mengelola pembayaran tetapi tidak dapat mengubah tarif', function () {
    $d = dataPembayaran();
    $this->actingAs(User::factory()->create(['role' => 'tu']));
    $this->get(route('pembayaran.tarif.index'))->assertOk()->assertDontSee('Simpan Tarif')->assertDontSee('Perbarui');
    $this->post(route('pembayaran.tarif.store'), ['tahun_ajaran_id' => $d['tahun'], 'semester' => 1, 'jenis' => 'uas', 'nominal' => 100000])->assertForbidden();
    $this->put(route('pembayaran.tarif.update', $d['spp']), ['nominal' => 100000])->assertForbidden();
    $this->postJson(route('pembayaran.kasir.bayar'), [
        'siswa_id' => $d['siswa']->id, 'tarif_id' => $d['spp']->id, 'periode_key' => '2026-07', 'nominal' => 100000,
    ])->assertCreated();
    expect($d['spp']->fresh()->nominal)->toBe(200000);
});

test('wali kelas tidak dapat mengakses halaman dan endpoint keuangan', function () {
    $d = dataPembayaran();
    $this->postJson(route('pembayaran.kasir.bayar'), [
        'siswa_id' => $d['siswa']->id, 'tarif_id' => $d['spp']->id, 'periode_key' => '2026-07', 'nominal' => 100000,
    ])->assertCreated();
    $transaksi = TagihanPembayaran::first()->transaksi()->first();
    $this->actingAs(User::factory()->create(['role' => 'wali_kelas']));
    foreach (['pembayaran.tarif.index', 'pembayaran.kasir.index', 'pembayaran.ujian.index'] as $name) {
        $this->get(route($name))->assertForbidden();
    }
    $this->get(route('pembayaran.kasir.kuitansi', $transaksi))->assertForbidden();
    $this->postJson(route('pembayaran.kasir.cari'), ['kode_uid' => 'ABCD1234'])->assertForbidden();
    $this->postJson(route('pembayaran.ujian.cek'), ['kode_uid' => 'ABCD1234', 'tarif_id' => $d['uts']->id])->assertForbidden();
    $this->postJson(route('pembayaran.kasir.bayar'), [
        'siswa_id' => $d['siswa']->id, 'tarif_id' => $d['spp']->id, 'periode_key' => '2026-07', 'nominal' => 100000,
    ])->assertForbidden();
    expect(TagihanPembayaran::count())->toBe(1)->and(DB::table('transaksi_pembayaran')->count())->toBe(1);
});

test('tahun ajaran tidak valid ditolak untuk tarif SPP dan tarif lama disembunyikan di kasir', function () {
    $d = dataPembayaran();
    $invalid = DB::table('tahun_ajaran')->insertGetId(['nama' => '2026/2028', 'semester' => 1, 'is_aktif' => false]);
    $this->post(route('pembayaran.tarif.store'), [
        'tahun_ajaran_id' => $invalid, 'semester' => 1, 'jenis' => 'spp', 'nominal' => 200000,
    ])->assertSessionHasErrors('tahun_ajaran_id');
    $this->post(route('pembayaran.tarif.store'), [
        'tahun_ajaran_id' => $invalid, 'semester' => 1, 'jenis' => 'uts', 'nominal' => 200000,
    ])->assertRedirect(route('pembayaran.tarif.index'));
    $legacy = TarifPembayaran::create(['tahun_ajaran_id' => $invalid, 'jenis' => 'spp', 'nominal' => 100000]);
    $this->get(route('pembayaran.kasir.index'))->assertOk()
        ->assertDontSee('value="'.$legacy->id.'"', false)
        ->assertSee('value="'.$d['spp']->id.'"', false);
    $this->postJson(route('pembayaran.kasir.bayar'), [
        'siswa_id' => $d['siswa']->id, 'tarif_id' => $legacy->id, 'periode_key' => '2026-07', 'nominal' => 100000,
    ])->assertUnprocessable();
});

test('validasi pembayaran menolak input tidak sah tanpa mencatat tagihan', function () {
    $d = dataPembayaran();
    $payload = ['siswa_id' => $d['siswa']->id, 'tarif_id' => $d['spp']->id, 'periode_key' => '2026-07'];
    $this->postJson(route('pembayaran.kasir.bayar'), $payload + ['nominal' => 0])->assertUnprocessable()->assertJsonValidationErrors('nominal');
    $this->postJson(route('pembayaran.kasir.bayar'), $payload + ['nominal' => 200001])->assertUnprocessable()->assertJsonValidationErrors('nominal');
    $this->postJson(route('pembayaran.kasir.bayar'), array_merge($payload, ['periode_key' => 'UTS', 'nominal' => 1]))
        ->assertUnprocessable()->assertJsonValidationErrors('periode_key');
    expect(TagihanPembayaran::count())->toBe(0);
});
