<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PosController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\AkunController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    // Kasir & Admin (Umum)
    Route::get('/pos', [PosController::class, 'pos'])->name('pos');
    Route::post('/pos/checkout', [PosController::class, 'storeTransaksi'])->name('pos.checkout');
    
    // Riwayat Transaksi (Kasir & Admin)
    Route::get('/riwayat', [PosController::class, 'riwayat'])->name('riwayat');

    // Manajemen Produk (Kasir & Admin)
    Route::get('/produk', [PosController::class, 'produk'])->name('produk');
    Route::post('/produk', [PosController::class, 'storeProduk'])->name('produk.store');
    Route::put('/produk/{id}', [PosController::class, 'updateProduk'])->name('produk.update');
    Route::delete('/produk/{id}', [PosController::class, 'destroyProduk'])->name('produk.destroy');

    // Admin Only
    Route::middleware('role:admin')->group(function () {
        Route::get('/dashboard', [PosController::class, 'dashboard'])->name('dashboard');
        
        Route::get('/kategori', [PosController::class, 'kategori'])->name('kategori');
        Route::post('/kategori', [PosController::class, 'storeKategori'])->name('kategori.store');
        Route::put('/kategori/{id}', [PosController::class, 'updateKategori'])->name('kategori.update');
        Route::delete('/kategori/{id}', [PosController::class, 'destroyKategori'])->name('kategori.destroy');
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan');
        Route::get('/laporan/export/penjualan', [LaporanController::class, 'exportPenjualan'])->name('laporan.export.penjualan');
        Route::get('/laporan/export/keuangan', [LaporanController::class, 'exportKeuangan'])->name('laporan.export.keuangan');
        
        Route::get('/akun', [AkunController::class, 'index'])->name('akun');
        Route::post('/akun', [AkunController::class, 'store'])->name('akun.store');
        Route::put('/akun/{id}', [AkunController::class, 'update'])->name('akun.update');
        Route::delete('/akun/{id}', [AkunController::class, 'destroy'])->name('akun.destroy');

        Route::get('/setting', [App\Http\Controllers\SettingController::class, 'index'])->name('setting');
        Route::put('/setting', [App\Http\Controllers\SettingController::class, 'update'])->name('setting.update');
    });
});
