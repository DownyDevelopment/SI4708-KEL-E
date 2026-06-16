<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkerGroup extends Model
{
    protected $fillable = [
        'nama_kelompok',
        'deskripsi',
    ];

    public function workers(): BelongsToMany
    {
        return $this->belongsToMany(Worker::class, 'worker_group_worker', 'worker_group_id', 'worker_id')
            ->withTimestamps();
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(WorkSchedule::class, 'worker_group_id');
    }

    public function logbooks(): HasMany
    {
        return $this->hasMany(Logbook::class, 'worker_group_id');
    }
}
