<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'pembayaran';
    protected $guarded = ['id'];

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'penjualan_id');
    }

    public function shiftKasir()
    {
        return $this->belongsTo(ShiftKasir::class, 'shift_id');
    }
}
