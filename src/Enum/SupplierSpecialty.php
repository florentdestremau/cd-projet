<?php

declare(strict_types=1);

namespace App\Enum;

enum SupplierSpecialty: string
{
    case STONES = 'stones';
    case METALS = 'metals';
    case CASTING = 'casting';
    case SETTING = 'setting';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::STONES => 'Pierres',
            self::METALS => 'Métaux',
            self::CASTING => 'Fonte',
            self::SETTING => 'Sertissage',
            self::OTHER => 'Autre',
        };
    }
}
