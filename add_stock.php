<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Produk;
use App\Models\Inventori;

foreach (Produk::all() as $p) {
    Inventori::firstOrCreate(
        ['produk_id' => $p->id, 'gudang_id' => 1],
        ['stok' => 100]
    );
    Inventori::firstOrCreate(
        ['produk_id' => $p->id, 'gudang_id' => 2],
        ['stok' => 100]
    );
}

echo "Stok berhasil ditambahkan ke gudang 1 dan 2.\n";
