<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Insentif extends Model
{
    use HasFactory;

    protected $fillable = [
        'worker_id',
        'tanggal',
        'jumlah_upah',
        'jenis_insentif',
        'keterangan'
    ];
}
