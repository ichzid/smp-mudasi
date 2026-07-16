<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\Rombel;
use App\Models\TahunAjaran;
use App\Models\RombelSiswa;
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
