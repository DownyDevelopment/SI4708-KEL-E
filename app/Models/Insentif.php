<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Insentif extends Model
{
    protected $fillable = [
        'worker_id',
        'tanggal',
        'jumlah_upah',
        'jenis_insentif',
        'keterangan',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }
}
