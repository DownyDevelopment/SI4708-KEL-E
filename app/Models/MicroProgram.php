<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MicroProgram extends Model
{
    protected $fillable = [
        'nama_program',
        'jenis_program',
        'sektor_keahlian',
        'deskripsi',
        'lokasi',
        'desa_lokasi',
        'kordinat',
        'stakeholders',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
    ];
}
