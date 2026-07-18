<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Master\SiswaController;
use App\Http\Controllers\Master\TahunAjaranController;
use App\Http\Controllers\Master\GuruController;
use App\Http\Controllers\Master\RombelController;
use App\Http\Controllers\KartuRfidController;
use App\Http\Controllers\RombelSiswaController;
use App\Http\Controllers\PresensiController;

// dashboard pages
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// CRUD Data Master Routes
Route::prefix('master')->name('master.')->group(function () {
    Route::resource('siswa', SiswaController::class);
    Route::resource('tahun-ajaran', TahunAjaranController::class);
    Route::resource('guru', GuruController::class);
    Route::resource('rombel', RombelController::class);
});

// Other main features
Route::resource('kartu-rfid', KartuRfidController::class);
Route::resource('rombel-siswa', RombelSiswaController::class)->except(['show', 'edit', 'update']);

Route::get('presensi/kiosk', [PresensiController::class, 'kiosk'])->name('presensi.kiosk');
Route::post('presensi/scan', [PresensiController::class, 'scan'])->name('presensi.scan');
Route::resource('presensi', PresensiController::class)->except(['create', 'show', 'edit', 'update', 'destroy']);



// ini adalah template pages
// calender pages
Route::get('/calendar', function () {
    return view('pages.calender', ['title' => 'Calendar']);
})->name('calendar');

// profile pages
Route::get('/profile', function () {
    return view('pages.profile', ['title' => 'Profile']);
})->name('profile');

// logout routing (temporary simple redirect to home or login page)
Route::post('/logout', function () {
    // Auth::logout();
    return redirect('/');
})->name('logout');
Route::get('/logout', function () {
    // Auth::logout();
    return redirect('/');
});

// form pages
Route::get('/form-elements', function () {
    return view('pages.form.form-elements', ['title' => 'Form Elements']);
})->name('form-elements');

// tables pages
Route::get('/basic-tables', function () {
    return view('pages.tables.basic-tables', ['title' => 'Basic Tables']);
})->name('basic-tables');

// pages

Route::get('/blank', function () {
    return view('pages.blank', ['title' => 'Blank']);
})->name('blank');

// error pages
Route::get('/error-404', function () {
    return view('pages.errors.error-404', ['title' => 'Error 404']);
})->name('error-404');

// chart pages
Route::get('/line-chart', function () {
    return view('pages.chart.line-chart', ['title' => 'Line Chart']);
})->name('line-chart');

Route::get('/bar-chart', function () {
    return view('pages.chart.bar-chart', ['title' => 'Bar Chart']);
})->name('bar-chart');


// authentication pages
Route::get('/signin', function () {
    return view('pages.auth.signin', ['title' => 'Sign In']);
})->name('signin');

Route::get('/signup', function () {
    return view('pages.auth.signup', ['title' => 'Sign Up']);
})->name('signup');

// ui elements pages
Route::get('/alerts', function () {
    return view('pages.ui-elements.alerts', ['title' => 'Alerts']);
})->name('alerts');

Route::get('/avatars', function () {
    return view('pages.ui-elements.avatars', ['title' => 'Avatars']);
})->name('avatars');

Route::get('/badge', function () {
    return view('pages.ui-elements.badges', ['title' => 'Badges']);
})->name('badges');

Route::get('/buttons', function () {
    return view('pages.ui-elements.buttons', ['title' => 'Buttons']);
})->name('buttons');

Route::get('/image', function () {
    return view('pages.ui-elements.images', ['title' => 'Images']);
})->name('images');

Route::get('/videos', function () {
    return view('pages.ui-elements.videos', ['title' => 'Videos']);
})->name('videos');






















