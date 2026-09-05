<?php

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    Carbon::setTestNow('2026-07-18 08:00:00');
    $this->actingAs(User::factory()->create());
});

afterEach(function () {
    Carbon::setTestNow();
});

test('dashboard hanya menghitung siswa aktif dengan keanggotaan rombel yang valid hari ini', function () {
    $tahunAktif = DB::table('tahun_ajaran')->insertGetId([
        'nama' => '2026/2027', 'semester' => 1, 'is_aktif' => true,
    ]);
    $tahunLama = DB::table('tahun_ajaran')->insertGetId([
        'nama' => '2025/2026', 'semester' => 2, 'is_aktif' => false,
    ]);
    $rombelAktif = DB::table('rombel')->insertGetId([
        'nama' => '7 A', 'tingkat' => '7', 'tahun_ajaran_id' => $tahunAktif,
    ]);
    $rombelLama = DB::table('rombel')->insertGetId([
        'nama' => '6 A', 'tingkat' => '6', 'tahun_ajaran_id' => $tahunLama,
    ]);

    $buatSiswa = fn (string $nis, string $status = 'aktif') => DB::table('siswa')->insertGetId([
        'nis' => $nis, 'nama_lengkap' => "Siswa {$nis}", 'jenis_kelamin' => 'L', 'status' => $status,
    ]);

    $hadir = $buatSiswa('001');
    $terlambat = $buatSiswa('002');
    $belumMasuk = $buatSiswa('003');
    $sudahKeluar = $buatSiswa('004');
    $tidakAktif = $buatSiswa('005', 'lulus');
    $tahunLalu = $buatSiswa('006');
    $rombelTidakCocok = $buatSiswa('007');

    DB::table('rombel_siswa')->insert([
        ['rombel_id' => $rombelAktif, 'siswa_id' => $hadir, 'tanggal_masuk' => '2026-07-01', 'tanggal_keluar' => null],
        ['rombel_id' => $rombelAktif, 'siswa_id' => $terlambat, 'tanggal_masuk' => '2026-07-18', 'tanggal_keluar' => '2026-07-18'],
        ['rombel_id' => $rombelAktif, 'siswa_id' => $belumMasuk, 'tanggal_masuk' => '2026-07-19', 'tanggal_keluar' => null],
        ['rombel_id' => $rombelAktif, 'siswa_id' => $sudahKeluar, 'tanggal_masuk' => '2026-07-01', 'tanggal_keluar' => '2026-07-17'],
        ['rombel_id' => $rombelAktif, 'siswa_id' => $tidakAktif, 'tanggal_masuk' => '2026-07-01', 'tanggal_keluar' => null],
        ['rombel_id' => $rombelLama, 'siswa_id' => $tahunLalu, 'tanggal_masuk' => '2025-07-01', 'tanggal_keluar' => null],
        ['rombel_id' => $rombelAktif, 'siswa_id' => $rombelTidakCocok, 'tanggal_masuk' => '2026-07-01', 'tanggal_keluar' => null],
    ]);

    foreach ([
        [$hadir, $rombelAktif, 'hadir'],
        [$terlambat, $rombelAktif, 'terlambat'],
        [$belumMasuk, $rombelAktif, 'izin'],
        [$sudahKeluar, $rombelAktif, 'sakit'],
        [$tidakAktif, $rombelAktif, 'alpa'],
        [$tahunLalu, $rombelLama, 'hadir'],
    ] as [$siswaId, $rombelId, $status]) {
        DB::table('presensi')->insert([
            'siswa_id' => $siswaId,
            'rombel_id' => $rombelId,
            'tanggal' => '2026-07-18',
            'status' => $status,
            'metode' => 'manual',
        ]);
    }

    DB::table('presensi')->insert([
        'siswa_id' => $rombelTidakCocok,
        'rombel_id' => $rombelLama,
        'tanggal' => '2026-07-18',
        'waktu_masuk' => '07:00:00',
        'waktu_pulang' => '10:00:00',
        'status' => 'hadir',
        'status_pulang' => 'pulang_cepat',
        'metode' => 'manual',
    ]);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertViewHas('totalSiswa', 3)
        ->assertViewHas('totalRombel', 1)
        ->assertViewHas('hadir', 1)
        ->assertViewHas('terlambat', 1)
        ->assertViewHas('izin', 0)
        ->assertViewHas('sakit', 0)
        ->assertViewHas('alpa', 0)
        ->assertViewHas('persentaseHadir', 67.0)
        ->assertViewHas('sudahMasuk', 0)
        ->assertViewHas('sudahPulang', 0)
        ->assertViewHas('pulangCepat', 0);
});

test('dashboard aman ketika tidak ada tahun ajaran aktif', function () {
    $this->get(route('dashboard'))
        ->assertOk()
        ->assertViewHas('totalSiswa', 0)
        ->assertViewHas('totalRombel', 0)
        ->assertViewHas('hadir', 0)
        ->assertViewHas('terlambat', 0)
        ->assertViewHas('izin', 0)
        ->assertViewHas('sakit', 0)
        ->assertViewHas('alpa', 0)
        ->assertViewHas('persentaseHadir', 0);
});

test('dashboard menentukan hari ini secara eksplisit dalam WIB', function () {
    config(['app.timezone' => 'UTC']);
    Carbon::setTestNow(Carbon::parse('2026-07-17 17:30:00', 'UTC'));

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertViewHas('today', '2026-07-18');
});
