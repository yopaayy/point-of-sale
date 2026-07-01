<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. MASTER DATA
        Schema::create('kategori', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->timestamps();
        });

        Schema::create('merk', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->timestamps();
        });

        Schema::create('satuan', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->string('nama_pendek')->nullable();
            $table->timestamps();
        });

        Schema::create('produk', function (Blueprint $table) {
            $table->id();
            $table->string('kode_produk')->unique()->nullable();
            $table->string('barcode')->unique()->nullable();
            $table->string('nama');
            $table->string('nama_pendek')->nullable();
            $table->foreignId('kategori_id')->nullable()->constrained('kategori')->nullOnDelete();
            $table->foreignId('merk_id')->nullable()->constrained('merk')->nullOnDelete();
            $table->foreignId('base_unit_id')->constrained('satuan')->restrictOnDelete();
            $table->boolean('allow_fraction')->default(false);
            $table->text('deskripsi')->nullable();
            $table->string('gambar')->nullable();
            $table->decimal('minimum_stok', 10, 3)->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('gambar_produk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained('produk')->cascadeOnDelete();
            $table->string('path_gambar');
            $table->boolean('is_utama')->default(false);
            $table->timestamps();
        });

        Schema::create('satuan_produk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained('produk')->cascadeOnDelete();
            $table->foreignId('satuan_id')->constrained('satuan')->restrictOnDelete();
            $table->decimal('konversi', 12, 4)->comment('Pengali dari satuan ini ke base unit');
            $table->boolean('is_base')->default(false);
            $table->timestamps();
        });

        Schema::create('harga_produk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained('produk')->cascadeOnDelete();
            $table->foreignId('satuan_id')->constrained('satuan')->restrictOnDelete();
            $table->decimal('harga_modal', 15, 2)->default(0);
            $table->decimal('harga_jual', 15, 2)->default(0);
            $table->timestamps();
        });

        // 2. ENTITAS
        Schema::create('supplier', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('pic')->nullable();
            $table->string('telepon')->nullable();
            $table->string('email')->nullable();
            $table->text('alamat')->nullable();
            $table->string('npwp')->nullable();
            $table->string('bank')->nullable();
            $table->integer('tempo_pembayaran')->default(0)->comment('Dalam hari');
            $table->timestamps();
        });

        Schema::create('pelanggan', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('telepon')->nullable();
            $table->text('alamat')->nullable();
            $table->timestamps();
        });

        Schema::create('member', function (Blueprint $table) {
            $table->id();
            $table->string('kode_member')->unique();
            $table->string('nama');
            $table->string('telepon')->nullable();
            $table->text('alamat')->nullable();
            $table->integer('poin')->default(0);
            $table->string('level')->nullable();
            $table->date('tanggal_gabung')->nullable();
            $table->timestamps();
        });

        // 3. GUDANG & INFRASTRUKTUR
        Schema::create('cabang', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->text('alamat')->nullable();
            $table->string('telepon')->nullable();
            $table->timestamps();
        });

        Schema::create('gudang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->nullable()->constrained('cabang')->nullOnDelete();
            $table->string('nama');
            $table->string('jenis')->nullable()->comment('Utama, Display, Pendingin');
            $table->timestamps();
        });

        Schema::create('lokasi_rak', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gudang_id')->constrained('gudang')->cascadeOnDelete();
            $table->string('nama');
            $table->timestamps();
        });

        // 4. INVENTORY ENGINE
        Schema::create('inventori', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained('produk')->cascadeOnDelete();
            $table->foreignId('gudang_id')->constrained('gudang')->cascadeOnDelete();
            $table->decimal('stok', 12, 3)->default(0)->comment('Dalam base unit');
            $table->timestamps();
            
            $table->unique(['produk_id', 'gudang_id']);
        });

        Schema::create('pergerakan_stok', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained('produk')->cascadeOnDelete();
            $table->foreignId('gudang_id')->constrained('gudang')->cascadeOnDelete();
            $table->string('tipe')->comment('masuk / keluar');
            $table->decimal('qty', 12, 3)->comment('Dalam base unit');
            $table->decimal('stok_akhir', 12, 3)->comment('Stok setelah pergerakan di gudang ini');
            $table->string('referensi_tipe')->nullable()->comment('pembelian, penjualan, mutasi, retur, opname');
            $table->unsignedBigInteger('referensi_id')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('stok_opname', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gudang_id')->constrained('gudang')->restrictOnDelete();
            $table->foreignId('produk_id')->constrained('produk')->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('stok_sistem', 12, 3);
            $table->decimal('stok_fisik', 12, 3);
            $table->decimal('selisih', 12, 3);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // 5. TRANSAKSI
        Schema::create('pembelian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('supplier')->restrictOnDelete();
            $table->foreignId('gudang_id')->constrained('gudang')->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('no_faktur')->nullable();
            $table->date('tanggal');
            $table->decimal('total_harga', 15, 2)->default(0);
            $table->string('status')->default('selesai')->comment('pending, selesai, batal');
            $table->timestamps();
        });

        Schema::create('detail_pembelian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembelian_id')->constrained('pembelian')->cascadeOnDelete();
            $table->foreignId('produk_id')->constrained('produk')->restrictOnDelete();
            $table->foreignId('satuan_id')->constrained('satuan')->restrictOnDelete();
            $table->decimal('qty', 12, 3);
            $table->decimal('konversi_ke_base', 12, 4);
            $table->decimal('qty_base', 12, 3);
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });

        Schema::create('penjualan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->nullable()->constrained('cabang')->nullOnDelete();
            $table->foreignId('pelanggan_id')->nullable()->constrained('pelanggan')->nullOnDelete();
            $table->foreignId('member_id')->nullable()->constrained('member')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->comment('Kasir');
            $table->string('no_struk')->unique();
            $table->dateTime('tanggal');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('diskon', 15, 2)->default(0);
            $table->decimal('pajak', 15, 2)->default(0);
            $table->decimal('total_akhir', 15, 2)->default(0);
            $table->string('status')->default('selesai');
            $table->timestamps();
        });

        Schema::create('detail_penjualan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penjualan_id')->constrained('penjualan')->cascadeOnDelete();
            $table->foreignId('produk_id')->constrained('produk')->restrictOnDelete();
            $table->foreignId('satuan_id')->constrained('satuan')->restrictOnDelete();
            $table->decimal('qty', 12, 3);
            $table->decimal('konversi_ke_base', 12, 4);
            $table->decimal('qty_base', 12, 3);
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->decimal('diskon', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('retur', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gudang_id')->constrained('gudang')->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tipe')->comment('penjualan / pembelian');
            $table->unsignedBigInteger('referensi_id')->nullable()->comment('ID penjualan atau pembelian');
            $table->date('tanggal');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('detail_retur', function (Blueprint $table) {
            $table->id();
            $table->foreignId('retur_id')->constrained('retur')->cascadeOnDelete();
            $table->foreignId('produk_id')->constrained('produk')->restrictOnDelete();
            $table->foreignId('satuan_id')->constrained('satuan')->restrictOnDelete();
            $table->decimal('qty', 12, 3);
            $table->decimal('qty_base', 12, 3);
            $table->string('alasan')->nullable();
            $table->string('kondisi')->nullable()->comment('rusak, bagus, expired');
            $table->timestamps();
        });

        Schema::create('mutasi_barang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gudang_asal_id')->constrained('gudang')->restrictOnDelete();
            $table->foreignId('gudang_tujuan_id')->constrained('gudang')->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('tanggal');
            $table->string('status')->default('proses')->comment('proses, selesai, batal');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('detail_mutasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mutasi_barang_id')->constrained('mutasi_barang')->cascadeOnDelete();
            $table->foreignId('produk_id')->constrained('produk')->restrictOnDelete();
            $table->decimal('qty_base', 12, 3);
            $table->timestamps();
        });

        // 6. KASIR & KEUANGAN
        Schema::create('shift_kasir', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('cabang_id')->nullable()->constrained('cabang')->nullOnDelete();
            $table->dateTime('waktu_buka');
            $table->dateTime('waktu_tutup')->nullable();
            $table->decimal('modal_awal', 15, 2)->default(0);
            $table->decimal('total_pemasukan', 15, 2)->default(0);
            $table->decimal('total_pengeluaran', 15, 2)->default(0);
            $table->decimal('selisih', 15, 2)->default(0);
            $table->string('status')->default('buka')->comment('buka / tutup');
            $table->timestamps();
        });

        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penjualan_id')->nullable()->constrained('penjualan')->cascadeOnDelete();
            $table->foreignId('shift_kasir_id')->nullable()->constrained('shift_kasir')->nullOnDelete();
            $table->string('metode')->comment('tunai, qris, debit, transfer, dll');
            $table->decimal('nominal', 15, 2);
            $table->string('referensi')->nullable()->comment('no edc, no transaksi qris');
            $table->timestamps();
        });

        Schema::create('promo', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('jenis')->comment('diskon %, nominal, buy X get Y, dll');
            $table->decimal('nilai', 15, 2)->nullable();
            $table->dateTime('tanggal_mulai')->nullable();
            $table->dateTime('tanggal_selesai')->nullable();
            $table->timestamps();
        });

        Schema::create('detail_promo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promo_id')->constrained('promo')->cascadeOnDelete();
            $table->foreignId('produk_id')->constrained('produk')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('pengeluaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->nullable()->constrained('cabang')->nullOnDelete();
            $table->foreignId('shift_kasir_id')->nullable()->constrained('shift_kasir')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('tanggal');
            $table->string('keterangan');
            $table->decimal('nominal', 15, 2);
            $table->timestamps();
        });

        Schema::create('pajak', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->decimal('persentase', 5, 2);
            $table->timestamps();
        });

        // 7. PENGATURAN & LOG
        Schema::create('log_aktivitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('aksi');
            $table->string('entitas')->nullable();
            $table->unsignedBigInteger('entitas_id')->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });

        Schema::create('pengaturan', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pengaturan');
        Schema::dropIfExists('log_aktivitas');
        Schema::dropIfExists('pajak');
        Schema::dropIfExists('pengeluaran');
        Schema::dropIfExists('detail_promo');
        Schema::dropIfExists('promo');
        Schema::dropIfExists('pembayaran');
        Schema::dropIfExists('shift_kasir');
        Schema::dropIfExists('detail_mutasi');
        Schema::dropIfExists('mutasi_barang');
        Schema::dropIfExists('detail_retur');
        Schema::dropIfExists('retur');
        Schema::dropIfExists('detail_penjualan');
        Schema::dropIfExists('penjualan');
        Schema::dropIfExists('detail_pembelian');
        Schema::dropIfExists('pembelian');
        Schema::dropIfExists('stok_opname');
        Schema::dropIfExists('pergerakan_stok');
        Schema::dropIfExists('inventori');
        Schema::dropIfExists('lokasi_rak');
        Schema::dropIfExists('gudang');
        Schema::dropIfExists('cabang');
        Schema::dropIfExists('member');
        Schema::dropIfExists('pelanggan');
        Schema::dropIfExists('supplier');
        Schema::dropIfExists('harga_produk');
        Schema::dropIfExists('satuan_produk');
        Schema::dropIfExists('gambar_produk');
        Schema::dropIfExists('produk');
        Schema::dropIfExists('satuan');
        Schema::dropIfExists('merk');
        Schema::dropIfExists('kategori');
    }
};
