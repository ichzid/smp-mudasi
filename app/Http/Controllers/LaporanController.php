<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\Rombel;
use App\Models\RombelSiswa;
use App\Models\TahunAjaran;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->data($request, false);

        return view('pages.laporan.index', $data + ['title' => 'Laporan Presensi']);
    }

    public function print(Request $request)
    {
        return view('pages.laporan.print', $this->data($request, true) + ['title' => 'Cetak Laporan Presensi']);
    }

    public function csv(Request $request): StreamedResponse
    {
        $d = $this->data($request, true);

        return response()->streamDownload(function () use ($d) {
            $h = fopen('php://output', 'w');
            fwrite($h, "\xEF\xBB\xBF");
            $headers = $d['tab'] === 'harian' ? ['NIS', 'Nama', 'Status Masuk', 'Waktu Masuk', 'Waktu Pulang', 'Status Pulang', 'Durasi', 'Alasan Pulang Cepat'] : ['NIS', 'Nama', 'Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpa', 'Sudah Pulang', 'Belum Pulang', 'Pulang Cepat'];
            fputcsv($h, $headers, ';');
            foreach ($d['rows'] as $r) {
                fputcsv($h, $d['tab'] === 'harian' ? [$r->siswa->nis, $r->siswa->nama_lengkap, $r->status, $r->waktu_masuk, $r->waktu_pulang, $r->status_pulang, $r->durasi_sekolah, $r->alasan_pulang_cepat] : [$r->nis, $r->nama_lengkap, $r->hadir, $r->terlambat, $r->izin, $r->sakit, $r->alpa, $r->sudah_pulang, $r->belum_pulang, $r->pulang_cepat], ';');
            }fclose($h);
        }, 'laporan-'.$d['tab'].'.csv', ['Content-Type' => 'text/csv']);
    }

    private function data(Request $request, bool $required): array
    {
        $f = $request->validate(['tab' => ['nullable', 'in:harian,bulanan'], 'tahun_ajaran_id' => ['nullable', 'exists:tahun_ajaran,id'], 'rombel_id' => ['nullable', 'exists:rombel,id'], 'tanggal' => ['nullable', 'date_format:Y-m-d'], 'bulan' => ['nullable', 'date_format:Y-m'], 'status_pulang' => ['nullable', 'in:sudah_pulang,belum_pulang,pulang_cepat,lengkap']]);
        $tab = $f['tab'] ?? 'harian';
        $tahunAjaran = isset($f['tahun_ajaran_id']) ? TahunAjaran::find($f['tahun_ajaran_id']) : TahunAjaran::where('is_aktif', true)->first();
        $rombels = $tahunAjaran ? $this->accessible($request, $tahunAjaran->id)->get() : collect();
        $rombel = isset($f['rombel_id']) ? $rombels->firstWhere('id', (int) $f['rombel_id']) : null;
        if (isset($f['rombel_id'])) {
            abort_unless($rombel, 403);
        }if ($required) {
            abort_unless($tahunAjaran && $rombel, 422);
        }$tanggal = $f['tanggal'] ?? now('Asia/Jakarta')->toDateString();
        $bulan = $f['bulan'] ?? now('Asia/Jakarta')->format('Y-m');
        $status_pulang = $f['status_pulang'] ?? null;
        $rows = $rombel ? ($tab === 'harian' ? $this->daily($rombel, $tanggal, $status_pulang) : $this->monthly($rombel, $bulan)) : collect();

        return compact('tab', 'tahunAjaran', 'rombels', 'rombel', 'tanggal', 'bulan', 'status_pulang', 'rows') + ['tahunAjarans' => TahunAjaran::orderByDesc('nama')->get()];
    }

    private function daily(Rombel $r, string $tanggal, ?string $filter)
    {
        $presensi = Presensi::where('rombel_id', $r->id)->whereDate('tanggal', $tanggal)->get()->keyBy('siswa_id');

        return RombelSiswa::with('siswa')->where('rombel_id', $r->id)
            ->whereDate('tanggal_masuk', '<=', $tanggal)
            ->where(fn ($q) => $q->whereNull('tanggal_keluar')->orWhereDate('tanggal_keluar', '>=', $tanggal))
            ->get()->map(function (RombelSiswa $anggota) use ($presensi, $r, $tanggal) {
                $item = $presensi->get($anggota->siswa_id) ?? new Presensi([
                    'siswa_id' => $anggota->siswa_id,
                    'rombel_id' => $r->id,
                    'tanggal' => $tanggal,
                ]);

                return $item->setRelation('siswa', $anggota->siswa);
            })
            ->filter(fn (Presensi $item) => match ($filter) {
                'sudah_pulang' => $item->waktu_pulang !== null,
                'belum_pulang' => $item->waktu_masuk !== null && $item->waktu_pulang === null,
                'pulang_cepat' => $item->status_pulang === 'pulang_cepat',
                'lengkap' => $item->waktu_masuk !== null && $item->waktu_pulang !== null,
                default => true,
            })->sortBy('siswa.nama_lengkap')->values();
    }

    private function monthly(Rombel $r, string $bulan)
    {
        $start = Carbon::createFromFormat('Y-m', $bulan)->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $presensi = Presensi::where('rombel_id', $r->id)->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])->get()->groupBy('siswa_id');

        return RombelSiswa::with('siswa')->where('rombel_id', $r->id)
            ->whereDate('tanggal_masuk', '<=', $end->toDateString())
            ->where(fn ($q) => $q->whereNull('tanggal_keluar')->orWhereDate('tanggal_keluar', '>=', $start->toDateString()))
            ->get()->unique('siswa_id')->map(function (RombelSiswa $anggota) use ($presensi) {
                $records = $presensi->get($anggota->siswa_id, collect());
                $row = (object) ['id' => $anggota->siswa->id, 'nis' => $anggota->siswa->nis, 'nama_lengkap' => $anggota->siswa->nama_lengkap];
                foreach (['hadir', 'terlambat', 'izin', 'sakit', 'alpa'] as $status) {
                    $row->{$status} = $records->where('status', $status)->count();
                }
                $row->sudah_pulang = $records->whereNotNull('waktu_pulang')->count();
                $row->belum_pulang = $records->whereNotNull('waktu_masuk')->whereNull('waktu_pulang')->count();
                $row->pulang_cepat = $records->where('status_pulang', 'pulang_cepat')->count();

                return $row;
            })->sortBy('nama_lengkap')->values();
    }

    private function accessible(Request $request, int $ta): Builder
    {
        return Rombel::where('tahun_ajaran_id', $ta)->when($request->user()->role === 'wali_kelas', fn ($q) => $q->whereHas('waliGuru', fn ($g) => $g->where('user_id', $request->user()->id)))->orderBy('tingkat')->orderBy('nama');
    }
}
