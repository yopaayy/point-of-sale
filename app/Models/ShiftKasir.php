<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftKasir extends Model
{
    use HasFactory;

    protected $table = 'shift_kasir';
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
