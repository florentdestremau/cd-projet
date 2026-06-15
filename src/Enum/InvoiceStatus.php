<?php

namespace App\Enum;

enum InvoiceStatus: string
{
    case DRAFT = 'draft';
    case SENT = 'sent';
    case PAID = 'paid';
    case OVERDUE = 'overdue';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Brouillon',
            self::SENT => 'Envoyée',
            self::PAID => 'Payée',
            self::OVERDUE => 'En retard',
            self::CANCELLED => 'Annulée',
        };
    }
}
