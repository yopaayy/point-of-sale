<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\ShiftKasir;
use App\Models\Produk;
use App\Models\Promo;
use App\Models\Pelanggan;
use App\Models\Member;
use App\Models\Penjualan;
use App\Models\DetailPenjualan;
use App\Models\Pembayaran;
use App\Models\Gudang;
use App\Models\Pengaturan;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KasirController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function index()
    {
        // 1. Validasi Shift Kasir
        $activeShift = ShiftKasir::where('user_id', auth()->id())
            ->where('status', 'buka')
            ->first();

        if (!$activeShift) {
            return redirect()->route('transaksi.shift-kasir.index')
                ->withErrors(['error' => 'Anda harus membuka shift kasir terlebih dahulu sebelum melakukan penjualan.']);
        }

        // 2. Ambil Master Data
        // Pelanggan dan Member
        $pelanggans = Pelanggan::all();
        $members = Member::all();

        // Promo yang aktif
        $promos = Promo::whereDate('tanggal_mulai', '<=', now())
            ->whereDate('tanggal_selesai', '>=', now())
            ->get();

        // Produk dengan harga_produk, stok, dll
        // Untuk mempercepat MVP, kita akan buat mock harga sementara di mapping jika harga_produk kosong.
        $produks = Produk::with(['kategori', 'merk', 'baseUnit', 'satuanProduk.satuan', 'hargaProduk.satuan'])
            ->where('status', true)
            ->get();

        $produkMapped = $produks->map(function ($p) {
            $satuans = [];
            
            // Cari harga base unit
            $hargaBase = $p->hargaProduk->where('satuan_id', $p->base_unit_id)->first();
            $hargaJualBase = $hargaBase ? $hargaBase->harga_jual : 3500; // default 3500 jika tidak diset

            $satuans[] = [
                'id' => $p->baseUnit->id,
                'nama' => $p->baseUnit->nama,
                'konversi' => 1,
                'is_base' => true,
                'harga_jual' => $hargaJualBase
            ];
            
            foreach ($p->satuanProduk as $sp) {
                $hargaLain = $p->hargaProduk->where('satuan_id', $sp->satuan_id)->first();
                $satuans[] = [
                    'id' => $sp->satuan->id,
                    'nama' => $sp->satuan->nama,
                    'konversi' => $sp->konversi,
                    'is_base' => false,
                    'harga_jual' => $hargaLain ? $hargaLain->harga_jual : ($hargaJualBase * $sp->konversi)
                ];
            }

            return [
                'id' => $p->id,
                'kode_produk' => $p->kode_produk,
                'barcode' => $p->barcode,
                'nama' => $p->nama,
                'kategori' => $p->kategori ? $p->kategori->nama : '-',
                'satuans' => $satuans,
                'allow_fraction' => $p->allow_fraction
            ];
        });

        $pengaturan = Pengaturan::pluck('value', 'key')->toArray();
        $defaultSettings = [
            'nama_toko' => 'ALFA-POS MARKET',
            'alamat_toko' => 'Jl. Kebenaran No. 123, Jakarta',
            'telepon_toko' => '08123456789',
            'catatan_struk' => 'Barang yang sudah dibeli\ntidak dapat ditukar/dikembalikan',
            'pajak_persen' => '0',
        ];

        return Inertia::render('Transaksi/Kasir/Index', [
            'activeShift' => $activeShift,
            'pelanggans' => $pelanggans,
            'members' => $members,
            'promos' => $promos,
            'produks' => $produkMapped,
            'pengaturan' => array_merge($defaultSettings, $pengaturan)
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'pelanggan_id' => 'nullable|exists:pelanggan,id',
            'member_id' => 'nullable|exists:member,id',
            'promo_id' => 'nullable|exists:promo,id', // Opsional, kita simpan diskon dari frontend
            'items' => 'required|array|min:1',
            'items.*.produk_id' => 'required|exists:produk,id',
            'items.*.satuan_id' => 'required|exists:satuan,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.harga_satuan' => 'required|numeric|min:0',
            'items.*.konversi_ke_base' => 'required|numeric|min:0.01',
            'items.*.diskon' => 'numeric|min:0',
            'metode_pembayaran' => 'required|string',
            'nominal_bayar' => 'required|numeric|min:0',
            'total_akhir' => 'required|numeric|min:0'
        ]);

        $activeShift = ShiftKasir::where('user_id', auth()->id())->where('status', 'buka')->first();
        if (!$activeShift) {
            return redirect()->back()->withErrors(['error' => 'Shift sudah ditutup!']);
        }

        try {
            DB::beginTransaction();

            $subtotal = 0;
            $totalDiskonItem = 0;

            // Generate No Struk
            $noStruk = 'INV-' . date('Ymd') . '-' . str_pad(Penjualan::whereDate('tanggal', date('Y-m-d'))->count() + 1, 4, '0', STR_PAD_LEFT);

            // Buat Penjualan Header
            $penjualan = Penjualan::create([
                'cabang_id' => $activeShift->cabang_id,
                'pelanggan_id' => $request->pelanggan_id,
                'member_id' => $request->member_id,
                'user_id' => auth()->id(),
                'no_struk' => $noStruk,
                'tanggal' => Carbon::now(),
                'subtotal' => 0, // diupdate nanti
                'diskon' => $request->diskon_tambahan ?? 0,
                'pajak' => $request->pajak ?? 0,
                'total_akhir' => $request->total_akhir,
                'status' => 'selesai'
            ]);

            // Cari gudang untuk cabang ini
            $gudang = Gudang::where('cabang_id', $activeShift->cabang_id)->first();
            if (!$gudang) {
                return redirect()->back()->withErrors(['error' => 'Cabang ini tidak memiliki gudang.']);
            }

            foreach ($request->items as $item) {
                $qtyBase = $item['qty'] * $item['konversi_ke_base'];
                $itemSubtotal = $item['qty'] * $item['harga_satuan'];
                $itemDiskon = $item['diskon'] ?? 0;
                
                $subtotal += $itemSubtotal;
                $totalDiskonItem += $itemDiskon;

                DetailPenjualan::create([
                    'penjualan_id' => $penjualan->id,
                    'produk_id' => $item['produk_id'],
                    'satuan_id' => $item['satuan_id'],
                    'qty' => $item['qty'],
                    'konversi_ke_base' => $item['konversi_ke_base'],
                    'qty_base' => $qtyBase,
                    'harga_satuan' => $item['harga_satuan'],
                    'subtotal' => $itemSubtotal,
                    'diskon' => $itemDiskon
                ]);

                // Kurangi stok via InventoryService
                $this->inventoryService->kurangiStok(
                    $item['produk_id'],
                    $gudang->id,
                    $qtyBase,
                    'penjualan',
                    $penjualan->id,
                    "Penjualan Kasir Struk: " . $noStruk
                );
            }

            $penjualan->update([
                'subtotal' => $subtotal,
                // Diskon bisa diskon per item + diskon promo
            ]);

            // Catat Pembayaran
            Pembayaran::create([
                'penjualan_id' => $penjualan->id,
                'shift_kasir_id' => $activeShift->id,
                'metode' => $request->metode_pembayaran,
                'nominal' => $request->nominal_bayar,
                'referensi' => $request->referensi_pembayaran ?? '-'
            ]);

            // Update Pemasukan Shift
            $activeShift->increment('total_pemasukan', $penjualan->total_akhir);

            DB::commit();

            // Load relasi untuk keperluan cetak struk
            $penjualan->load([
                'detailPenjualan.produk', 
                'detailPenjualan.satuan', 
                'user', 
                'pelanggan',
                'pembayaran'
            ]);

            return redirect()->back()->with([
                'success' => 'Transaksi berhasil disimpan!',
                'cetak_struk' => $penjualan
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Gagal menyimpan transaksi: ' . $e->getMessage()]);
        }
    }
}
