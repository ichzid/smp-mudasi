<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\Rombel;
use App\Models\TahunAjaran;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanController extends Controller
{
    private const STATUSES = ['hadir', 'terlambat', 'izin', 'sakit', 'alpa'];

    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $tab = $filters['tab'] ?? 'harian';
        $tahunAjaran = $this->selectedTahunAjaran($filters['tahun_ajaran_id'] ?? null);
        $rombels = $tahunAjaran ? $this->accessibleRombels($request, $tahunAjaran->id)->get() : collect();
        $rombel = $this->selectedRombel($rombels, $filters['rombel_id'] ?? null);
        $tanggal = $filters['tanggal'] ?? now()->toDateString();
        $bulan = $filters['bulan'] ?? now()->format('Y-m');

        $rows = $tab === 'bulanan'
            ? $this->monthlyRows($rombel, $bulan)
            : $this->dailyRows($rombel, $tanggal);

        return view('pages.laporan.index', [
            'title' => 'Laporan Presensi',
            'tab' => $tab,
            'tahunAjarans' => TahunAjaran::orderByDesc('nama')->orderByDesc('semester')->get(),
            'tahunAjaran' => $tahunAjaran,
            'rombels' => $rombels,
            'rombel' => $rombel,
            'tanggal' => $tanggal,
            'bulan' => $bulan,
            'rows' => $rows,
            'statuses' => self::STATUSES,
        ]);
    }

    public function csv(Request $request): StreamedResponse
    {
        $data = $this->reportData($request);
        $filename = 'laporan-'.$data['tab'].'-'.($data['tab'] === 'harian' ? $data['tanggal'] : $data['bulan']).'.csv';

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $data['tab'] === 'harian'
                ? ['Rombel', 'Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpa', 'Total']
                : ['NIS', 'Nama Siswa', 'Rombel', 'Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpa', 'Total'], ';');

            foreach ($data['rows'] as $row) {
                $values = $data['tab'] === 'harian'
                    ? [$row->nama, $row->hadir, $row->terlambat, $row->izin, $row->sakit, $row->alpa, $row->total]
                    : [$row->nis, $row->nama_lengkap, $row->rombel_nama, $row->hadir, $row->terlambat, $row->izin, $row->sakit, $row->alpa, $row->total];
                fputcsv($handle, $values, ';');
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function print(Request $request)
    {
        $data = $this->reportData($request);

        return view('pages.laporan.print', $data + ['title' => 'Cetak Laporan Presensi', 'statuses' => self::STATUSES]);
    }

    private function reportData(Request $request): array
    {
        $filters = $this->filters($request);
        $tab = $filters['tab'] ?? 'harian';
        $tahunAjaran = $this->selectedTahunAjaran($filters['tahun_ajaran_id'] ?? null);
        abort_unless($tahunAjaran, 422, 'Tahun ajaran harus dipilih.');
        $rombels = $this->accessibleRombels($request, $tahunAjaran->id)->get();
        $rombel = $this->selectedRombel($rombels, $filters['rombel_id'] ?? null);
        $tanggal = $filters['tanggal'] ?? now()->toDateString();
        $bulan = $filters['bulan'] ?? now()->format('Y-m');

        return compact('tab', 'tahunAjaran', 'rombel', 'tanggal', 'bulan') + [
            'rows' => $tab === 'bulanan' ? $this->monthlyRows($rombel, $bulan) : $this->dailyRows($rombel, $tanggal),
        ];
    }

    private function filters(Request $request): array
    {
        return $request->validate([
            'tab' => ['nullable', 'in:harian,bulanan'],
            'tahun_ajaran_id' => ['nullable', 'integer', 'exists:tahun_ajaran,id'],
            'rombel_id' => ['nullable', 'integer', 'exists:rombel,id'],
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
            'bulan' => ['nullable', 'date_format:Y-m'],
        ]);
    }

    private function selectedTahunAjaran(int|string|null $id): ?TahunAjaran
    {
        return $id ? TahunAjaran::find($id) : TahunAjaran::where('is_aktif', true)->first();
    }

    private function accessibleRombels(Request $request, int $tahunAjaranId): Builder
    {
        return Rombel::query()
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->when($request->user()->role === 'wali_kelas', fn (Builder $query) =>
                $query->whereHas('waliGuru', fn (Builder $guru) => $guru->where('user_id', $request->user()->id)))
            ->orderBy('tingkat')->orderBy('nama');
    }

    private function selectedRombel($rombels, int|string|null $id): ?Rombel
    {
        if (!$id) {
            return null;
        }

        $rombel = $rombels->firstWhere('id', $id);
        abort_unless($rombel, 403, 'Rombel tidak dapat diakses pada tahun ajaran yang dipilih.');

        return $rombel;
    }

    private function dailyRows(?Rombel $rombel, string $tanggal)
    {
        $query = Rombel::query()
            ->select('rombel.id', 'rombel.nama', 'rombel.tingkat')
            ->selectRaw("SUM(CASE WHEN presensi.status = 'hadir' THEN 1 ELSE 0 END) AS hadir")
            ->selectRaw("SUM(CASE WHEN presensi.status = 'terlambat' THEN 1 ELSE 0 END) AS terlambat")
            ->selectRaw("SUM(CASE WHEN presensi.status = 'izin' THEN 1 ELSE 0 END) AS izin")
            ->selectRaw("SUM(CASE WHEN presensi.status = 'sakit' THEN 1 ELSE 0 END) AS sakit")
            ->selectRaw("SUM(CASE WHEN presensi.status = 'alpa' THEN 1 ELSE 0 END) AS alpa")
            ->selectRaw('COUNT(presensi.id) AS total')
            ->leftJoin('presensi', function ($join) use ($tanggal) {
                $join->on('presensi.rombel_id', '=', 'rombel.id')->where('presensi.tanggal', '=', $tanggal);
            })
            ->where('rombel.id', $rombel?->id ?? 0)
            ->groupBy('rombel.id', 'rombel.nama', 'rombel.tingkat');

        return $query->get();
    }

    private function monthlyRows(?Rombel $rombel, string $bulan)
    {
        if (!$rombel) {
            return collect();
        }

        $start = Carbon::createFromFormat('Y-m', $bulan)->startOfMonth()->toDateString();
        $end = Carbon::createFromFormat('Y-m', $bulan)->endOfMonth()->toDateString();

        return Presensi::query()
            ->join('siswa', 'siswa.id', '=', 'presensi.siswa_id')
            ->join('rombel', 'rombel.id', '=', 'presensi.rombel_id')
            ->select('siswa.id', 'siswa.nis', 'siswa.nama_lengkap', 'rombel.nama as rombel_nama')
            ->selectRaw("SUM(CASE WHEN presensi.status = 'hadir' THEN 1 ELSE 0 END) AS hadir")
            ->selectRaw("SUM(CASE WHEN presensi.status = 'terlambat' THEN 1 ELSE 0 END) AS terlambat")
            ->selectRaw("SUM(CASE WHEN presensi.status = 'izin' THEN 1 ELSE 0 END) AS izin")
            ->selectRaw("SUM(CASE WHEN presensi.status = 'sakit' THEN 1 ELSE 0 END) AS sakit")
            ->selectRaw("SUM(CASE WHEN presensi.status = 'alpa' THEN 1 ELSE 0 END) AS alpa")
            ->selectRaw('COUNT(presensi.id) AS total')
            ->where('presensi.rombel_id', $rombel->id)
            ->where('rombel.tahun_ajaran_id', $rombel->tahun_ajaran_id)
            ->whereBetween('presensi.tanggal', [$start, $end])
            ->groupBy('siswa.id', 'siswa.nis', 'siswa.nama_lengkap', 'rombel.nama')
            ->orderBy('siswa.nama_lengkap')
            ->get();
    }
}
