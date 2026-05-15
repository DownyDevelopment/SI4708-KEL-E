<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Logbook extends Model
{
    protected $fillable = [
        'schedule_id', 'pengawas_id', 'progres_persentase', 
        'catatan', 'foto_bukti_url', 'lokasi_pekerjaan', 'pekerja_terlibat'
    ];
}
