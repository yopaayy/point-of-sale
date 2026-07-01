<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kategori;
use App\Models\Merk;
use App\Models\Satuan;
use App\Models\Produk;
use App\Models\SatuanProduk;
use App\Models\HargaProduk;
use Illuminate\Support\Str;

class MasterDataSeeder extends Seeder
{
    public function run()
    {
        // 1. Kategori
        $kategori1 = Kategori::create(['nama' => 'Sembako']);
        $kategori2 = Kategori::create(['nama' => 'Minuman']);
        $kategori3 = Kategori::create(['nama' => 'Snack']);

        // 2. Merk
        $merk1 = Merk::create(['nama' => 'Indofood']);
        $merk2 = Merk::create(['nama' => 'Bimoli']);
        $merk3 = Merk::create(['nama' => 'Aqua']);

        // 3. Satuan
        $satuanPcs = Satuan::create(['nama' => 'Pieces', 'nama_pendek' => 'Pcs']);
        $satuanDus = Satuan::create(['nama' => 'Karton (Dus)', 'nama_pendek' => 'Dus']);
        $satuanLiter = Satuan::create(['nama' => 'Liter', 'nama_pendek' => 'Ltr']);

        // 4. Produk Sembako - Minyak Goreng
        $minyak = Produk::create([
            'kode_produk' => 'PRD-000001',
            'nama' => 'Minyak Goreng Bimoli 1 Liter',
            'kategori_id' => $kategori1->id,
            'merk_id' => $merk2->id,
            'base_unit_id' => $satuanPcs->id,
            'allow_fraction' => false,
            'deskripsi' => 'Minyak Goreng Pilihan Keluarga',
        ]);

        // Base Unit (Pcs)
        SatuanProduk::create(['produk_id' => $minyak->id, 'satuan_id' => $satuanPcs->id, 'konversi' => 1, 'is_base' => true]);
        HargaProduk::create(['produk_id' => $minyak->id, 'satuan_id' => $satuanPcs->id, 'harga_modal' => 18000, 'harga_jual' => 20000]);

        // Konversi (Dus) -> 1 Dus = 12 Pcs
        SatuanProduk::create(['produk_id' => $minyak->id, 'satuan_id' => $satuanDus->id, 'konversi' => 12, 'is_base' => false]);
        HargaProduk::create(['produk_id' => $minyak->id, 'satuan_id' => $satuanDus->id, 'harga_modal' => 210000, 'harga_jual' => 235000]);

        // 5. Produk Minuman - Aqua
        $aqua = Produk::create([
            'kode_produk' => 'PRD-000002',
            'nama' => 'Air Mineral Aqua 600ml',
            'kategori_id' => $kategori2->id,
            'merk_id' => $merk3->id,
            'base_unit_id' => $satuanPcs->id,
            'allow_fraction' => false,
        ]);

        SatuanProduk::create(['produk_id' => $aqua->id, 'satuan_id' => $satuanPcs->id, 'konversi' => 1, 'is_base' => true]);
        HargaProduk::create(['produk_id' => $aqua->id, 'satuan_id' => $satuanPcs->id, 'harga_modal' => 2500, 'harga_jual' => 3500]);

        SatuanProduk::create(['produk_id' => $aqua->id, 'satuan_id' => $satuanDus->id, 'konversi' => 24, 'is_base' => false]);
        HargaProduk::create(['produk_id' => $aqua->id, 'satuan_id' => $satuanDus->id, 'harga_modal' => 55000, 'harga_jual' => 65000]);
        
        // 6. Produk Indomie
        $indomie = Produk::create([
            'kode_produk' => 'PRD-000003',
            'nama' => 'Indomie Goreng Spesial',
            'kategori_id' => $kategori3->id,
            'merk_id' => $merk1->id,
            'base_unit_id' => $satuanPcs->id,
            'allow_fraction' => false,
        ]);

        SatuanProduk::create(['produk_id' => $indomie->id, 'satuan_id' => $satuanPcs->id, 'konversi' => 1, 'is_base' => true]);
        HargaProduk::create(['produk_id' => $indomie->id, 'satuan_id' => $satuanPcs->id, 'harga_modal' => 2700, 'harga_jual' => 3500]);

        SatuanProduk::create(['produk_id' => $indomie->id, 'satuan_id' => $satuanDus->id, 'konversi' => 40, 'is_base' => false]);
        HargaProduk::create(['produk_id' => $indomie->id, 'satuan_id' => $satuanDus->id, 'harga_modal' => 105000, 'harga_jual' => 115000]);
    }
}
