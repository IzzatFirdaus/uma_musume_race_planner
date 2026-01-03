<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Import Target Enum
 *
 * Defines where imported data should be stored.
 * Implements FR-6B.11: Import Target selection (Local, Account).
 */
enum ImportTarget: string
{
    case Local = 'local';
    case Account = 'account';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Local => 'Local Storage',
            self::Account => 'Account (Database)',
        };
    }

    /**
     * Get description for UI.
     */
    public function description(): string
    {
        return match ($this) {
            self::Local => 'Store in browser local storage. No account required.',
            self::Account => 'Store in your account. Requires authentication.',
        };
    }

    /**
     * Check if this target requires authentication.
     */
    public function requiresAuth(): bool
    {
        return match ($this) {
            self::Local => false,
            self::Account => true,
        };
    }
}
