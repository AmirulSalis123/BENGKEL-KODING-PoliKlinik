<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DokterController;
use App\Http\Controllers\ObatController;
use App\Http\Controllers\PasienController;
use App\Http\Controllers\PoliController;
use App\Http\Controllers\Pasien\PoliController as PasienPoliController;
use App\Http\Controllers\Pasien\RiwayatController;
use App\Http\Controllers\Dokter\JadwalPeriksaController as DokterJadwalPeriksaController;
use App\Http\Controllers\Dokter\PeriksaPasienController as DokterPeriksaPasienController;
use App\Http\Controllers\Dokter\RiwayatPasienController as DokterRiwayatPasienController;
use Illuminate\Routing\Router;

// 1. ROUTE UTAMA / DEFAULT
Route::get('/', function () {
    return view('welcome');
    
});

// Halaman utama
Route::get('/home', function () {

    return view('home');

})->name('home');

// Halaman kontak
Route::get('/contact', function () {

    return view('contact');

})->name('contact');



// 2. ROUTE AUTENTIKASI (LOGIN & REGISTER)

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout']);

// 3. ROUTE DASHBOARD BERDASARKAN ROLE
// DASHBOARD ADMIN
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
    Route::resource('polis', PoliController::class);
    Route::resource('dokters', DokterController::class);
    Route::resource('pasien', PasienController::class);
    Route::resource('obat', ObatController::class);
    Route::post('obat/{id}/restock', [ObatController::class, 'restock'])
        ->name('obat.restock');
});

// DASHBOARD DOKTER 
Route::middleware(['auth', 'role:dokter'])->prefix('dokter')->group(function () {
    Route::get('/dashboard', function () {
        return view('dokter.dashboard');
    })->name('dokter.dashboard');
    Route::resource('jadwal-periksa', DokterJadwalPeriksaController::class)
        ->names('jadwal-periksa');
    Route::get('/periksa-pasien', [DokterPeriksaPasienController::class, 'index'])
        ->name('periksa-pasien.index');
    Route::post('/periksa-pasien', [DokterPeriksaPasienController::class, 'store'])
        ->name('periksa-pasien.store');
    Route::get('/periksa-pasien/{id}', [DokterPeriksaPasienController::class, 'create'])
        ->name('periksa-pasien.create');
    Route::post('/periksa-pasien/check-stock', [DokterPeriksaPasienController::class, 'checkStock'])
        ->name('periksa-pasien.check-stock');
    Route::get('/riwayat-pasien', [DokterRiwayatPasienController::class, 'index'])
        ->name('riwayat-pasien.index');
    Route::get('/riwayat-pasien/{id}', [DokterRiwayatPasienController::class, 'show'])
        ->name('riwayat-pasien.show');
});

// DASHBOARD PASIEN
Route::middleware(['auth', 'role:pasien'])->prefix('pasien')->group(function () {
    Route::get('/dashboard', function () {
        return view('pasien.dashboard');
    })->name('pasien.dashboard');
    Route::get('/daftar', [PasienPoliController::class, 'get'])
        ->name('pasien.daftar');
    Route::post('/daftar', [PasienPoliController::class, 'submit'])
        ->name('pasien.daftar.submit');
    Route::get('/riwayat', [RiwayatController::class, 'index'])
        ->name('riwayat.index');
});

