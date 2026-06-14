<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfilingHistory extends Model
{
    protected $fillable = [
        'worker_id',
        'skor_makan',
        'skor_sanitasi',
        'skor_pendapatan',
        'skor_pendidikan',
        'total_skor',
        'kategori_kelayakan',
        'bukti_foto_kondisi',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }
}
