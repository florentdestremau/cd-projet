<?php

namespace App\Enum;

enum MaterialType: string
{
    case GOLD_18K = 'gold_18k';
    case GOLD_14K = 'gold_14k';
    case GOLD_9K = 'gold_9k';
    case PLATINUM = 'platinum';
    case PALLADIUM = 'palladium';
    case SILVER = 'silver';

    public function label(): string
    {
        return match ($this) {
            self::GOLD_18K => 'Or 18 carats',
            self::GOLD_14K => 'Or 14 carats',
            self::GOLD_9K => 'Or 9 carats',
            self::PLATINUM => 'Platine',
            self::PALLADIUM => 'Palladium',
            self::SILVER => 'Argent',
        };
    }
}
