<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Penjualan;
use App\Models\Pengaturan;

class PenjualanController extends Controller
{
    public function index(Request $request)
    {
        $query = Penjualan::with([
            'user', 
            'pelanggan', 
            'detailPenjualan.produk', 
            'detailPenjualan.satuan',
            'pembayaran'
        ])->orderBy('created_at', 'desc');

        // Pencarian berdasarkan No Struk
        if ($request->has('search') && $request->search != '') {
            $query->where('no_struk', 'like', '%' . $request->search . '%');
        }

        $penjualans = $query->paginate(15)->withQueryString();

        $pengaturan = Pengaturan::pluck('value', 'key')->toArray();
        $defaultSettings = [
            'nama_toko' => 'ALFA-POS MARKET',
            'alamat_toko' => 'Jl. Kebenaran No. 123, Jakarta',
            'telepon_toko' => '08123456789',
            'catatan_struk' => 'Barang yang sudah dibeli\ntidak dapat ditukar/dikembalikan',
            'pajak_persen' => '0',
        ];

        return Inertia::render('Transaksi/Penjualan/Index', [
            'penjualans' => $penjualans,
            'filters' => $request->only(['search']),
            'pengaturan' => array_merge($defaultSettings, $pengaturan)
        ]);
    }
}
