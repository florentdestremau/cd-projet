<?php

declare(strict_types=1);

namespace App\Enum;

enum DocumentCategory: string
{
    case SKETCH = 'sketch';
    case PHOTO = 'photo';
    case CAD = 'cad';
    case INVOICE = 'invoice';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::SKETCH => 'Croquis',
            self::PHOTO => 'Photo',
            self::CAD => 'CAO',
            self::INVOICE => 'Facture',
            self::OTHER => 'Autre',
        };
    }
}
