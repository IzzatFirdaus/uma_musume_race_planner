<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Uma class progression levels
 * per requirement FR-2.4
 */
enum UmaClass: string
{
    case Debut = 'debut';
    case Maiden = 'maiden';
    case Beginner = 'beginner';
    case Bronze = 'bronze';
    case Silver = 'silver';
    case Gold = 'gold';
    case Platinum = 'platinum';
    case Star = 'star';
    case Legend = 'legend';

    /**
     * Get human-readable label for the class.
     */
    public function label(): string
    {
        return match ($this) {
            self::Debut => 'Debut',
            self::Maiden => 'Maiden',
            self::Beginner => 'Beginner',
            self::Bronze => 'Bronze',
            self::Silver => 'Silver',
            self::Gold => 'Gold',
            self::Platinum => 'Platinum',
            self::Star => 'Star',
            self::Legend => 'Legend',
        };
    }

    /**
     * Get the progression order (0 = lowest, 8 = highest).
     */
    public function order(): int
    {
        return match ($this) {
            self::Debut => 0,
            self::Maiden => 1,
            self::Beginner => 2,
            self::Bronze => 3,
            self::Silver => 4,
            self::Gold => 5,
            self::Platinum => 6,
            self::Star => 7,
            self::Legend => 8,
        };
    }

    /**
     * Get all values as array for validation rules.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
