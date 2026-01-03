<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Skill acquisition status (3-state)
 * per requirement FR-4.3
 */
enum SkillStatus: string
{
    case Acquired = 'acquired';
    case Skipped = 'skipped';
    case Suggested = 'suggested';

    /**
     * Get human-readable label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Acquired => 'Acquired',
            self::Skipped => 'Skipped',
            self::Suggested => 'Suggested',
        };
    }

    /**
     * Get CSS class for status badge styling.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Acquired => 'bg-green-500 text-white',
            self::Skipped => 'bg-gray-400 text-white',
            self::Suggested => 'bg-yellow-400 text-gray-900',
        };
    }

    /**
     * Get icon class for the status.
     */
    public function iconClass(): string
    {
        return match ($this) {
            self::Acquired => 'bi-check-circle-fill',
            self::Skipped => 'bi-x-circle',
            self::Suggested => 'bi-lightbulb',
        };
    }

    /**
     * Check if this status requires turn_acquired to be set.
     */
    public function requiresTurnAcquired(): bool
    {
        return $this === self::Acquired;
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
