<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reward extends Model
{
    use HasFactory;
    protected $fillable = [
        'worker_id',
        'nama_penghargaan',
        'tanggal_pemberian',
    ];
    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }
}
