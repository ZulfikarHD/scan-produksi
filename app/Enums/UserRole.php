<?php

namespace App\Enums;

enum UserRole: string
{
    case SUPERVISOR = 'supervisor';
    case PETUGAS = 'petugas';

    public function label(): string
    {
        return match ($this) {
            self::SUPERVISOR => 'Supervisor / Ka. Unit',
            self::PETUGAS => 'Petugas Kemas',
        };
    }
}
