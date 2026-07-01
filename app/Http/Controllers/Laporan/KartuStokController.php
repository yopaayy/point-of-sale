<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Produk;
use App\Models\Gudang;
use App\Models\PergerakanStok;

class KartuStokController extends Controller
{
    public function index(Request $request)
    {
        $produks = Produk::where('status', true)->get(['id', 'nama', 'kode_produk']);
        $gudangs = Gudang::all(['id', 'nama']);

        $produkId = $request->query('produk_id');
        $gudangId = $request->query('gudang_id');

        $pergerakanStok = null;
        $stokSaatIni = 0;

        if ($produkId) {
            $query = PergerakanStok::with(['gudang'])
                ->where('produk_id', $produkId);
            
            if ($gudangId) {
                $query->where('gudang_id', $gudangId);
            }

            $pergerakanStok = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

            // Hitung total stok saat ini untuk produk tersebut
            $stokQuery = \App\Models\Inventori::where('produk_id', $produkId);
            if ($gudangId) {
                $stokQuery->where('gudang_id', $gudangId);
            }
            $stokSaatIni = $stokQuery->sum('stok');
        }

        return Inertia::render('Laporan/KartuStok/Index', [
            'produks' => $produks,
            'gudangs' => $gudangs,
            'pergerakanStok' => $pergerakanStok,
            'filters' => [
                'produk_id' => $produkId,
                'gudang_id' => $gudangId
            ],
            'stokSaatIni' => $stokSaatIni
        ]);
    }
}
