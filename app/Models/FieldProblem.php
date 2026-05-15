<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FieldProblem extends Model
{
    protected $fillable = [
        'pengawas_id', 'tanggal', 'waktu', 'masalah', 
        'tingkatan_masalah', 'lokasi_masalah', 'kordinat'
    ];
}
