<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Insentif extends Model
{
    use HasFactory;
    protected $fillable = [
        'worker_id',
        'logbook_id',
        'tanggal',
        'jumlah_upah',
        'jenis_insentif',
        'keterangan',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function logbook(): BelongsTo
    {
        return $this->belongsTo(Logbook::class);
    }
}
