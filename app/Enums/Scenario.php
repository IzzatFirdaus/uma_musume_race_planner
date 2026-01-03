<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Training scenario types
 * per requirement FR-10.1
 */
enum Scenario: string
{
    case URA = 'URA';

    // Future scenarios can be added here without schema changes
    // case Aoharu = 'Aoharu';
    // case MakeANewTrack = 'MakeANewTrack';
    // case GrandLive = 'GrandLive';
    // case MANT = 'MANT';
    // case LArc = 'LArc';
    // case GrandMasters = 'GrandMasters';
    // case ProjectLArc = 'ProjectLArc';

    /**
     * Get human-readable label for the scenario.
     */
    public function label(): string
    {
        return match ($this) {
            self::URA => 'URA Finals',
        };
    }

    /**
     * Get description of the scenario.
     */
    public function description(): string
    {
        return match ($this) {
            self::URA => 'The original Uma Musume training scenario',
        };
    }

    /**
     * Get the default scenario.
     */
    public static function default(): self
    {
        return self::URA;
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
