<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\KartuRfid;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KartuRfidController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = KartuRfid::with(['siswa', 'guru'])->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_uid', 'like', "%{$search}%")
                    ->orWhereHas('siswa', function ($siswa) use ($search) {
                        $siswa->where('nama_lengkap', 'like', "%{$search}%")
                            ->orWhere('nisn', 'like', "%{$search}%");
                    })
                    ->orWhereHas('guru', function ($guru) use ($search) {
                        $guru->where('nama_lengkap', 'like', "%{$search}%")
                            ->orWhere('nomor_indentitas', 'like', "%{$search}%");
                    });
            });
        }

        $kartu_rfids = $query->get();

        return view('pages.kartu-rfid.index', compact('kartu_rfids', 'search'), ['title' => 'Data Kartu RFID']);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $siswas = Siswa::where('status', 'aktif')->doesntHave('kartuRfid')->orderBy('nama_lengkap')->get();
        $gurus = Guru::where('aktif', true)->doesntHave('kartuRfid')->orderBy('nama_lengkap')->get();

        return view('pages.kartu-rfid.create', compact('siswas', 'gurus'), ['title' => 'Tambah Kartu RFID']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->merge(['kode_uid' => strtoupper($request->input('kode_uid', ''))]);

        $validated = $request->validate([
            'siswa_id' => ['nullable', 'required_without:guru_id', Rule::prohibitedIf(fn () => $request->filled('guru_id')), Rule::exists('siswa', 'id')->where('status', 'aktif'), Rule::unique('kartu_rfid', 'siswa_id')],
            'guru_id' => ['nullable', 'required_without:siswa_id', Rule::prohibitedIf(fn () => $request->filled('siswa_id')), Rule::exists('guru', 'id')->where('aktif', true), Rule::unique('kartu_rfid', 'guru_id')],
            'kode_uid' => ['required', 'string', 'min:4', 'max:32', 'regex:/^[A-Z0-9:-]+$/', 'unique:kartu_rfid,kode_uid'],
            'status' => 'required|in:aktif,nonaktif',
        ]);

        $validated['diterbitkan_pada'] = now();

        KartuRfid::create($validated);

        return redirect()->route('kartu-rfid.index')->with('success', 'Kartu RFID berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $kartu = KartuRfid::with(['siswa', 'guru'])->findOrFail($id);

        return view('pages.kartu-rfid.show', compact('kartu'), ['title' => 'Detail Kartu RFID']);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $kartu = KartuRfid::with(['siswa', 'guru'])->findOrFail($id);

        return view('pages.kartu-rfid.edit', compact('kartu'), ['title' => 'Edit Kartu RFID']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $kartu = KartuRfid::findOrFail($id);
        $request->merge(['kode_uid' => strtoupper($request->input('kode_uid', ''))]);

        $validated = $request->validate([
            'kode_uid' => ['required', 'string', 'min:4', 'max:32', 'regex:/^[A-Z0-9:-]+$/', Rule::unique('kartu_rfid', 'kode_uid')->ignore($kartu->id)],
            'status' => 'required|in:aktif,nonaktif',
        ]);

        $kartu->update($validated);

        return redirect()->route('kartu-rfid.index')->with('success', 'Kartu RFID berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $kartu = KartuRfid::findOrFail($id);
        $kartu->delete();

        return redirect()->route('kartu-rfid.index')->with('success', 'Kartu RFID berhasil dihapus.');
    }
}
