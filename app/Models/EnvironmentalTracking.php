<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnvironmentalTracking extends Model
{
    protected $fillable = [
        'tanggal',
        'jenis_limbah',
        'volume_kg',
        'estimasi_emisi_berkurang_kg',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'volume_kg' => 'decimal:2',
        'estimasi_emisi_berkurang_kg' => 'decimal:2',
    ];
}
