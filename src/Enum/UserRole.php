<?php

namespace App\Enum;

enum UserRole: string
{
    case ADMIN = 'ROLE_ADMIN';
    case COMMERCIAL = 'ROLE_COMMERCIAL';
    case DESIGNER = 'ROLE_DESIGNER';
    case JEWELER = 'ROLE_JEWELER';
    case SETTER = 'ROLE_SETTER';
    case ACCOUNTANT = 'ROLE_ACCOUNTANT';
    case CLIENT = 'ROLE_CLIENT';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrateur',
            self::COMMERCIAL => 'Commercial',
            self::DESIGNER => 'Designer',
            self::JEWELER => 'Joaillier',
            self::SETTER => 'Sertisseur',
            self::ACCOUNTANT => 'Comptable',
            self::CLIENT => 'Client',
        };
    }
}
