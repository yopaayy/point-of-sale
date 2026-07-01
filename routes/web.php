<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Master\KategoriController;
use App\Http\Controllers\Master\MerkController;
use App\Http\Controllers\Master\SatuanController;
use App\Http\Controllers\Master\ProdukController;
use App\Http\Controllers\Transaksi\PembelianController;
use App\Http\Controllers\Transaksi\ShiftKasirController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Transaksi\KasirController;
use App\Http\Controllers\Laporan\KartuStokController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Master Data Routes
    Route::prefix('master')->name('master.')->group(function () {
        Route::resource('kategori', KategoriController::class)->except(['create', 'show', 'edit']);
        Route::resource('merk', MerkController::class)->except(['create', 'show', 'edit']);
        Route::resource('satuan', SatuanController::class)->except(['create', 'show', 'edit']);
        Route::post('produk/{produk}', [ProdukController::class, 'update'])->name('produk.update.post');
        Route::resource('produk', ProdukController::class);
    });

    // Transaksi Routes
    Route::prefix('transaksi')->name('transaksi.')->group(function () {
        Route::resource('pembelian', PembelianController::class);
        
        // Kasir
        Route::get('shift-kasir', [ShiftKasirController::class, 'index'])->name('shift-kasir.index');
        Route::post('shift-kasir', [ShiftKasirController::class, 'store'])->name('shift-kasir.store');
        Route::put('shift-kasir/{id}/close', [ShiftKasirController::class, 'close'])->name('shift-kasir.close');

        Route::get('kasir', [KasirController::class, 'index'])->name('kasir.index');
        Route::post('kasir', [KasirController::class, 'store'])->name('kasir.store');

        Route::get('penjualan', [\App\Http\Controllers\Transaksi\PenjualanController::class, 'index'])->name('penjualan.index');
    });

    // Laporan Routes
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('kartu-stok', [KartuStokController::class, 'index'])->name('kartu-stok.index');
        Route::get('penjualan', [LaporanController::class, 'penjualan'])->name('penjualan');
        Route::get('stok-menipis', [LaporanController::class, 'stokMenipis'])->name('stok-menipis');
    });

    // Pengaturan
    Route::get('pengaturan', [PengaturanController::class, 'index'])->name('pengaturan.index');
    Route::post('pengaturan', [PengaturanController::class, 'update'])->name('pengaturan.update');
});
