<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Career run status values
 * per requirement FR-2.3
 */
enum RunStatus: string
{
    case Ongoing = 'ongoing';
    case Finished = 'finished';
    case Failed = 'failed';

    /**
     * Get human-readable label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Ongoing => 'Ongoing',
            self::Finished => 'Finished',
            self::Failed => 'Failed',
        };
    }

    /**
     * Get CSS class for status badge styling.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Ongoing => 'bg-blue-500',
            self::Finished => 'bg-green-500',
            self::Failed => 'bg-red-500',
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
