<?php

namespace App\Enum;

enum ProjectStatus: string
{
    case ACTIVE = 'active';
    case DELIVERED = 'delivered';
    case ON_HOLD = 'on_hold';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'En cours',
            self::DELIVERED => 'Livré',
            self::ON_HOLD => 'En attente',
            self::CANCELLED => 'Annulé',
        };
    }
}
