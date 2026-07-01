<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LokasiRak extends Model
{
    use HasFactory;

    protected $table = 'lokasi_rak';
    protected $guarded = ['id'];
}
