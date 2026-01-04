<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Aptitude grade levels (SS through G)
 * per requirement FR-1.3, 8.4, 8.5
 *
 * Grade effectiveness percentages:
 * SS=120%, S=110%, A=100%, B=90%, C=80%, D=70%, E=60%, F=50%, G=40%
 */
enum AptitudeGrade: string
{
    case SS = 'SS';
    case S = 'S';
    case A = 'A';
    case B = 'B';
    case C = 'C';
    case D = 'D';
    case E = 'E';
    case F = 'F';
    case G = 'G';

    /**
     * Get the numeric value for calculations (higher = better).
     */
    public function value(): int
    {
        return match ($this) {
            self::SS => 9,
            self::S => 8,
            self::A => 7,
            self::B => 6,
            self::C => 5,
            self::D => 4,
            self::E => 3,
            self::F => 2,
            self::G => 1,
        };
    }

    /**
     * Get the effectiveness percentage for this grade.
     * Requirements: 8.4 - Show effectiveness percentages
     */
    public function effectivenessPercentage(): int
    {
        return match ($this) {
            self::SS => 120,
            self::S => 110,
            self::A => 100,
            self::B => 90,
            self::C => 80,
            self::D => 70,
            self::E => 60,
            self::F => 50,
            self::G => 40,
        };
    }

    /**
     * Get the display label with effectiveness percentage.
     */
    public function labelWithPercentage(): string
    {
        return "{$this->value} ({$this->effectivenessPercentage()}%)";
    }

    /**
     * Get Tailwind CSS text color class for the grade.
     * Requirements: 8.5 - Game-accurate colors
     */
    public function colorClass(): string
    {
        return match ($this) {
            self::SS => 'text-grade-SS',
            self::S => 'text-grade-S',
            self::A => 'text-grade-A',
            self::B => 'text-grade-B',
            self::C => 'text-grade-C',
            self::D => 'text-grade-D',
            self::E => 'text-grade-E',
            self::F => 'text-grade-F',
            self::G => 'text-grade-G',
        };
    }

    /**
     * Get background color class for badges.
     * Requirements: 8.5 - Game-accurate colors
     */
    public function bgClass(): string
    {
        return match ($this) {
            self::SS => 'bg-grade-SS',
            self::S => 'bg-grade-S',
            self::A => 'bg-grade-A',
            self::B => 'bg-grade-B',
            self::C => 'bg-grade-C',
            self::D => 'bg-grade-D',
            self::E => 'bg-grade-E',
            self::F => 'bg-grade-F',
            self::G => 'bg-grade-G',
        };
    }

    /**
     * Get the hex color for this grade.
     * Requirements: 8.5 - Game-accurate colors
     */
    public function hexColor(): string
    {
        return match ($this) {
            self::SS => '#e5e7eb', // Platinum/Light Gray
            self::S => '#ffd700',  // Gold
            self::A => '#ef4444',  // Red
            self::B => '#f97316',  // Orange
            self::C => '#22c55e',  // Green
            self::D => '#3b82f6',  // Blue
            self::E => '#a855f7',  // Purple
            self::F => '#6b7280',  // Gray
            self::G => '#9ca3af',  // Dark Gray
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

    /**
     * Get all grades as options for select dropdowns.
     *
     * @return array<array{value: string, label: string, percentage: int, color: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $grade) => [
                'value' => $grade->value,
                'label' => $grade->labelWithPercentage(),
                'percentage' => $grade->effectivenessPercentage(),
                'color' => $grade->hexColor(),
            ],
            self::cases()
        );
    }
}
