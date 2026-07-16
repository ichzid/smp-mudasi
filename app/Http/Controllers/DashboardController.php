<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Guru;
use App\Models\Rombel;
use App\Models\Presensi;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $totalSiswa = Siswa::count();
        $totalGuru = Guru::count();
        $totalRombel = Rombel::count();
        
        $today = Carbon::today()->format('Y-m-d');
        
        $hadir = Presensi::where('tanggal', $today)->where('status', 'hadir')->count();
        $terlambat = Presensi::where('tanggal', $today)->where('status', 'terlambat')->count();
        $izin = Presensi::where('tanggal', $today)->where('status', 'izin')->count();
        $sakit = Presensi::where('tanggal', $today)->where('status', 'sakit')->count();
        $alpa = Presensi::where('tanggal', $today)->where('status', 'alpa')->count();
        
        $totalHadir = $hadir + $terlambat;
        // Asumsi bahwa semua siswa harus diabsen hari ini
        // Pada prakteknya mungkin hanya yang terdaftar di rombel pada tahun ajaran aktif
        $persentaseHadir = $totalSiswa > 0 ? round(($totalHadir / $totalSiswa) * 100) : 0;
        
        return view('pages.dashboard.index', compact(
            'totalSiswa', 'totalGuru', 'totalRombel', 'today',
            'hadir', 'terlambat', 'izin', 'sakit', 'alpa', 'totalHadir', 'persentaseHadir'
        ), ['title' => 'Dashboard Utama']);
    }
}
