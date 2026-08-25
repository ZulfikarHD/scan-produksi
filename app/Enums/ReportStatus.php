<?php

namespace App\Enums;

enum ReportStatus: string
{
    case DRAFT = 'draft';
    case SUBMITTED = 'submitted';
    case VERIFIED = 'verified';

    /**
     * Get human-readable Indonesian label for the report status.
     */
    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft (Sedang Dikerjakan)',
            self::SUBMITTED => 'Terkirim (Selesai Scan)',
            self::VERIFIED => 'Terverifikasi (Disetujui Ka. Unit)',
        };
    }

    /**
     * Get Flux badge color name for the status.
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::DRAFT => 'zinc',
            self::SUBMITTED => 'amber',
            self::VERIFIED => 'emerald',
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
