<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PergerakanStok extends Model
{
    use HasFactory;

    protected $table = 'pergerakan_stok';
    protected $guarded = ['id'];

    public function gudang()
    {
        return $this->belongsTo(Gudang::class);
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }
}
