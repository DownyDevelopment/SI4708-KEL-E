<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventaris extends Model
{
    protected $guarded = [];

    public function histories()
    {
        return $this->hasMany(InventarisHistory::class);
    }
}
