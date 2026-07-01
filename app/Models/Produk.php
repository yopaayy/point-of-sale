<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;

    protected $table = 'produk';
    protected $guarded = ['id'];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    public function merk()
    {
        return $this->belongsTo(Merk::class);
    }

    public function baseUnit()
    {
        return $this->belongsTo(Satuan::class, 'base_unit_id');
    }

    public function satuanProduk()
    {
        return $this->hasMany(SatuanProduk::class);
    }

    public function hargaProduk()
    {
        return $this->hasMany(HargaProduk::class);
    }
}
