<?php

namespace App\Services;

use App\Enums\ProductSeries;
use App\Enums\WorkShift;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use DateTimeInterface;

class ReportCodeGenerator
{
    /**
     * Generate a standardized daily production report code.
     *
     * Example outputs:
     * - `PCHT-2026-08-26` (base code)
     * - `PCHT-2026-08-26-S1` (shift-specific code)
     */
    public static function generate(
        ProductSeries|string $series,
        CarbonInterface|DateTimeInterface|string $date,
        WorkShift|string|int|null $shift = null,
        bool $includeShift = false
    ): string {
        $seriesCode = self::resolveSeriesCode($series);
        $dateStr = self::resolveDateString($date);

        $code = "{$seriesCode}-{$dateStr}";

        if ($includeShift && $shift !== null) {
            $shiftSuffix = self::resolveShiftSuffix($shift);
            $code .= "-{$shiftSuffix}";
        }

        return $code;
    }

    /**
     * Generate shift-specific report code (e.g. PCHT-2026-08-26-S1).
     */
    public static function generateWithShift(
        ProductSeries|string $series,
        CarbonInterface|DateTimeInterface|string $date,
        WorkShift|string|int $shift
    ): string {
        return self::generate($series, $date, $shift, includeShift: true);
    }

    /**
     * Resolve the series uppercase code (PCHT, MMEA, HPTL).
     */
    protected static function resolveSeriesCode(ProductSeries|string $series): string
    {
        if ($series instanceof ProductSeries) {
            return $series->code();
        }

        $seriesStr = strtolower(trim($series));

        return match ($seriesStr) {
            'pcht', 'pita cukai hasil tembakau' => 'PCHT',
            'mmea', 'minuman mengandung etil alkohol' => 'MMEA',
            'hptl', 'hasil pengolahan tembakau lainnya', 'rel' => 'HPTL',
            default => strtoupper($seriesStr),
        };
    }

    /**
     * Resolve date to YYYY-MM-DD string.
     */
    protected static function resolveDateString(CarbonInterface|DateTimeInterface|string $date): string
    {
        if ($date instanceof CarbonInterface || $date instanceof DateTimeInterface) {
            return $date->format('Y-m-d');
        }

        return Carbon::parse($date)->format('Y-m-d');
    }

    /**
     * Resolve shift suffix (e.g. S1, S2, S3).
     */
    protected static function resolveShiftSuffix(WorkShift|string|int $shift): string
    {
        if ($shift instanceof WorkShift) {
            return 'S'.$shift->number();
        }

        if (is_int($shift) || is_numeric($shift)) {
            return 'S'.((int) $shift);
        }

        $shiftStr = strtolower(trim($shift));

        return match ($shiftStr) {
            'shift_1', 'shift 1', '1', 's1' => 'S1',
            'shift_2', 'shift 2', '2', 's2' => 'S2',
            'shift_3', 'shift 3', '3', 's3' => 'S3',
            default => strtoupper($shiftStr),
        };
    }
}
