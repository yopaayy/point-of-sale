<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Pembelian;
use App\Models\DetailPembelian;
use App\Models\Supplier;
use App\Models\Gudang;
use App\Models\Produk;
use App\Models\Satuan;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;
use Exception;

class PembelianController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function index(Request $request)
    {
        $pembelian = Pembelian::with(['supplier', 'gudang', 'user'])
            ->latest()
            ->paginate(10);
            
        return Inertia::render('Transaksi/Pembelian/Index', [
            'pembelian' => $pembelian
        ]);
    }

    public function create()
    {
        // Untuk Cart: butuh supplier, gudang, produk, satuan
        $suppliers = Supplier::all();
        $gudangs = Gudang::all();
        $produks = Produk::with(['kategori', 'merk', 'baseUnit', 'satuanProduk.satuan'])->where('status', true)->get();
        $satuans = Satuan::all(); // Untuk konversi fallback (meskipun biasanya ditarik dari relasi produk)

        // Mapping produk ke format yang enak buat cart
        $produkMapped = $produks->map(function ($p) {
            $satuans = [];
            // Base unit
            $satuans[] = [
                'id' => $p->baseUnit->id,
                'nama' => $p->baseUnit->nama,
                'konversi' => 1,
                'is_base' => true,
                'harga_modal' => 0 // Sebenarnya harus ditarik dari HargaProduk, tapi untuk demo bisa disesuaikan
            ];
            
            // Satuan lain
            foreach ($p->satuanProduk as $sl) {
                $satuans[] = [
                    'id' => $sl->satuan->id,
                    'nama' => $sl->satuan->nama,
                    'konversi' => $sl->konversi,
                    'is_base' => false,
                    'harga_modal' => 0
                ];
            }

            return [
                'id' => $p->id,
                'kode_produk' => $p->kode_produk,
                'nama' => $p->nama,
                'kategori' => $p->kategori ? $p->kategori->nama : '-',
                'merk' => $p->merk ? $p->merk->nama : '-',
                'satuans' => $satuans,
                'allow_fraction' => $p->allow_fraction
            ];
        });

        return Inertia::render('Transaksi/Pembelian/Create', [
            'suppliers' => $suppliers,
            'gudangs' => $gudangs,
            'produks' => $produkMapped
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:supplier,id',
            'gudang_id' => 'required|exists:gudang,id',
            'tanggal' => 'required|date',
            'no_faktur' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.produk_id' => 'required|exists:produk,id',
            'items.*.satuan_id' => 'required|exists:satuan,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.harga_satuan' => 'required|numeric|min:0',
            'items.*.konversi_ke_base' => 'required|numeric|min:0.01'
        ]);

        try {
            DB::beginTransaction();

            $totalHarga = 0;

            // Buat Pembelian Header
            $pembelian = Pembelian::create([
                'supplier_id' => $request->supplier_id,
                'gudang_id' => $request->gudang_id,
                'user_id' => auth()->id(),
                'no_faktur' => $request->no_faktur,
                'tanggal' => $request->tanggal,
                'status' => 'selesai', // Langsung masuk gudang
                'total_harga' => 0 // Akan diupdate nanti
            ]);

            foreach ($request->items as $item) {
                $qtyBase = $item['qty'] * $item['konversi_ke_base'];
                $subtotal = $item['qty'] * $item['harga_satuan'];
                
                $totalHarga += $subtotal;

                DetailPembelian::create([
                    'pembelian_id' => $pembelian->id,
                    'produk_id' => $item['produk_id'],
                    'satuan_id' => $item['satuan_id'],
                    'qty' => $item['qty'],
                    'konversi_ke_base' => $item['konversi_ke_base'],
                    'qty_base' => $qtyBase,
                    'harga_satuan' => $item['harga_satuan'],
                    'subtotal' => $subtotal
                ]);

                // Update stok ke gudang melalui InventoryService
                $this->inventoryService->tambahStok(
                    $item['produk_id'],
                    $request->gudang_id,
                    $qtyBase,
                    'pembelian',
                    $pembelian->id,
                    "Pembelian dari supplier No Faktur: " . $request->no_faktur
                );
            }

            $pembelian->update(['total_harga' => $totalHarga]);

            DB::commit();

            return redirect()->route('transaksi.pembelian.index')->with('success', 'Transaksi Pembelian berhasil disimpan dan stok telah ditambahkan ke gudang.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Gagal menyimpan transaksi: ' . $e->getMessage()]);
        }
    }
}
