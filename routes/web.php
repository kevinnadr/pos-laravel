<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PosController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\AkunController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/forgot-password', [PasswordResetController::class, 'create'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    // Point of Sale and transaction history are for admins and cashiers.
    Route::middleware('role:admin,kasir')->group(function () {
        Route::get('/pos', [PosController::class, 'pos'])->name('pos');
        Route::post('/pos/checkout', [PosController::class, 'storeTransaksi'])->name('pos.checkout');
        Route::get('/riwayat', [PosController::class, 'riwayat'])->name('riwayat');
    });

    // Product viewing and editing are available to admins, cashiers, and warehouse users.
    Route::middleware('role:admin,kasir,gudang')->group(function () {
        Route::get('/produk', [PosController::class, 'produk'])->name('produk');
        Route::put('/produk/{id}', [PosController::class, 'updateProduk'])->name('produk.update');
    });

    // Only admins and warehouse users can add or remove products.
    Route::middleware('role:admin,gudang')->group(function () {
        Route::post('/produk', [PosController::class, 'storeProduk'])->name('produk.store');
        Route::delete('/produk/{id}', [PosController::class, 'destroyProduk'])->name('produk.destroy');
    });

    // Admin Only
    Route::middleware('role:admin')->group(function () {
        Route::get('/dashboard', [PosController::class, 'dashboard'])->name('dashboard');
        
        Route::get('/kategori', function () {
            return redirect()->route('produk');
        })->name('kategori');
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
