<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Career year stages in Uma Musume training
 * per requirement FR-2.2
 */
enum CareerYear: string
{
    case Junior = 'junior';
    case Classic = 'classic';
    case Senior = 'senior';

    /**
     * Get human-readable label for the career year.
     */
    public function label(): string
    {
        return match ($this) {
            self::Junior => 'Junior',
            self::Classic => 'Classic',
            self::Senior => 'Senior',
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
