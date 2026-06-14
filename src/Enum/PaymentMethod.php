<?php

declare(strict_types=1);

namespace App\Enum;

enum PaymentMethod: string
{
    case TRANSFER = 'transfer';
    case CARD = 'card';
    case CASH = 'cash';
    case CHECK = 'check';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::TRANSFER => 'Virement',
            self::CARD => 'Carte',
            self::CASH => 'Espèces',
            self::CHECK => 'Chèque',
            self::OTHER => 'Autre',
        };
    }
}
