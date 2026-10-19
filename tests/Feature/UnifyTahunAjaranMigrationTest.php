<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('menyatukan tahun ajaran semester tanpa mengubah identitas data historis', function () {
    expect(DB::getDefaultConnection())->toBe('sqlite')
        ->and(DB::connection()->getDatabaseName())->toBe(':memory:');

    $target = '2026_10_18_000000_unify_tahun_ajaran_per_year.php';
    $migrations = glob(database_path('migrations/*.php'));
    $awal = array_values(array_filter($migrations, fn ($path) => basename($path) !== $target));
    Artisan::call('migrate', ['--path' => $awal, '--realpath' => true, '--force' => true]);

    $ganjil = DB::table('tahun_ajaran')->insertGetId(['nama' => '2026/2027', 'semester' => 1, 'is_aktif' => false]);
    $genap = DB::table('tahun_ajaran')->insertGetId(['nama' => '2026/2027', 'semester' => 2, 'is_aktif' => true]);
    $guru = DB::table('guru')->insertGetId(['nama_lengkap' => 'Guru Wali', 'jenis_kelamin' => 'P']);
    $siswa = DB::table('siswa')->insertGetId(['nis' => '2026001', 'nama_lengkap' => 'Siswa', 'jenis_kelamin' => 'P']);
    $user = DB::table('users')->insertGetId(['name' => 'Kasir', 'username' => 'kasir', 'email' => 'kasir@example.test', 'password' => 'hash']);

    $rombelGanjil = DB::table('rombel')->insertGetId(['nama' => '7 A', 'tingkat' => '7', 'tahun_ajaran_id' => $ganjil, 'wali_guru_id' => $guru]);
    $rombelGenap = DB::table('rombel')->insertGetId(['nama' => '7 A', 'tingkat' => '7', 'tahun_ajaran_id' => $genap, 'wali_guru_id' => $guru]);
    $anggotaGanjil = DB::table('rombel_siswa')->insertGetId(['rombel_id' => $rombelGanjil, 'siswa_id' => $siswa, 'tanggal_masuk' => '2026-07-01']);
    $anggotaGenap = DB::table('rombel_siswa')->insertGetId(['rombel_id' => $rombelGenap, 'siswa_id' => $siswa, 'tanggal_masuk' => '2027-01-01']);
    $presensiGanjil = DB::table('presensi')->insertGetId(['rombel_id' => $rombelGanjil, 'siswa_id' => $siswa, 'tanggal' => '2026-07-20', 'status' => 'hadir', 'metode' => 'manual']);
    $presensiGenap = DB::table('presensi')->insertGetId(['rombel_id' => $rombelGenap, 'siswa_id' => $siswa, 'tanggal' => '2027-01-20', 'status' => 'hadir', 'metode' => 'manual']);
    $tarifGanjil = DB::table('tarif_pembayaran')->insertGetId(['tahun_ajaran_id' => $ganjil, 'jenis' => 'spp', 'nominal' => 200000]);
    $tarifGenap = DB::table('tarif_pembayaran')->insertGetId(['tahun_ajaran_id' => $genap, 'jenis' => 'spp', 'nominal' => 220000]);
    $tagihanGanjil = DB::table('tagihan_pembayaran')->insertGetId(['siswa_id' => $siswa, 'tarif_id' => $tarifGanjil, 'periode_key' => '2026-07', 'nominal' => 200000]);
    $tagihanGenap = DB::table('tagihan_pembayaran')->insertGetId(['siswa_id' => $siswa, 'tarif_id' => $tarifGenap, 'periode_key' => '2027-01', 'nominal' => 220000]);
    $transaksiGanjil = DB::table('transaksi_pembayaran')->insertGetId(['tagihan_id' => $tagihanGanjil, 'nominal' => 200000, 'user_id' => $user, 'dibayar_pada' => '2026-07-20 10:00:00', 'kode_kuitansi' => 'GANJIL']);
    $transaksiGenap = DB::table('transaksi_pembayaran')->insertGetId(['tagihan_id' => $tagihanGenap, 'nominal' => 220000, 'user_id' => $user, 'dibayar_pada' => '2027-01-20 10:00:00', 'kode_kuitansi' => 'GENAP']);

    Artisan::call('migrate', ['--path' => database_path('migrations/'.$target), '--realpath' => true, '--force' => true]);

    expect(DB::table('tahun_ajaran')->where('nama', '2026/2027')->pluck('id')->all())->toBe([$genap])
        ->and((int) DB::table('tahun_ajaran')->where('id', $genap)->value('semester'))->toBe(2)
        ->and((int) DB::table('tahun_ajaran')->where('id', $genap)->value('is_aktif'))->toBe(1);

    foreach ([[$rombelGanjil, 1], [$rombelGenap, 2]] as [$id, $semester]) {
        $rombel = DB::table('rombel')->find($id);
        expect($rombel)->not->toBeNull()
            ->and((int) $rombel->tahun_ajaran_id)->toBe($genap)
            ->and((int) $rombel->semester)->toBe($semester)
            ->and((int) $rombel->wali_guru_id)->toBe($guru);
    }
    foreach ([[$tarifGanjil, 1, 200000], [$tarifGenap, 2, 220000]] as [$id, $semester, $nominal]) {
        $tarif = DB::table('tarif_pembayaran')->find($id);
        expect($tarif)->not->toBeNull()
            ->and((int) $tarif->tahun_ajaran_id)->toBe($genap)
            ->and((int) $tarif->semester)->toBe($semester)
            ->and((int) $tarif->nominal)->toBe($nominal);
    }
    foreach ([[$anggotaGanjil, $rombelGanjil], [$anggotaGenap, $rombelGenap]] as [$id, $rombel]) {
        expect((int) DB::table('rombel_siswa')->find($id)->rombel_id)->toBe($rombel);
    }
    foreach ([[$presensiGanjil, $rombelGanjil], [$presensiGenap, $rombelGenap]] as [$id, $rombel]) {
        expect((int) DB::table('presensi')->find($id)->rombel_id)->toBe($rombel);
    }
    foreach ([[$tagihanGanjil, $tarifGanjil], [$tagihanGenap, $tarifGenap]] as [$id, $tarif]) {
        expect((int) DB::table('tagihan_pembayaran')->find($id)->tarif_id)->toBe($tarif);
    }
    foreach ([[$transaksiGanjil, $tagihanGanjil], [$transaksiGenap, $tagihanGenap]] as [$id, $tagihan]) {
        expect((int) DB::table('transaksi_pembayaran')->find($id)->tagihan_id)->toBe($tagihan);
    }
    expect(Schema::hasColumn('rombel', 'semester'))->toBeTrue()
        ->and(DB::table('rombel')->count())->toBe(2)
        ->and(DB::table('tarif_pembayaran')->count())->toBe(2)
        ->and(DB::table('rombel_siswa')->count())->toBe(2)
        ->and(DB::table('presensi')->count())->toBe(2)
        ->and(DB::table('tagihan_pembayaran')->count())->toBe(2)
        ->and(DB::table('transaksi_pembayaran')->count())->toBe(2);
});
