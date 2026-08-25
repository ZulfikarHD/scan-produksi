<?php

namespace App\Helpers;

use Carbon\Carbon;
use Carbon\CarbonInterface;

class ShiftHelper
{
    /**
     * Get current shift details based on Jakarta time.
     *
     * @return array{number: int, name: string, time_range: string, badge_color: string}
     */
    public static function getCurrentShift(?CarbonInterface $time = null): array
    {
        $now = $time ? Carbon::instance($time)->setTimezone('Asia/Jakarta') : Carbon::now('Asia/Jakarta');
        $hour = (int) $now->format('H');

        if ($hour >= 7 && $hour < 15) {
            return [
                'number' => 1,
                'name' => 'Shift 1 (Pagi)',
                'time_range' => '07:00 - 15:00 WIB',
                'badge_color' => 'sky',
            ];
        }

        if ($hour >= 15 && $hour < 23) {
            return [
                'number' => 2,
                'name' => 'Shift 2 (Siang)',
                'time_range' => '15:00 - 23:00 WIB',
                'badge_color' => 'amber',
            ];
        }

        return [
            'number' => 3,
            'name' => 'Shift 3 (Malam)',
            'time_range' => '23:00 - 07:00 WIB',
            'badge_color' => 'purple',
        ];
    }

    /**
     * Format current date and time in Indonesian.
     */
    public static function getFormattedDateTime(?CarbonInterface $time = null): string
    {
        $now = $time ? Carbon::instance($time)->setTimezone('Asia/Jakarta') : Carbon::now('Asia/Jakarta');

        $dayNames = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ];

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $day = $dayNames[$now->format('l')] ?? $now->format('l');
        $date = $now->format('d');
        $month = $monthNames[(int) $now->format('m')];
        $year = $now->format('Y');
        $timeStr = $now->format('H:i');

        return "{$day}, {$date} {$month} {$year} • {$timeStr} WIB";
    }
}
