<?php

namespace App\Support;

use App\Models\Worker;

class BidangKerjaHelper
{
    public const STANDAR = [
        'Pertanian',
        'Pengelolaan Sampah',
        'Kerajinan Tangan',
        'Pertukangan',
    ];

    public static function normalize(?string $bidang): string
    {
        $bidang = trim((string) $bidang);
        if ($bidang === '') {
            return 'Lainnya';
        }

        $lower = strtolower($bidang);

        if (self::matches($lower, ['pertanian', 'bertani', 'tani', 'kebun', 'petani', 'nelayan', 'ternak'])) {
            return 'Pertanian';
        }
        if (self::matches($lower, ['sampah', 'pembersih', 'membersihkan', 'kompos', 'lingkungan', 'pengelolaan sampah'])) {
            return 'Pengelolaan Sampah';
        }
        if (self::matches($lower, ['kerajinan', 'rajut', 'pengrajin', 'kerajinan tangan'])) {
            return 'Kerajinan Tangan';
        }
        if (self::matches($lower, ['pertukangan', 'tukang', 'bangunan', 'konstruksi', 'las'])) {
            return 'Pertukangan';
        }

        if (in_array($bidang, self::STANDAR, true)) {
            return $bidang;
        }

        return 'Lainnya';
    }

    public static function chartData(): array
    {
        $workers = Worker::query()->select('kemampuan_utama')->get();
        $grouped = [];
        $breakdown = [];

        foreach ($workers as $worker) {
            $raw = trim((string) ($worker->kemampuan_utama ?? ''));
            $category = self::normalize($raw);

            $grouped[$category] = ($grouped[$category] ?? 0) + 1;

            if ($category === 'Lainnya' && $raw !== '') {
                $breakdown[$raw] = ($breakdown[$raw] ?? 0) + 1;
            }
        }

        $labels = array_merge(self::STANDAR, ['Lainnya']);
        $values = array_map(fn ($label) => $grouped[$label] ?? 0, $labels);

        return [
            'labels' => $labels,
            'values' => $values,
            'breakdown' => $breakdown,
        ];
    }

    private static function matches(string $haystack, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (str_contains($haystack, $keyword)) {
                return true;
            }
        }

        return false;
    }
}
