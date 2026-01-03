<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Storage mode for career runs
 * per requirement FR-9.4
 */
enum StorageMode: string
{
    case Local = 'local';
    case Account = 'account';

    /**
     * Get human-readable label for the storage mode.
     */
    public function label(): string
    {
        return match ($this) {
            self::Local => 'Local',
            self::Account => 'Account',
        };
    }

    /**
     * Get description of the storage mode.
     */
    public function description(): string
    {
        return match ($this) {
            self::Local => 'Stored in browser, available offline',
            self::Account => 'Stored in your account, synced across devices',
        };
    }

    /**
     * Get CSS class for badge styling.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Local => 'bg-amber-500 text-white',
            self::Account => 'bg-blue-500 text-white',
        };
    }

    /**
     * Get icon class for the storage mode.
     */
    public function iconClass(): string
    {
        return match ($this) {
            self::Local => 'bi-hdd',
            self::Account => 'bi-cloud',
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
