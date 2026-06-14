<?php

declare(strict_types=1);

namespace App\Enum;

enum ExpenseCategory: string
{
    case MATERIAL = 'material';
    case STONE = 'stone';
    case SUBCONTRACT = 'subcontract';
    case SHIPPING = 'shipping';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::MATERIAL => 'Matière',
            self::STONE => 'Pierre',
            self::SUBCONTRACT => 'Sous-traitance',
            self::SHIPPING => 'Expédition',
            self::OTHER => 'Autre',
        };
    }
}
