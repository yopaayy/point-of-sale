<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Penjualan;
use App\Models\Produk;
use App\Services\InventoryService;
use Carbon\Carbon;

class LaporanController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function penjualan(Request $request)
    {
        $filter = $request->query('filter', 'today'); // today, week, month, year, custom
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = Penjualan::with(['user', 'pelanggan']);

        if ($filter === 'today') {
            $query->whereDate('tanggal', Carbon::today());
        } elseif ($filter === 'week') {
            $query->whereBetween('tanggal', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($filter === 'month') {
            $query->whereMonth('tanggal', Carbon::now()->month)
                  ->whereYear('tanggal', Carbon::now()->year);
        } elseif ($filter === 'year') {
            $query->whereYear('tanggal', Carbon::now()->year);
        } elseif ($filter === 'custom' && $startDate && $endDate) {
            $query->whereBetween('tanggal', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }

        $penjualan = $query->orderBy('tanggal', 'desc')->get();

        $summary = [
            'total_transaksi' => $penjualan->count(),
            'total_omset' => $penjualan->sum('total_akhir'),
            'total_diskon' => $penjualan->sum('diskon'),
            'total_pajak' => $penjualan->sum('pajak'),
        ];

        return Inertia::render('Laporan/Penjualan', [
            'penjualan' => $penjualan,
            'summary' => $summary,
            'filter' => $filter,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    public function stokMenipis()
    {
        // Get all products
        $produkList = Produk::with(['kategori', 'merk', 'baseUnit'])->where('status', true)->get();
        
        $stokMenipis = [];

        foreach ($produkList as $produk) {
            $stokBase = \App\Models\Inventori::where('produk_id', $produk->id)->sum('stok');
            
            // Compare with minimum_stok
            if ($stokBase <= $produk->minimum_stok) {
                $produkArray = $produk->toArray();
                $produkArray['stok_saat_ini'] = $stokBase;
                $stokMenipis[] = $produkArray;
            }
        }

        return Inertia::render('Laporan/StokMenipis', [
            'stokMenipis' => $stokMenipis
        ]);
    }
}
