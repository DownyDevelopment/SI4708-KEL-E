<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Insentif extends Model
{
<<<<<<< HEAD
    use HasFactory;

=======
>>>>>>> 39ff43e1bf25f74d1c786ba53a16904cbe7ed34b
    protected $fillable = [
        'worker_id',
        'tanggal',
        'jumlah_upah',
        'jenis_insentif',
<<<<<<< HEAD
        'keterangan'
    ];
=======
        'keterangan',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }
>>>>>>> 39ff43e1bf25f74d1c786ba53a16904cbe7ed34b
}
