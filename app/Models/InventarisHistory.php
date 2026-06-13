<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventarisHistory extends Model
{
    protected $fillable = [
        'inventaris_id',
        'jumlah_perubahan',
        'tipe_perubahan',
        'keterangan',
        'household_id',
    ];

    public function inventaris()
    {
        return $this->belongsTo(Inventaris::class);
    }

    public function household()
    {
        return $this->belongsTo(Household::class);
    }
}
