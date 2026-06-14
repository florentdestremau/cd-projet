<?php

declare(strict_types=1);

namespace App\Enum;

enum ProjectStage: string
{
    case BRIEF = 'brief';
    case SKETCH = 'sketch';
    case CLIENT_VALIDATION = 'client_validation';
    case CAD_3D = 'cad_3d';
    case WAX_PROTOTYPE = 'wax_prototype';
    case CASTING = 'casting';
    case STONE_SETTING = 'stone_setting';
    case POLISHING = 'polishing';
    case QUALITY_CONTROL = 'quality_control';
    case DELIVERY = 'delivery';

    public function label(): string
    {
        return match ($this) {
            self::BRIEF => 'Brief',
            self::SKETCH => 'Croquis',
            self::CLIENT_VALIDATION => 'Validation client',
            self::CAD_3D => 'CAO 3D',
            self::WAX_PROTOTYPE => 'Prototype cire',
            self::CASTING => 'Fonte',
            self::STONE_SETTING => 'Sertissage',
            self::POLISHING => 'Polissage',
            self::QUALITY_CONTROL => 'Contrôle qualité',
            self::DELIVERY => 'Livraison',
        };
    }

    public function position(): int
    {
        return match ($this) {
            self::BRIEF => 1,
            self::SKETCH => 2,
            self::CLIENT_VALIDATION => 3,
            self::CAD_3D => 4,
            self::WAX_PROTOTYPE => 5,
            self::CASTING => 6,
            self::STONE_SETTING => 7,
            self::POLISHING => 8,
            self::QUALITY_CONTROL => 9,
            self::DELIVERY => 10,
        };
    }

    /** @return list<self> */
    public static function ordered(): array
    {
        $cases = self::cases();
        usort($cases, fn (self $a, self $b) => $a->position() <=> $b->position());

        return $cases;
    }

    public function next(): ?self
    {
        $ordered = self::ordered();
        foreach ($ordered as $i => $stage) {
            if ($stage === $this) {
                return $ordered[$i + 1] ?? null;
            }
        }

        return null;
    }
}
