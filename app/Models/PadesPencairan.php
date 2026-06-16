<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PadesPencairan extends Model
{
    protected $table = 'pades_pencairans';

    protected $fillable = [
        'nominal',
        'tanggal_pencairan',
        'keterangan',
        'bukti_foto',
    ];

    protected $casts = [
        'tanggal_pencairan' => 'date',
        'nominal' => 'integer',
    ];
}
