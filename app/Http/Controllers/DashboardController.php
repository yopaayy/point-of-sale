<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Penjualan;
use App\Models\Produk;
use App\Models\Inventori;
use App\Models\DetailPenjualan;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        
        // 1. Total Penjualan Hari Ini
        $totalPenjualanHariIni = Penjualan::whereDate('tanggal', $today)
                                          ->where('status', 'selesai')
                                          ->sum('total_akhir');

        // 2. Total Transaksi Hari Ini
        $totalTransaksiHariIni = Penjualan::whereDate('tanggal', $today)
                                          ->where('status', 'selesai')
                                          ->count();

        // 3. Produk Terjual Hari Ini
        // Using DetailPenjualan filtered by today's transactions
        $produkTerjualHariIni = DetailPenjualan::whereHas('penjualan', function ($query) use ($today) {
                                                    $query->whereDate('tanggal', $today)->where('status', 'selesai');
                                                })->sum('qty');

        // 4. Stok Menipis
        $stokMenipis = 0;
        $produks = Produk::where('status', true)->get();
        foreach ($produks as $produk) {
            $totalStok = Inventori::where('produk_id', $produk->id)->sum('stok');
            if ($totalStok <= $produk->stok_minimum) {
                $stokMenipis++;
            }
        }

        // 5. Transaksi Terakhir (5 record terakhir)
        $transaksiTerakhir = Penjualan::with('pelanggan', 'user')
                                      ->orderBy('tanggal', 'desc')
                                      ->limit(5)
                                      ->get();

        // 6. Data Grafik Mingguan (7 Hari Terakhir)
        $grafikMingguan = [];
        $grafikLabels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $grafikLabels[] = $date->format('d M');
            $grafikMingguan[] = Penjualan::whereDate('tanggal', $date)
                                         ->where('status', 'selesai')
                                         ->sum('total_akhir');
        }

        return Inertia::render('Dashboard', [
            'overview' => [
                'total_penjualan' => $totalPenjualanHariIni,
                'total_transaksi' => $totalTransaksiHariIni,
                'produk_terjual' => $produkTerjualHariIni,
                'stok_menipis' => $stokMenipis,
            ],
            'transaksi_terakhir' => $transaksiTerakhir,
            'grafik_mingguan' => [
                'labels' => $grafikLabels,
                'data' => $grafikMingguan,
            ]
        ]);
    }
}
