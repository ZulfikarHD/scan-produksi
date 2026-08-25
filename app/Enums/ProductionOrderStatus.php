<?php

namespace App\Enums;

enum ProductionOrderStatus: string
{
    case READY = 'ready';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    /**
     * Get human-readable Indonesian label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::READY => 'Siap Kemas',
            self::IN_PROGRESS => 'Sedang Diproses',
            self::COMPLETED => 'Selesai',
            self::CANCELLED => 'Dibatalkan',
        };
    }

    /**
     * Get Flux badge color name for the status.
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::READY => 'blue',
            self::IN_PROGRESS => 'amber',
            self::COMPLETED => 'green',
            self::CANCELLED => 'red',
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
