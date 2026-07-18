<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\Rombel;
use App\Models\TahunAjaran;
use App\Models\RombelSiswa;
use App\Models\KartuRfid;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PresensiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tanggal = $request->input('tanggal', date('Y-m-d'));
        $tahun_ajaran_id = $request->input('tahun_ajaran_id');
        $rombel_id = $request->input('rombel_id');
        
        $tahun_ajarans = TahunAjaran::latest()->get();
        $rombels = collect();
        $presensi_data = collect();

        if ($tahun_ajaran_id) {
            $rombels = Rombel::where('tahun_ajaran_id', $tahun_ajaran_id)->get();
        }

        if ($rombel_id) {
            // Get all students in the rombel
            $rombel_siswas = RombelSiswa::with('siswa')
                                        ->where('rombel_id', $rombel_id)
                                        // Ensure student was in the rombel on the selected date
                                        ->where('tanggal_masuk', '<=', $tanggal)
                                        ->where(function($query) use ($tanggal) {
                                            $query->whereNull('tanggal_keluar')
                                                  ->orWhere('tanggal_keluar', '>=', $tanggal);
                                        })
                                        ->get();

            // Get existing presensi records
            $existing_presensi = Presensi::where('rombel_id', $rombel_id)
                                         ->where('tanggal', $tanggal)
                                         ->get()
                                         ->keyBy('siswa_id');

            // Map the data
            $presensi_data = $rombel_siswas->map(function ($rs) use ($existing_presensi) {
                $siswa_id = $rs->siswa_id;
                $presensi = $existing_presensi->get($siswa_id);
                
                return (object)[
                    'siswa' => $rs->siswa,
                    'presensi' => $presensi,
                    'status' => $presensi ? $presensi->status : null,
                    'waktu_scan' => $presensi ? $presensi->waktu_scan : null,
                    'metode' => $presensi ? $presensi->metode : null,
                    'id' => $presensi ? $presensi->id : null,
                ];
            })->sortBy(function ($item) {
                return $item->siswa->nama_lengkap;
            });
        }

        return view('pages.presensi.index', compact(
            'tahun_ajarans', 'rombels', 'presensi_data', 
            'tahun_ajaran_id', 'rombel_id', 'tanggal'
        ), ['title' => 'Manajemen Presensi Harian']);
    }

    /**
     * Show the Kiosk interface for RFID scanning
     */
    public function kiosk()
    {
        return view('pages.presensi.kiosk', ['title' => 'Kiosk Presensi RFID']);
    }

    /**
     * Handle the AJAX request from RFID scanner
     */
    public function scan(Request $request)
    {
        $request->validate([
            'kode_uid' => 'required|string'
        ]);

        $kode_uid = $request->kode_uid;
        
        // 1. Cari kartu RFID aktif
        $kartu = KartuRfid::where('kode_uid', $kode_uid)
                          ->where('status', 'aktif')
                          ->first();
                          
        if (!$kartu) {
            return response()->json([
                'success' => false,
                'message' => 'Kartu tidak dikenali atau tidak aktif.'
            ], 404);
        }

        // 2. Ambil data siswa
        $siswa = Siswa::find($kartu->siswa_id);
        if (!$siswa || $siswa->status !== 'aktif') {
            return response()->json([
                'success' => false,
                'message' => 'Siswa tidak ditemukan atau statusnya sudah tidak aktif.'
            ], 422);
        }

        // 3. Cari rombel aktif siswa saat ini
        $hari_ini = date('Y-m-d');
        $rombel_siswa = RombelSiswa::with(['rombel.tahunAjaran'])
                                   ->where('siswa_id', $siswa->id)
                                   ->where('tanggal_masuk', '<=', $hari_ini)
                                   ->where(function($q) use ($hari_ini) {
                                       $q->whereNull('tanggal_keluar')
                                         ->orWhere('tanggal_keluar', '>=', $hari_ini);
                                   })
                                   ->whereHas('rombel.tahunAjaran', function($q) {
                                       $q->where('is_aktif', true);
                                   })
                                   ->latest('tanggal_masuk')
                                   ->first();

        if (!$rombel_siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa belum terdaftar di Rombel (Kelas) pada Tahun Ajaran yang aktif.'
            ], 422);
        }

        // 4. Tentukan status (hadir/terlambat) - Disini sementara hardcode 07:15
        $waktu_sekarang = Carbon::now()->format('H:i:s');
        $batas_terlambat = '07:15:00'; // Nanti bisa dipindah ke setting database
        
        $status_kehadiran = ($waktu_sekarang > $batas_terlambat) ? 'terlambat' : 'hadir';

        // 5. Insert or Update (mencegah double tap pada hari yang sama)
        DB::beginTransaction();
        try {
            $presensi = Presensi::updateOrCreate(
                [
                    'siswa_id' => $siswa->id,
                    'tanggal' => $hari_ini,
                ],
                [
                    'rombel_id' => $rombel_siswa->rombel_id,
                    // Jangan ubah waktu scan pertama & statusnya jika sudah ada (mencegah tap 2x ngubah jam)
                    'waktu_scan' => DB::raw("COALESCE(waktu_scan, '{$waktu_sekarang}')"),
                    'status' => DB::raw("COALESCE(status, '{$status_kehadiran}')"),
                    'metode' => 'rfid',
                    'dicatat_oleh' => null
                ]
            );
            
            DB::commit();
            
            // 6. Return response for Kiosk UI
            return response()->json([
                'success' => true,
                'data' => [
                    'nama' => $siswa->nama_lengkap,
                    'nis' => $siswa->nis,
                    'foto_url' => $siswa->foto_url ? asset('storage/' . $siswa->foto_url) : null,
                    'kelas' => $rombel_siswa->rombel->tingkat . ' ' . $rombel_siswa->rombel->nama,
                    'waktu' => $presensi->waktu_scan ?? $waktu_sekarang,
                    'status' => $presensi->status ?? $status_kehadiran,
                    'pesan' => ($presensi->wasRecentlyCreated) 
                               ? 'Berhasil melakukan presensi.' 
                               : 'Anda sudah melakukan presensi hari ini.'
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat menyimpan presensi.'
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Not used, using modal/batch update in index
    }

    /**
     * Store a newly created resource in storage (Batch Insert/Update).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'rombel_id' => 'required|exists:rombels,id',
            'tanggal' => 'required|date',
            'presensi' => 'required|array',
            'presensi.*' => 'in:hadir,terlambat,izin,sakit,alpa',
        ]);

        $rombel_id = $validated['rombel_id'];
        $tanggal = $validated['tanggal'];
        $waktu_sekarang = Carbon::now()->format('H:i:s');
        
        DB::beginTransaction();
        try {
            foreach ($validated['presensi'] as $siswa_id => $status) {
                Presensi::updateOrCreate(
                    [
                        'siswa_id' => $siswa_id,
                        'tanggal' => $tanggal,
                    ],
                    [
                        'rombel_id' => $rombel_id,
                        'status' => $status,
                        'metode' => 'manual',
                        'waktu_scan' => ($status == 'hadir' || $status == 'terlambat') ? DB::raw("COALESCE(waktu_scan, '{$waktu_sekarang}')") : null,
                        'dicatat_oleh' => auth()->id() ?? 1, // fallback jika auth tidak aktif
                    ]
                );
            }
            DB::commit();
            
            // Redirect back with same filter parameters
            $rombel = Rombel::find($rombel_id);
            return redirect()->route('presensi.index', [
                'tahun_ajaran_id' => $rombel->tahun_ajaran_id,
                'rombel_id' => $rombel_id,
                'tanggal' => $tanggal
            ])->with('success', 'Data presensi berhasil disimpan.');
            
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan saat menyimpan data presensi. ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
