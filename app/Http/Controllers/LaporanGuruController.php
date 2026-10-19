<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\PresensiGuru;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanGuruController extends Controller
{
    public function index(Request $request)
    {
        return view('pages.laporan-guru.index', $this->data($request) + ['title' => 'Laporan Guru']);
    }

    public function print(Request $request)
    {
        return view('pages.laporan-guru.print', $this->data($request) + ['title' => 'Cetak Laporan Guru']);
    }

    public function csv(Request $request): StreamedResponse
    {
        $data = $this->data($request);

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $data['tab'] === 'harian'
                ? ['ID Guru', 'Nomor Identitas', 'Nama', 'Status Masuk', 'Waktu Masuk', 'Waktu Pulang', 'Status Pulang']
                : ['ID Guru', 'Nomor Identitas', 'Nama', 'Hadir', 'Terlambat', 'Belum Pulang'], ';');
            foreach ($data['rows'] as $row) {
                $identity = [$row->id, $row->nomor_indentitas, $row->nama_lengkap];
                $values = $data['tab'] === 'harian'
                    ? [$row->status, $row->waktu_masuk, $row->waktu_pulang, $row->status_pulang]
                    : [$row->hadir, $row->terlambat, $row->belum_pulang];
                fputcsv($handle, array_map(fn ($value) => is_string($value) && preg_match('/^[\s]*[=+@-]/u', $value) ? "'".$value : $value, [...$identity, ...$values]), ';');
            }
            fclose($handle);
        }, 'laporan-guru-'.$data['tab'].'.csv', ['Content-Type' => 'text/csv']);
    }

    private function data(Request $request): array
    {
        $filters = $request->validate([
            'tab' => ['nullable', 'in:harian,bulanan'],
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
            'bulan' => ['nullable', 'date_format:Y-m'],
        ]);
        $tab = $filters['tab'] ?? 'harian';
        $today = Carbon::now('Asia/Jakarta')->toDateString();
        $tanggal = $filters['tanggal'] ?? $today;
        $bulan = $filters['bulan'] ?? Carbon::now('Asia/Jakarta')->format('Y-m');
        $gurus = Guru::where('aktif', true)->orderBy('nama_lengkap')->orderBy('id')->get();

        if ($tab === 'harian') {
            $records = $tanggal <= $today
                ? PresensiGuru::whereDate('tanggal', $tanggal)->get()->keyBy('guru_id')
                : collect();
            $rows = $gurus->map(function (Guru $guru) use ($records) {
                $record = $records->get($guru->id);

                return (object) [
                    'id' => $guru->id,
                    'nomor_indentitas' => $guru->nomor_indentitas,
                    'nama_lengkap' => $guru->nama_lengkap,
                    'status' => $record?->status ? ucfirst($record->status) : 'Belum tercatat',
                    'waktu_masuk' => $record?->waktu_masuk,
                    'waktu_pulang' => $record?->waktu_pulang,
                    'status_pulang' => $record?->status_pulang ? ucwords(str_replace('_', ' ', $record->status_pulang)) : null,
                ];
            });
        } else {
            $start = Carbon::createFromFormat('!Y-m', $bulan, 'Asia/Jakarta')->startOfMonth()->toDateString();
            $end = min(Carbon::parse($start, 'Asia/Jakarta')->endOfMonth()->toDateString(), $today);
            $records = $start <= $end
                ? PresensiGuru::whereBetween('tanggal', [$start, $end])->get()->groupBy('guru_id')
                : collect();
            $rows = $gurus->map(function (Guru $guru) use ($records) {
                $attendance = $records->get($guru->id, collect());

                return (object) [
                    'id' => $guru->id,
                    'nomor_indentitas' => $guru->nomor_indentitas,
                    'nama_lengkap' => $guru->nama_lengkap,
                    'hadir' => $attendance->where('status', 'hadir')->count(),
                    'terlambat' => $attendance->where('status', 'terlambat')->count(),
                    'belum_pulang' => $attendance->whereNotNull('waktu_masuk')->whereNull('waktu_pulang')->count(),
                ];
            });
        }

        return compact('tab', 'tanggal', 'bulan', 'rows');
    }
}
