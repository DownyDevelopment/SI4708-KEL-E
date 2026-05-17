<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkSchedule extends Model
{
    protected $fillable = [
        'program_id',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'shift_label',
        'status',
        'deskripsi',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(MicroProgram::class, 'program_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ScheduleAssignment::class, 'schedule_id');
    }

    public function logbooks(): HasMany
    {
        return $this->hasMany(Logbook::class, 'schedule_id');
    }
}
