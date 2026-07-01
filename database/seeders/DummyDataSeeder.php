<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Kategori;
use App\Models\Produk;
use App\Models\Member;
use App\Models\Supplier;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Pengeluaran;
use Carbon\Carbon;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Kategori
        $kategoriList = ['Makanan', 'Minuman', 'Alat Tulis', 'Kebutuhan Sehari-hari'];
        foreach ($kategoriList as $k) {
            Kategori::updateOrCreate(['nama_kategori' => $k]);
        }
        $kategoris = Kategori::all();

        // 2. Supplier
        $supplierData = [
            ['nama' => 'PT. Sinar Jaya', 'alamat' => 'Jl. Merdeka No. 10, Jakarta', 'telepon' => '081234567890'],
            ['nama' => 'CV. Abadi Makmur', 'alamat' => 'Jl. Sudirman No. 22, Bandung', 'telepon' => '089876543210'],
        ];
        foreach ($supplierData as $s) {
            Supplier::updateOrCreate(['nama' => $s['nama']], $s);
        }
        $suppliers = Supplier::all();

        // 3. Member
        $memberData = [
            ['nama' => 'Budi Santoso', 'alamat' => 'Jl. Mawar 15, Surabaya', 'telepon' => '085111111111'],
            ['nama' => 'Siti Aminah', 'alamat' => 'Jl. Melati 20, Yogyakarta', 'telepon' => '085222222222'],
            ['nama' => 'Andi Susanto', 'alamat' => 'Jl. Anggrek 5, Semarang', 'telepon' => '085333333333'],
        ];
        foreach ($memberData as $idx => $m) {
            $m['kode_member'] = tambah_nol_didepan($idx + 1, 5);
            Member::updateOrCreate(['nama' => $m['nama']], $m);
        }
        $members = Member::all();

        // 4. Produk
        $produkData = [
            ['nama_produk' => 'Roti Tawar', 'merk' => 'Sari Roti', 'harga_beli' => 10000, 'harga_jual' => 12000, 'diskon' => 0, 'stok' => 50],
            ['nama_produk' => 'Air Mineral 600ml', 'merk' => 'Aqua', 'harga_beli' => 2000, 'harga_jual' => 3000, 'diskon' => 0, 'stok' => 100],
            ['nama_produk' => 'Buku Tulis 38 Lembar', 'merk' => 'Sidu', 'harga_beli' => 2500, 'harga_jual' => 3500, 'diskon' => 5, 'stok' => 200],
            ['nama_produk' => 'Pena Hitam', 'merk' => 'Snowman', 'harga_beli' => 1500, 'harga_jual' => 2000, 'diskon' => 0, 'stok' => 150],
            ['nama_produk' => 'Sabun Mandi', 'merk' => 'Lifebuoy', 'harga_beli' => 3000, 'harga_jual' => 4000, 'diskon' => 0, 'stok' => 80],
            ['nama_produk' => 'Kopi Instan', 'merk' => 'Nescafe', 'harga_beli' => 10000, 'harga_jual' => 12500, 'diskon' => 10, 'stok' => 60],
            ['nama_produk' => 'Susu Kotak', 'merk' => 'Ultramilk', 'harga_beli' => 4500, 'harga_jual' => 6000, 'diskon' => 0, 'stok' => 120],
        ];

        foreach ($produkData as $idx => $p) {
            $p['id_kategori'] = $kategoris->random()->id_kategori;
            $p['kode_produk'] = 'P' . tambah_nol_didepan($idx + 1, 6);
            Produk::updateOrCreate(['nama_produk' => $p['nama_produk']], $p);
        }
        $produks = Produk::all();

        // 5. Pembelian (Purchase)
        for ($i = 1; $i <= 3; $i++) {
            $supplier = $suppliers->random();
            $pembelian = Pembelian::create([
                'id_supplier' => $supplier->id_supplier,
                'total_item' => 0,
                'total_harga' => 0,
                'diskon' => rand(0, 5),
                'bayar' => 0,
                'created_at' => Carbon::now()->subDays(rand(1, 10))
            ]);

            $totalItem = 0;
            $totalHarga = 0;

            for ($j = 1; $j <= rand(2, 4); $j++) {
                $produk = $produks->random();
                $jumlah = rand(10, 50);
                $subtotal = $produk->harga_beli * $jumlah;
                
                PembelianDetail::create([
                    'id_pembelian' => $pembelian->id_pembelian,
                    'id_produk' => $produk->id_produk,
                    'harga_beli' => $produk->harga_beli,
                    'jumlah' => $jumlah,
                    'subtotal' => $subtotal
                ]);

                $totalItem += $jumlah;
                $totalHarga += $subtotal;
            }

            $diskonAmount = $totalHarga * ($pembelian->diskon / 100);
            $bayar = $totalHarga - $diskonAmount;

            $pembelian->update([
                'total_item' => $totalItem,
                'total_harga' => $totalHarga,
                'bayar' => $bayar
            ]);
        }

        // 6. Penjualan (Sales)
        for ($i = 1; $i <= 10; $i++) {
            $member = rand(0, 1) ? $members->random() : null;
            $penjualan = Penjualan::create([
                'id_member' => $member ? $member->id_member : null,
                'total_item' => 0,
                'total_harga' => 0,
                'diskon' => 0,
                'bayar' => 0,
                'diterima' => 0,
                'id_user' => 1,
                'created_at' => Carbon::now()->subDays(rand(0, 5))
            ]);

            $totalItem = 0;
            $totalHarga = 0;

            for ($j = 1; $j <= rand(1, 5); $j++) {
                $produk = $produks->random();
                $jumlah = rand(1, 5);
                $hargaJual = $produk->harga_jual;
                $diskonProduk = $produk->diskon;
                
                $hargaSetelahDiskon = $hargaJual - ($hargaJual * $diskonProduk / 100);
                $subtotal = $hargaSetelahDiskon * $jumlah;

                PenjualanDetail::create([
                    'id_penjualan' => $penjualan->id_penjualan,
                    'id_produk' => $produk->id_produk,
                    'harga_jual' => $hargaJual,
                    'jumlah' => $jumlah,
                    'diskon' => $diskonProduk,
                    'subtotal' => $subtotal
                ]);

                $totalItem += $jumlah;
                $totalHarga += $subtotal;
            }

            // Setting Diskon for Member
            $diskonMember = 0;
            if ($member) {
                $setting = DB::table('setting')->first();
                $diskonMember = $setting ? $setting->diskon : 5; // default 5%
            }

            $totalDiskonMember = $totalHarga * ($diskonMember / 100);
            $bayar = $totalHarga - $totalDiskonMember;
            
            // Generate valid payment amount
            $diterimaChoices = [50000, 100000, 150000, 200000, 500000];
            $diterima = 0;
            foreach ($diterimaChoices as $choice) {
                if ($choice >= $bayar) {
                    $diterima = $choice;
                    break;
                }
            }
            if ($diterima == 0) $diterima = $bayar; // fallback

            $penjualan->update([
                'total_item' => $totalItem,
                'total_harga' => $totalHarga,
                'diskon' => $diskonMember,
                'bayar' => $bayar,
                'diterima' => $diterima
            ]);
        }

        // 7. Pengeluaran (Expenses)
        $pengeluaranData = [
            ['deskripsi' => 'Listrik Bulan Ini', 'nominal' => 250000, 'created_at' => Carbon::now()->subDays(2)],
            ['deskripsi' => 'Air PAM', 'nominal' => 100000, 'created_at' => Carbon::now()->subDays(4)],
            ['deskripsi' => 'Beli ATK Kantor', 'nominal' => 50000, 'created_at' => Carbon::now()->subDays(1)],
        ];
        foreach ($pengeluaranData as $pe) {
            Pengeluaran::create($pe);
        }
    }
}
