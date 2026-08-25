<?php

namespace App\Enums;

enum WorkShift: string
{
    case SHIFT_1 = 'shift_1';
    case SHIFT_2 = 'shift_2';
    case SHIFT_3 = 'shift_3';

    /**
     * Get shift number integer (1, 2, 3).
     */
    public function number(): int
    {
        return match ($this) {
            self::SHIFT_1 => 1,
            self::SHIFT_2 => 2,
            self::SHIFT_3 => 3,
        };
    }

    /**
     * Get human-readable Indonesian label.
     */
    public function label(): string
    {
        return match ($this) {
            self::SHIFT_1 => 'Shift 1 (Pagi)',
            self::SHIFT_2 => 'Shift 2 (Siang)',
            self::SHIFT_3 => 'Shift 3 (Malam)',
        };
    }

    /**
     * Get operational time range in WIB.
     */
    public function timeRange(): string
    {
        return match ($this) {
            self::SHIFT_1 => '07:00 - 15:00 WIB',
            self::SHIFT_2 => '15:00 - 23:00 WIB',
            self::SHIFT_3 => '23:00 - 07:00 WIB',
        };
    }

    /**
     * Get Flux UI color badge.
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::SHIFT_1 => 'sky',
            self::SHIFT_2 => 'amber',
            self::SHIFT_3 => 'purple',
        };
    }

    /**
     * Alias for badgeColor.
     */
    public function color(): string
    {
        return $this->badgeColor();
    }
}
