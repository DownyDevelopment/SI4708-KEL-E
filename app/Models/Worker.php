<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'kontak_darurat',
        'status_keluarga',
        'status_rumah',
        'riwayat_penyakit',
        'kemampuan_utama',
        'household_id',
    ];

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function insentifs(): HasMany
    {
        return $this->hasMany(Insentif::class);
    }

    /**
     * Get the worker's total income (Household per capita + sum of all insentifs).
     */
    public function getTotalPendapatanAttribute(): float
    {
        $pendapatanKeluarga = 0;
        if ($this->household && $this->household->jumlah_anggota > 0) {
            $pendapatanKeluarga = $this->household->pendapatan_per_bulan / $this->household->jumlah_anggota;
        }

        $pendapatanInsentif = $this->insentifs_sum_jumlah_upah ?? 0;

        return $pendapatanKeluarga + $pendapatanInsentif;
    }

    /**
     * Get the worker's poverty status.
     * Assuming poverty line is Rp 500,000 per capita.
     */
    public function getIsMiskinAttribute(): bool
    {
        $total = $this->total_pendapatan;
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
        $total = $this->total_pendapatan;

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
