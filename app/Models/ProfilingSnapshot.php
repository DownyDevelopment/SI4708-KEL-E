<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfilingSnapshot extends Model
{
    protected $fillable = [
        'worker_id',
        'recorded_by',
        'skor_vulnerabilitas',
        'frekuensi_makan',
        'kondisi_sanitasi',
        'pendidikan_terakhir',
        'pendapatan_per_kapita',
        'status_gizi',
        'catatan',
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'date',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
