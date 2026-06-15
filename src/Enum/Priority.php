<?php

namespace App\Enum;

enum Priority: string
{
    case NORMAL = 'normal';
    case HIGH = 'high';
    case URGENT = 'urgent';

    public function label(): string
    {
        return match ($this) {
            self::NORMAL => 'Normale',
            self::HIGH => 'Élevée',
            self::URGENT => 'Urgente',
        };
    }
}
