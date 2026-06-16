<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Logbook extends Model
{
    protected $fillable = [
        'schedule_id',
        'worker_group_id',
        'worker_id',
        'pengawas_id',
        'tanggal',
        'catatan_progres',
        'catatan',
        'progres_persentase',
        'status_validasi',
        'foto_bukti',
        'foto_bukti_url',
        'foto_sebelum',
        'foto_sesudah',
        'lokasi_pekerjaan',
        'pekerja_terlibat',
        'detail_monitoring',
        'rating_kinerja',
        'catatan_evaluasi',
        'evaluated_by',
        'evaluated_at',
    ];

    protected $casts = [
        'detail_monitoring' => 'array',
        'evaluated_at' => 'datetime',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(WorkSchedule::class, 'schedule_id');
    }

    public function workerGroup(): BelongsTo
    {
        return $this->belongsTo(WorkerGroup::class, 'worker_group_id');
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function pengawas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengawas_id');
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }
}
