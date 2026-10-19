<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Presensi;
use App\Models\RombelSiswa;
use App\Models\TahunAjaran;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today('Asia/Jakarta')->toDateString();
        $tahunAjaranAktif = TahunAjaran::where('is_aktif', true)->first();

        $anggotaAktif = RombelSiswa::query()
            ->whereHas('siswa', fn ($query) => $query->where('status', 'aktif'))
            ->whereDate('tanggal_masuk', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('tanggal_keluar')
                    ->orWhereDate('tanggal_keluar', '>=', $today);
            });

        if ($tahunAjaranAktif) {
            $tahun = (int) substr($today, 0, 4);
            $semester = (int) substr($today, 5, 2) >= 7 ? 1 : 2;
            $nama = $semester === 1 ? $tahun.'/'.($tahun + 1) : ($tahun - 1).'/'.$tahun;
            $semester = $tahunAjaranAktif->nama === $nama ? $semester : null;
            $anggotaAktif->whereHas('rombel', fn ($query) => $query->where('tahun_ajaran_id', $tahunAjaranAktif->id)
                ->where(fn ($query) => $query->whereNull('semester')->when($semester, fn ($query) => $query->orWhere('semester', $semester))))
                ->whereNotExists(function ($query) use ($today, $tahunAjaranAktif) {
                    $query->selectRaw('1')->from('rombel as snapshot_rombel')
                        ->join('rombel_siswa as annual_anggota', 'annual_anggota.siswa_id', '=', 'rombel_siswa.siswa_id')
                        ->join('rombel as annual_rombel', 'annual_rombel.id', '=', 'annual_anggota.rombel_id')
                        ->whereColumn('snapshot_rombel.id', 'rombel_siswa.rombel_id')
                        ->whereNotNull('snapshot_rombel.semester')
                        ->where('annual_rombel.tahun_ajaran_id', $tahunAjaranAktif->id)
                        ->whereNull('annual_rombel.semester')
                        ->whereDate('annual_anggota.tanggal_masuk', '<=', $today)
                        ->where(fn ($query) => $query->whereNull('annual_anggota.tanggal_keluar')->orWhereDate('annual_anggota.tanggal_keluar', '>=', $today));
                });
        } else {
            $anggotaAktif->whereRaw('1 = 0');
        }

        $siswaIds = (clone $anggotaAktif)->select('siswa_id')->distinct();
        $rombelIds = (clone $anggotaAktif)->select('rombel_id')->distinct();
        $totalSiswa = (clone $siswaIds)->count('siswa_id');
        $totalGuru = Guru::count();
        $totalRombel = (clone $rombelIds)->count('rombel_id');

        $presensiHariIni = Presensi::query()->whereDate('tanggal', $today)
            ->whereExists((clone $anggotaAktif)->selectRaw('1')
                ->whereColumn('rombel_siswa.siswa_id', 'presensi.siswa_id')
                ->whereColumn('rombel_siswa.rombel_id', 'presensi.rombel_id')->toBase());
        $statistik = (clone $presensiHariIni)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $hadir = (int) $statistik->get('hadir', 0);
        $terlambat = (int) $statistik->get('terlambat', 0);
        $izin = (int) $statistik->get('izin', 0);
        $sakit = (int) $statistik->get('sakit', 0);
        $alpa = (int) $statistik->get('alpa', 0);
        $totalHadir = $hadir + $terlambat;
        $totalTercatat = $totalHadir + $izin + $sakit + $alpa;
        $belumTercatat = max(0, $totalSiswa - $totalTercatat);
        $persentaseHadir = $totalSiswa > 0 ? round(($totalHadir / $totalSiswa) * 100) : 0;
        $sudahMasuk = (clone $presensiHariIni)->whereNotNull('waktu_masuk')->count();
        $sudahPulang = (clone $presensiHariIni)->whereNotNull('waktu_pulang')->count();
        $belumPulang = (clone $presensiHariIni)->whereNotNull('waktu_masuk')->whereNull('waktu_pulang')->count();
        $pulangCepat = (clone $presensiHariIni)->where('status_pulang', 'pulang_cepat')->count();
        $persentasePulang = $sudahMasuk > 0 ? round(($sudahPulang / $sudahMasuk) * 100) : 0;

        return view('pages.dashboard.index', compact(
            'totalSiswa', 'totalGuru', 'totalRombel', 'today',
            'hadir', 'terlambat', 'izin', 'sakit', 'alpa', 'totalHadir', 'totalTercatat', 'belumTercatat', 'persentaseHadir',
            'sudahMasuk', 'sudahPulang', 'belumPulang', 'pulangCepat', 'persentasePulang'
        ), ['title' => 'Dashboard Utama']);
    }
}
