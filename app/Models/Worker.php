<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Worker extends Model
{
    use HasFactory;
    protected $fillable = [
        'nama',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat',
        'no_telepon',
        'nama_bank',
        'nomor_rekening',
        'total_pendapatan',
        'kontak_darurat',
        'status_keluarga',
        'status_rumah',
        'riwayat_penyakit',
        'kemampuan_utama',
        'keahlian_kerja',
        'pendidikan_terakhir',
        'frekuensi_makan',
        'kondisi_sanitasi',
        'akses_air_bersih',
        'status_gizi',
        'kebiasaan',
        'skor_vulnerabilitas',
        'total_skor',
        'prioritas',
        'status_kesejahteraan',
        'status_program',
        'profiling_awal',
        'household_id',
    ];

    protected $casts = [
        'profiling_awal' => 'array',
    ];

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    protected function usia(): Attribute
    {
        return Attribute::get(function (): ?int {
            if (!$this->tanggal_lahir) {
                return null;
            }

            return Carbon::parse($this->tanggal_lahir)->age;
        });
    }

    public function insentifs(): HasMany
    {
        return $this->hasMany(Insentif::class);
    }

    public function schedules(): BelongsToMany
    {
        return $this->belongsToMany(WorkSchedule::class, 'schedule_assignments', 'worker_id', 'schedule_id');
    }

    public function workerGroups(): BelongsToMany
    {
        return $this->belongsToMany(WorkerGroup::class, 'worker_group_worker', 'worker_id', 'worker_group_id')
            ->withTimestamps();
    }

    public function profilingSnapshots(): HasMany
    {
        return $this->hasMany(ProfilingSnapshot::class);
    }

    public function profilingHistories(): HasMany
    {
        return $this->hasMany(ProfilingHistory::class);
    }

    public function getDesaAsalAttribute(): ?string
    {
        return $this->household?->nama_desa;
    }

    public function getPrioritasLabelAttribute(): string
    {
        return \App\Support\ProfilingScorer::prioritasLabel($this->prioritas ?? 'sedang');
    }

    public function getStatusProgramLabelAttribute(): string
    {
        return match ($this->status_program) {
            'lulus' => 'Lulus Program',
            'tidak_layak' => 'Tidak Layak',
            default => 'Aktif',
        };
    }

    /**
     * Get the worker's poverty status.
     * Assuming poverty line is Rp 500,000 per capita.
     */
    public function getIsMiskinAttribute(): bool
    {
        $total = (float) ($this->attributes['total_pendapatan'] ?? 0);
        if ($total == 0) {
            return false; // Or true, but maybe no data means we don't classify as miskin automatically
        }
        return $total < 500000;
    }

    /**
     * Get the worker's welfare classification.
     */
    public function getKlasifikasiKesejahteraanAttribute(): string
    {
        $total = (float) ($this->attributes['total_pendapatan'] ?? 0);

        if ($total == 0 && !$this->household && ($this->insentifs_count ?? 0) == 0) {
            return 'Tidak Diketahui';
        }

        if ($total < 300000) {
            return 'Sangat Miskin';
        } elseif ($total < 500000) {
            return 'Miskin';
        } elseif ($total < 800000) {
            return 'Rentan Miskin';
        } else {
            return 'Sejahtera';
        }
    }

    /**
     * Map kemampuan_utama to macro job sector.
     */
    public function getPekerjaanMakroAttribute(): string
    {
        $kemampuan = strtolower($this->kemampuan_utama ?? '');

        if (str_contains($kemampuan, 'tani') || str_contains($kemampuan, 'kebun') || str_contains($kemampuan, 'ternak') || str_contains($kemampuan, 'nelayan')) {
            return 'Pertanian & Perikanan';
        } elseif (str_contains($kemampuan, 'tukang') || str_contains($kemampuan, 'bangunan') || str_contains($kemampuan, 'konstruksi') || str_contains($kemampuan, 'las')) {
            return 'Konstruksi & Infrastruktur';
        } elseif (str_contains($kemampuan, 'jual') || str_contains($kemampuan, 'dagang') || str_contains($kemampuan, 'warung')) {
            return 'Perdagangan';
        } elseif (str_contains($kemampuan, 'guru') || str_contains($kemampuan, 'ajar') || str_contains($kemampuan, 'didik')) {
            return 'Pendidikan';
        } elseif (str_contains($kemampuan, 'sehat') || str_contains($kemampuan, 'medis') || str_contains($kemampuan, 'perawat')) {
            return 'Kesehatan';
        } elseif (str_contains($kemampuan, 'jasa') || str_contains($kemampuan, 'supir') || str_contains($kemampuan, 'layanan') || str_contains($kemampuan, 'bersih')) {
            return 'Jasa';
        }

        return 'Lainnya';
    }
}
