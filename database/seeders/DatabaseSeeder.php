<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kategori;
use App\Models\Merk;
use App\Models\Satuan;
use App\Models\Produk;
use App\Models\SatuanProduk;
use App\Models\HargaProduk;
use App\Models\Cabang;
use App\Models\Gudang;
use App\Models\Supplier;
use App\Models\Pelanggan;
use App\Models\Promo;
use App\Models\Member;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $this->call([
            SettingTableSeeder::class,
            UserTableSeeder::class,
            MasterDataSeeder::class,
        ]);
        // Seed Cabang & Gudang
        $cabang = Cabang::create([
            'nama' => 'Cabang Utama Jakarta',
            'alamat' => 'Jl. Sudirman No. 1, Jakarta',
            'telepon' => '021-12345678'
        ]);

        Gudang::create([
            'cabang_id' => $cabang->id,
            'nama' => 'Gudang Utama',
            'jenis' => 'Utama'
        ]);

        Gudang::create([
            'cabang_id' => $cabang->id,
            'nama' => 'Gudang Display (Toko)',
            'jenis' => 'Display'
        ]);

        // Seed Supplier
        Supplier::create([
            'nama' => 'PT. Indofood Sukses Makmur',
            'pic' => 'Budi Santoso',
            'telepon' => '081234567890',
            'alamat' => 'Kawasan Industri Cikarang',
            'tempo_pembayaran' => 14
        ]);

        Supplier::create([
            'nama' => 'PT. Tirta Investama (Danone)',
            'pic' => 'Siti Aminah',
            'telepon' => '081987654321',
            'alamat' => 'Kawasan Industri Pulogadung',
            'tempo_pembayaran' => 7
        ]);

        // Seed Pelanggan (Bukan Member)
        Pelanggan::create([
            'nama' => 'Pelanggan Umum',
            'telepon' => '-',
            'alamat' => '-'
        ]);

        // Seed Member
        Member::create([
            'kode_member' => 'MBR-0001',
            'nama' => 'Bapak Budi',
            'telepon' => '08111222333',
            'alamat' => 'Jl. Merdeka No.45',
            'poin' => 50,
            'level' => 'Gold',
            'tanggal_gabung' => now()
        ]);

        // Seed Promo
        Promo::create([
            'nama' => 'Diskon 10% All Item',
            'jenis' => 'persentase',
            'nilai' => 10, // 10%
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDays(30)
        ]);

        Promo::create([
            'nama' => 'Potongan 20 Ribu',
            'jenis' => 'nominal',
            'nilai' => 20000,
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDays(15)
        ]);
    }
}
