<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\Kategori;
use App\Models\Merk;
use App\Models\Satuan;
use App\Models\SatuanProduk;
use App\Models\HargaProduk;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProdukController extends Controller
{
    public function index(Request $request)
    {
        $sortField = $request->input('sort', 'id');
        $sortDir = $request->input('dir', 'desc');

        if (!in_array($sortField, ['id', 'kode_produk', 'nama', 'created_at'])) {
            $sortField = 'id';
        }
        if (!in_array($sortDir, ['asc', 'desc'])) {
            $sortDir = 'desc';
        }

        $produk = Produk::with(['kategori', 'merk', 'baseUnit'])
            ->when($request->search, function ($query, $search) {
                $query->where('nama', 'like', "%{$search}%")
                      ->orWhere('kode_produk', 'like', "%{$search}%")
                      ->orWhereHas('kategori', function($q) use ($search) {
                          $q->where('nama', 'like', "%{$search}%");
                      })
                      ->orWhereHas('merk', function($q) use ($search) {
                          $q->where('nama', 'like', "%{$search}%");
                      });
            })
            ->orderBy($sortField, $sortDir)
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Master/Produk/Index', [
            'produk' => $produk,
            'filters' => $request->only(['search', 'sort', 'dir'])
        ]);
    }

    public function create()
    {
        return Inertia::render('Master/Produk/Form', [
            'kategoris' => Kategori::orderBy('nama')->get(),
            'merks' => Merk::orderBy('nama')->get(),
            'satuans' => Satuan::orderBy('nama')->get(),
            'produk' => null,
            'units' => []
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategori,id',
            'merk_id' => 'nullable|exists:merk,id',
            'base_unit_id' => 'required|exists:satuan,id',
            'allow_fraction' => 'boolean',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|max:2048',
            'barcode' => 'nullable|string|max:50|unique:produk,barcode',
            
            // Validate units array
            'units' => 'required|array|min:1',
            'units.*.satuan_id' => 'required|exists:satuan,id',
            'units.*.konversi' => 'required|numeric|min:0.001',
            'units.*.harga_modal' => 'required|numeric|min:0',
            'units.*.harga_jual' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $data = $request->except(['units', 'gambar', 'kode_produk']);
            $data['allow_fraction'] = $request->boolean('allow_fraction');
            $data['kode_produk'] = $this->generateKodeProduk();
            
            if (empty($data['barcode'])) {
                $data['barcode'] = $this->generateBarcode();
            }

            if ($request->hasFile('gambar')) {
                $data['gambar'] = $request->file('gambar')->store('produk', 'public');
            }

            $produk = Produk::create($data);

            foreach ($request->units as $unit) {
                $is_base = $unit['satuan_id'] == $request->base_unit_id;
                
                SatuanProduk::create([
                    'produk_id' => $produk->id,
                    'satuan_id' => $unit['satuan_id'],
                    'konversi' => $is_base ? 1 : $unit['konversi'],
                    'is_base' => $is_base,
                ]);

                HargaProduk::create([
                    'produk_id' => $produk->id,
                    'satuan_id' => $unit['satuan_id'],
                    'harga_modal' => $unit['harga_modal'],
                    'harga_jual' => $unit['harga_jual'],
                ]);
            }
        });

        return redirect()->route('master.produk.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Produk $produk)
    {
        $produk->load(['satuanProduk', 'hargaProduk']);

        // Format existing units for the form
        $units = $produk->satuanProduk->map(function ($sp) use ($produk) {
            $harga = $produk->hargaProduk->where('satuan_id', $sp->satuan_id)->first();
            return [
                'satuan_id' => $sp->satuan_id,
                'konversi' => (float) $sp->konversi,
                'harga_modal' => $harga ? (float) $harga->harga_modal : 0,
                'harga_jual' => $harga ? (float) $harga->harga_jual : 0,
            ];
        });

        return Inertia::render('Master/Produk/Form', [
            'kategoris' => Kategori::orderBy('nama')->get(),
            'merks' => Merk::orderBy('nama')->get(),
            'satuans' => Satuan::orderBy('nama')->get(),
            'produk' => $produk,
            'units' => $units
        ]);
    }

    public function update(Request $request, Produk $produk)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategori,id',
            'merk_id' => 'nullable|exists:merk,id',
            'base_unit_id' => 'required|exists:satuan,id',
            'allow_fraction' => 'boolean',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|max:2048',
            'barcode' => 'nullable|string|max:50|unique:produk,barcode,' . $produk->id,
            
            // Validate units array
            'units' => 'required|array|min:1',
            'units.*.satuan_id' => 'required|exists:satuan,id',
            'units.*.konversi' => 'required|numeric|min:0.001',
            'units.*.harga_modal' => 'required|numeric|min:0',
            'units.*.harga_jual' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $produk) {
            $data = $request->except(['units', 'gambar', 'kode_produk']);
            $data['allow_fraction'] = $request->boolean('allow_fraction');

            if (empty($data['barcode'])) {
                $data['barcode'] = $this->generateBarcode();
            }

            if ($request->hasFile('gambar')) {
                if ($produk->gambar) {
                    Storage::disk('public')->delete($produk->gambar);
                }
                $data['gambar'] = $request->file('gambar')->store('produk', 'public');
            }

            $produk->update($data);

            // Re-sync units and prices
            SatuanProduk::where('produk_id', $produk->id)->delete();
            HargaProduk::where('produk_id', $produk->id)->delete();

            foreach ($request->units as $unit) {
                $is_base = $unit['satuan_id'] == $request->base_unit_id;
                
                SatuanProduk::create([
                    'produk_id' => $produk->id,
                    'satuan_id' => $unit['satuan_id'],
                    'konversi' => $is_base ? 1 : $unit['konversi'],
                    'is_base' => $is_base,
                ]);

                HargaProduk::create([
                    'produk_id' => $produk->id,
                    'satuan_id' => $unit['satuan_id'],
                    'harga_modal' => $unit['harga_modal'],
                    'harga_jual' => $unit['harga_jual'],
                ]);
            }
        });

        return redirect()->route('master.produk.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Produk $produk)
    {
        DB::transaction(function () use ($produk) {
            if ($produk->gambar) {
                Storage::disk('public')->delete($produk->gambar);
            }
            // Cascading delete is assumed to be setup in DB, or done manually
            SatuanProduk::where('produk_id', $produk->id)->delete();
            HargaProduk::where('produk_id', $produk->id)->delete();
            $produk->delete();
        });

        return redirect()->back()->with('success', 'Produk berhasil dihapus.');
    }

    private function generateKodeProduk()
    {
        $lastProduk = Produk::where('kode_produk', 'like', 'PRD-%')
                            ->orderBy('id', 'desc')
                            ->first();

        if (!$lastProduk) {
            return 'PRD-000001';
        }

        $lastKode = $lastProduk->kode_produk;
        preg_match('/PRD-(\d+)/', $lastKode, $matches);
        
        if (isset($matches[1])) {
            $newNumber = intval($matches[1]) + 1;
            return 'PRD-' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
        }

        return 'PRD-' . strtoupper(\Illuminate\Support\Str::random(6));
    }

    private function generateBarcode()
    {
        // Auto-generate a 12 digit string resembling EAN/UPC or Code128
        // Using timestamp and random digits to ensure uniqueness
        $prefix = '899'; // Example standard prefix for Indonesia
        $suffix = str_pad(mt_rand(1, 999999999), 9, '0', STR_PAD_LEFT);
        $barcode = $prefix . $suffix;
        
        // Ensure uniqueness
        while (Produk::where('barcode', $barcode)->exists()) {
            $suffix = str_pad(mt_rand(1, 999999999), 9, '0', STR_PAD_LEFT);
            $barcode = $prefix . $suffix;
        }

        return $barcode;
    }
}
