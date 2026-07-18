<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KartuRfidController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\Master\GuruController;
use App\Http\Controllers\Master\RombelController;
use App\Http\Controllers\Master\SiswaController;
use App\Http\Controllers\Master\TahunAjaranController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\RombelSiswaController;
use Illuminate\Support\Facades\Route;

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
            Route::resource('rombel', RombelController::class);
        });

        Route::resource('kartu-rfid', KartuRfidController::class)->except(['show']);
        Route::resource('rombel-siswa', RombelSiswaController::class)->except(['show', 'edit', 'update']);
    });

    Route::middleware('role:admin,tu,wali_kelas')->group(function () {
        Route::resource('presensi', PresensiController::class)->only(['index', 'store']);
        Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('laporan/csv', [LaporanController::class, 'csv'])->name('laporan.csv');
        Route::get('laporan/print', [LaporanController::class, 'print'])->name('laporan.print');
    });

    Route::get('/calendar', fn () => view('pages.calender', ['title' => 'Calendar']))->name('calendar');
    Route::get('/profile', fn () => view('pages.profile', ['title' => 'Profile']))->name('profile');
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
