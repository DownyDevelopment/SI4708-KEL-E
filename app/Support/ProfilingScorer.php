<?php

namespace App\Support;

use App\Models\ProfilingHistory;
use App\Models\Worker;

/**
 * Skor vulnerabilitas berbasis indikator Kemensos/BPS (P3KE & garis kemiskinan).
 * Skor lebih tinggi = kondisi lebih rentan = prioritas program lebih tinggi.
 */
class ProfilingScorer
{
    public const THRESHOLD_TIDAK_LAYAK = 6;
    public const THRESHOLD_RENDAH = 10;
    public const THRESHOLD_TINGGI = 14;

    public static function score(Worker $worker): int
    {
        $worker->loadMissing('household');
        $total = 0;

        $total += self::scorePendapatan($worker);
        $total += self::scoreFrekuensiMakan($worker->frekuensi_makan);
        $total += self::scoreSanitasi($worker->kondisi_sanitasi);
        $total += self::scorePendidikan($worker->pendidikan_terakhir);
        $total += self::scoreAirBersih($worker->akses_air_bersih);
        $total += self::scoreStatusRumah($worker->status_rumah);
        $total += self::scoreGizi($worker->status_gizi);
        $total += self::scoreKesehatan($worker->riwayat_penyakit);

        return min(30, max(0, $total));
    }

    public static function prioritas(int $skor): string
    {
        if ($skor < self::THRESHOLD_TIDAK_LAYAK) {
            return 'tidak_layak';
        }
        if ($skor >= self::THRESHOLD_TINGGI) {
            return 'tinggi';
        }
        if ($skor >= self::THRESHOLD_RENDAH) {
            return 'sedang';
        }

        return 'rendah';
    }

    public static function prioritasLabel(string $prioritas): string
    {
        return match ($prioritas) {
            'tinggi' => 'Prioritas Tinggi',
            'sedang' => 'Prioritas Sedang',
            'rendah' => 'Prioritas Rendah',
            'tidak_layak' => 'Tidak Layak Program',
            default => $prioritas,
        };
    }

    public static function layakProgram(Worker $worker): bool
    {
        return $worker->prioritas !== 'tidak_layak' && $worker->status_program !== 'tidak_layak';
    }

    public static function snapshotPayload(Worker $worker): array
    {
        $worker->loadMissing('household');

        return [
            'frekuensi_makan' => $worker->frekuensi_makan,
            'kondisi_sanitasi' => $worker->kondisi_sanitasi,
            'pendidikan_terakhir' => $worker->pendidikan_terakhir,
            'status_gizi' => $worker->status_gizi,
            'pendapatan_per_kapita' => $worker->household
                ? (int) round($worker->household->pendapatan_per_kapita)
                : null,
            'skor_vulnerabilitas' => $worker->skor_vulnerabilitas,
            'prioritas' => $worker->prioritas,
        ];
    }

    public static function scoreDimensionMakan(?string $value): int
    {
        return match ($value) {
            '1 kali' => 3,
            '2 kali' => 2,
            '3 kali atau lebih' => 1,
            default => 2,
        };
    }

    public static function scoreDimensionSanitasi(?string $value): int
    {
        return match ($value) {
            'Tidak Ada Jamban' => 3,
            'Jamban Bersama' => 2,
            'Jamban Sendiri' => 1,
            'Jamban Sendiri + Septic Tank' => 0,
            default => 1,
        };
    }

    public static function scoreDimensionPendidikan(?string $value): int
    {
        return match ($value) {
            'Tidak Sekolah' => 3,
            'SD / Sederajat' => 2,
            'SMP / Sederajat' => 2,
            'SMA / Sederajat' => 1,
            'Diploma / S1+' => 0,
            default => 1,
        };
    }

    public static function scoreDimensionPendapatan(Worker $worker): int
    {
        $perKapita = $worker->household?->pendapatan_per_kapita ?? 0;

        if ($perKapita <= 0) {
            return 3;
        }
        if ($perKapita < 300_000) {
            return 3;
        }
        if ($perKapita < 500_000) {
            return 2;
        }
        if ($perKapita < 800_000) {
            return 1;
        }

        return 0;
    }

    /** @return array{skor_makan: int, skor_sanitasi: int, skor_pendapatan: int, skor_pendidikan: int, total_skor: int, kategori_kelayakan: string} */
    public static function computeDimensionScores(Worker $worker): array
    {
        $worker->loadMissing('household');

        $skorMakan = self::scoreDimensionMakan($worker->frekuensi_makan);
        $skorSanitasi = self::scoreDimensionSanitasi($worker->kondisi_sanitasi);
        $skorPendidikan = self::scoreDimensionPendidikan($worker->pendidikan_terakhir);
        $skorPendapatan = self::scoreDimensionPendapatan($worker);
        $total = $skorMakan + $skorSanitasi + $skorPendidikan + $skorPendapatan;

        return [
            'skor_makan' => $skorMakan,
            'skor_sanitasi' => $skorSanitasi,
            'skor_pendapatan' => $skorPendapatan,
            'skor_pendidikan' => $skorPendidikan,
            'total_skor' => $total,
            'kategori_kelayakan' => self::kategoriKelayakan($total),
        ];
    }

    public static function kategoriKelayakan(int $totalSkor): string
    {
        if ($totalSkor > 10) {
            return 'Sangat Miskin';
        }
        if ($totalSkor >= 7) {
            return 'Rentan Miskin';
        }

        return 'Tidak Layak';
    }

    public static function statusKesejahteraan(Worker $worker, ?string $kategori = null): string
    {
        if ($worker->status_program === 'lulus') {
            return 'Lulus/Tidak Layak';
        }

        $kategori ??= self::kategoriKelayakan($worker->total_skor ?? 0);

        return match ($kategori) {
            'Sangat Miskin' => 'Sangat Miskin',
            'Rentan Miskin' => 'Rentan Miskin',
            'Tidak Layak' => $worker->status_program === 'tidak_layak'
                ? 'Lulus/Tidak Layak'
                : 'Pending',
            default => 'Pending',
        };
    }

    public static function applyToWorker(Worker $worker, bool $saveInitial = false): Worker
    {
        $skor = self::score($worker);
        $worker->skor_vulnerabilitas = $skor;
        $worker->prioritas = self::prioritas($skor);

        $dimensions = self::computeDimensionScores($worker);
        $worker->total_skor = $dimensions['total_skor'];
        $worker->keahlian_kerja = $worker->kemampuan_utama;
        $worker->status_kesejahteraan = match ($dimensions['kategori_kelayakan']) {
            'Sangat Miskin' => 'Sangat Miskin',
            'Rentan Miskin' => 'Rentan Miskin',
            default => $worker->status_program === 'tidak_layak' ? 'Lulus/Tidak Layak' : 'Pending',
        };

        if ($saveInitial && !$worker->profiling_awal) {
            $worker->profiling_awal = self::snapshotPayload($worker);
        }

        return $worker;
    }

    public static function createHistory(Worker $worker, ?string $buktiFoto = null): ProfilingHistory
    {
        $dimensions = self::computeDimensionScores($worker);

        return ProfilingHistory::create([
            'worker_id' => $worker->id,
            'skor_makan' => $dimensions['skor_makan'],
            'skor_sanitasi' => $dimensions['skor_sanitasi'],
            'skor_pendapatan' => $dimensions['skor_pendapatan'],
            'skor_pendidikan' => $dimensions['skor_pendidikan'],
            'total_skor' => $dimensions['total_skor'],
            'kategori_kelayakan' => $dimensions['kategori_kelayakan'],
            'bukti_foto_kondisi' => $buktiFoto,
        ]);
    }

    /** Cocokkan pekerja dengan program berdasarkan keahlian & prioritas. */
    public static function matchScore(Worker $worker, string $jenisProgram, ?string $sektorKeahlian = null): int
    {
        if (!self::layakProgram($worker)) {
            return 0;
        }

        $kemampuan = strtolower($worker->kemampuan_utama ?? '');
        $jenis = strtolower($jenisProgram);
        $sektor = strtolower($sektorKeahlian ?? $jenis);
        $score = 1;

        $keywords = [
            'pertanian' => ['tani', 'kebun', 'sayur', 'panen', 'pertanian'],
            'lingkungan' => ['sampah', 'kompos', 'bersih', 'lingkungan', 'bank sampah'],
            'kerajinan' => ['kerajinan', 'rajut', 'anyam', 'craft', 'jual'],
            'perikanan' => ['nelayan', 'ikan', 'perikanan', ' tambak'],
            'perdagangan' => ['jual', 'dagang', 'warung', 'jualan'],
        ];

        foreach ($keywords as $sector => $terms) {
            if (str_contains($sektor, $sector) || str_contains($jenis, $sector)) {
                foreach ($terms as $term) {
                    if (str_contains($kemampuan, trim($term))) {
                        $score += 10;
                        break 2;
                    }
                }
            }
        }

        $score += match ($worker->prioritas) {
            'tinggi' => 5,
            'sedang' => 3,
            'rendah' => 1,
            default => 0,
        };

        return $score;
    }

    private static function scorePendapatan(Worker $worker): int
    {
        $perKapita = $worker->household?->pendapatan_per_kapita ?? 0;

        if ($perKapita <= 0) {
            return 4;
        }
        if ($perKapita < 300_000) {
            return 5;
        }
        if ($perKapita < 500_000) {
            return 4;
        }
        if ($perKapita < 800_000) {
            return 2;
        }

        return 0;
    }

    private static function scoreFrekuensiMakan(?string $value): int
    {
        return match ($value) {
            '1 kali' => 5,
            '2 kali' => 3,
            '3 kali atau lebih' => 1,
            default => 3,
        };
    }

    private static function scoreSanitasi(?string $value): int
    {
        return match ($value) {
            'Tidak Ada Jamban' => 5,
            'Jamban Bersama' => 3,
            'Jamban Sendiri' => 2,
            'Jamban Sendiri + Septic Tank' => 0,
            default => 2,
        };
    }

    private static function scorePendidikan(?string $value): int
    {
        return match ($value) {
            'Tidak Sekolah' => 4,
            'SD / Sederajat' => 3,
            'SMP / Sederajat' => 2,
            'SMA / Sederajat' => 1,
            'Diploma / S1+' => 0,
            default => 2,
        };
    }

    private static function scoreAirBersih(?string $value): int
    {
        return match ($value) {
            'Tidak Ada' => 4,
            'Sumur / Mata Air' => 2,
            'PAM / PDAM' => 0,
            'Air Kemasan' => 1,
            default => 2,
        };
    }

    private static function scoreStatusRumah(?string $value): int
    {
        return match ($value) {
            'Tidak Ada (Gelandangan/Numpang)' => 4,
            'Kontrak / Sewa' => 2,
            'Milik Sendiri' => 0,
            default => 1,
        };
    }

    private static function scoreGizi(?string $value): int
    {
        return match ($value) {
            'Buruk' => 5,
            'Kurang' => 3,
            'Normal' => 0,
            default => 2,
        };
    }

    private static function scoreKesehatan(?string $value): int
    {
        if (!$value || trim($value) === '' || strtolower(trim($value)) === 'tidak ada') {
            return 0;
        }

        return 2;
    }
}
