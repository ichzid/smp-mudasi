<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JadwalPresensiController;
use App\Http\Controllers\KartuRfidController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\Master\GuruController;
use App\Http\Controllers\Master\RombelController;
use App\Http\Controllers\Master\SiswaController;
use App\Http\Controllers\Master\TahunAjaranController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\RombelSiswaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

Route::get('presensi/kiosk', [PresensiController::class, 'kiosk'])->name('presensi.kiosk');
Route::post('presensi/scan', [PresensiController::class, 'scan'])
    ->middleware('throttle:30,1')
    ->name('presensi.scan');

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('role:admin,tu')->group(function () {
        Route::prefix('master')->name('master.')->group(function () {
            Route::resource('siswa', SiswaController::class);
            Route::resource('tahun-ajaran', TahunAjaranController::class);
            Route::resource('guru', GuruController::class);
            Route::resource('kelas', RombelController::class)
                ->parameters(['kelas' => 'rombel'])
                ->names('rombel');
        });

        Route::resource('kartu-rfid', KartuRfidController::class)->except(['show']);
        Route::resource('anggota-kelas', RombelSiswaController::class)
            ->parameters(['anggota-kelas' => 'rombel_siswa'])
            ->names('rombel-siswa')
            ->except(['show', 'edit', 'update']);
    });

    Route::middleware('role:admin,tu,wali_kelas')->group(function () {
        Route::get('pengaturan-presensi', [JadwalPresensiController::class, 'index'])->name('pengaturan-presensi.index');
        Route::put('pengaturan-presensi/{jadwalPresensi}', [JadwalPresensiController::class, 'update'])->name('pengaturan-presensi.update');
        Route::resource('presensi', PresensiController::class)->only(['index', 'store']);
        Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('laporan/csv', [LaporanController::class, 'csv'])->name('laporan.csv');
        Route::get('laporan/print', [LaporanController::class, 'print'])->name('laporan.print');
    });

    Route::get('/calendar', fn () => view('pages.calender', ['title' => 'Calendar']))->name('calendar');
    Route::get('/profile', fn () => view('pages.profile', ['title' => 'Profil Saya']))->name('profile');
    Route::put('/profile', function (Request $request) {
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->name = $validated['name'];
        $user->username = $validated['username'];
        $user->email = $validated['email'] ?? null;

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('profile')->with('success', 'Profil berhasil diperbarui.');
    })->name('profile.update');
    Route::get('/form-elements', fn () => view('pages.form.form-elements', ['title' => 'Form Elements']))->name('form-elements');
    Route::get('/basic-tables', fn () => view('pages.tables.basic-tables', ['title' => 'Basic Tables']))->name('basic-tables');
    Route::get('/blank', fn () => view('pages.blank', ['title' => 'Blank']))->name('blank');
    Route::get('/error-404', fn () => view('pages.errors.error-404', ['title' => 'Error 404']))->name('error-404');
    Route::get('/line-chart', fn () => view('pages.chart.line-chart', ['title' => 'Line Chart']))->name('line-chart');
    Route::get('/bar-chart', fn () => view('pages.chart.bar-chart', ['title' => 'Bar Chart']))->name('bar-chart');
    Route::get('/alerts', fn () => view('pages.ui-elements.alerts', ['title' => 'Alerts']))->name('alerts');
    Route::get('/avatars', fn () => view('pages.ui-elements.avatars', ['title' => 'Avatars']))->name('avatars');
    Route::get('/badge', fn () => view('pages.ui-elements.badges', ['title' => 'Badges']))->name('badges');
    Route::get('/buttons', fn () => view('pages.ui-elements.buttons', ['title' => 'Buttons']))->name('buttons');
    Route::get('/image', fn () => view('pages.ui-elements.images', ['title' => 'Images']))->name('images');
    Route::get('/videos', fn () => view('pages.ui-elements.videos', ['title' => 'Videos']))->name('videos');
});
