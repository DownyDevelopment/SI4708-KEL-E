<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleAssignment extends Model
{
    protected $fillable = [
        'schedule_id',
        'worker_id',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(WorkSchedule::class, 'schedule_id');
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }
}
