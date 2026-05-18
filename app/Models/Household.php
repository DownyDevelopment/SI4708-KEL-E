<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Household extends Model
{
    use HasFactory;

    protected $fillable = [
        'kepala_keluarga',
        'alamat',
        'rt_rw',
        'jumlah_anggota',
        'pendapatan_per_bulan',
    ];

    public function workers(): HasMany
    {
        return $this->hasMany(Worker::class);
    }

    public function getPendapatanPerKapitaAttribute(): float
    {
        if ($this->jumlah_anggota <= 0) {
            return 0;
        }

        return $this->pendapatan_per_bulan / $this->jumlah_anggota;
    }
}
