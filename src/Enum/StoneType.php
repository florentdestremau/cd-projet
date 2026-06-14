<?php
declare(strict_types=1);
namespace App\Enum;

enum StoneType: string
{
    case DIAMOND = 'diamond';
    case SAPPHIRE = 'sapphire';
    case RUBY = 'ruby';
    case EMERALD = 'emerald';
    case TOPAZ = 'topaz';
    case AQUAMARINE = 'aquamarine';
    case TOURMALINE = 'tourmaline';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::DIAMOND => 'Diamant',
            self::SAPPHIRE => 'Saphir',
            self::RUBY => 'Rubis',
            self::EMERALD => 'Émeraude',
            self::TOPAZ => 'Topaze',
            self::AQUAMARINE => 'Aigue-marine',
            self::TOURMALINE => 'Tourmaline',
            self::OTHER => 'Autre',
        };
    }
}
