<?php

namespace App\Enums;

enum ProductSeries: string
{
    case PCHT = 'pcht';
    case MMEA = 'mmea';
    case HPTL = 'hptl';

    /**
     * Get human-readable Indonesian label for the product series.
     */
    public function label(): string
    {
        return match ($this) {
            self::PCHT => 'Pita Cukai Hasil Tembakau (PCHT)',
            self::MMEA => 'Minuman Mengandung Etil Alkohol (MMEA)',
            self::HPTL => 'Hasil Pengolahan Tembakau Lainnya / REL (HPTL)',
        };
    }

    /**
     * Get short title for badges or compact cards.
     */
    public function shortLabel(): string
    {
        return match ($this) {
            self::PCHT => 'PCHT',
            self::MMEA => 'MMEA',
            self::HPTL => 'HPTL / REL',
        };
    }

    /**
     * Get descriptive subtitle for product category selection.
     */
    public function description(): string
    {
        return match ($this) {
            self::PCHT => 'Pita cukai untuk rokok kretek, cerutu, rokok putih, dan produk tembakau.',
            self::MMEA => 'Pita cukai untuk produk minuman yang mengandung alkohol / etil alkohol.',
            self::HPTL => 'Pita cukai untuk rokok elektrik (REL), cairan vape, dan hasil tembakau lainnya.',
        };
    }

    /**
     * Get uppercase code prefix for report generation.
     */
    public function code(): string
    {
        return match ($this) {
            self::PCHT => 'PCHT',
            self::MMEA => 'MMEA',
            self::HPTL => 'HPTL',
        };
    }

    /**
     * Get Flux UI color identifier.
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::PCHT => 'emerald',
            self::MMEA => 'purple',
            self::HPTL => 'amber',
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
