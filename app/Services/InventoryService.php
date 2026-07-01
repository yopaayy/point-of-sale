<?php

namespace App\Services;

use App\Models\Inventori;
use App\Models\PergerakanStok;
use App\Models\Produk;
use Illuminate\Support\Facades\DB;
use Exception;

class InventoryService
{
    /**
     * Tambah stok produk ke gudang tertentu
     *
     * @param int $produkId
     * @param int $gudangId
     * @param float $qtyBase (Sudah dikonversi ke Base Unit)
     * @param string $referensiTipe (pembelian, retur_penjualan, dll)
     * @param int|null $referensiId
     * @param string|null $keterangan
     * @return Inventori
     * @throws Exception
     */
    public function tambahStok($produkId, $gudangId, $qtyBase, $referensiTipe, $referensiId = null, $keterangan = null)
    {
        if ($qtyBase <= 0) {
            throw new Exception("Kuantitas tambah stok harus lebih dari 0.");
        }

        // Cek allow_fraction
        $produk = Produk::findOrFail($produkId);
        if (!$produk->allow_fraction && floor($qtyBase) != $qtyBase) {
            throw new Exception("Produk {$produk->nama} tidak mengizinkan stok desimal/pecahan.");
        }

        return DB::transaction(function () use ($produkId, $gudangId, $qtyBase, $referensiTipe, $referensiId, $keterangan) {
            $inventori = Inventori::firstOrCreate(
                ['produk_id' => $produkId, 'gudang_id' => $gudangId],
                ['stok' => 0]
            );

            $inventori->stok += $qtyBase;
            $inventori->save();

            PergerakanStok::create([
                'produk_id' => $produkId,
                'gudang_id' => $gudangId,
                'tipe' => 'masuk',
                'qty' => $qtyBase,
                'stok_akhir' => $inventori->stok,
                'referensi_tipe' => $referensiTipe,
                'referensi_id' => $referensiId,
                'keterangan' => $keterangan
            ]);

            return $inventori;
        });
    }

    /**
     * Kurangi stok produk dari gudang tertentu
     *
     * @param int $produkId
     * @param int $gudangId
     * @param float $qtyBase (Sudah dikonversi ke Base Unit)
     * @param string $referensiTipe (penjualan, retur_pembelian, dll)
     * @param int|null $referensiId
     * @param string|null $keterangan
     * @return Inventori
     * @throws Exception
     */
    public function kurangiStok($produkId, $gudangId, $qtyBase, $referensiTipe, $referensiId = null, $keterangan = null)
    {
        if ($qtyBase <= 0) {
            throw new Exception("Kuantitas kurangi stok harus lebih dari 0.");
        }

        $produk = Produk::findOrFail($produkId);
        if (!$produk->allow_fraction && floor($qtyBase) != $qtyBase) {
            throw new Exception("Produk {$produk->nama} tidak mengizinkan stok desimal/pecahan.");
        }

        return DB::transaction(function () use ($produkId, $gudangId, $qtyBase, $referensiTipe, $referensiId, $keterangan) {
            $inventori = Inventori::where('produk_id', $produkId)->where('gudang_id', $gudangId)->first();

            if (!$inventori || $inventori->stok < $qtyBase) {
                throw new Exception("Stok tidak mencukupi untuk pengurangan. Gudang ID: {$gudangId}, Produk ID: {$produkId}");
            }

            $inventori->stok -= $qtyBase;
            $inventori->save();

            PergerakanStok::create([
                'produk_id' => $produkId,
                'gudang_id' => $gudangId,
                'tipe' => 'keluar',
                'qty' => $qtyBase,
                'stok_akhir' => $inventori->stok,
                'referensi_tipe' => $referensiTipe,
                'referensi_id' => $referensiId,
                'keterangan' => $keterangan
            ]);

            return $inventori;
        });
    }
}
