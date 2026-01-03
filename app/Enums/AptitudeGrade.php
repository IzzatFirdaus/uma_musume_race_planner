<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Aptitude grade levels (S through G)
 * per requirement FR-1.3
 */
enum AptitudeGrade: string
{
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
     * Get Tailwind CSS color class for the grade.
     */
    public function colorClass(): string
    {
        return match ($this) {
            self::S => 'text-grade-s',
            self::A => 'text-grade-a',
            self::B => 'text-grade-b',
            self::C => 'text-grade-c',
            self::D => 'text-grade-d',
            self::E => 'text-grade-e',
            self::F => 'text-grade-f',
            self::G => 'text-grade-g',
        };
    }

    /**
     * Get background color class for badges.
     */
    public function bgClass(): string
    {
        return match ($this) {
            self::S => 'bg-grade-s',
            self::A => 'bg-grade-a',
            self::B => 'bg-grade-b',
            self::C => 'bg-grade-c',
            self::D => 'bg-grade-d',
            self::E => 'bg-grade-e',
            self::F => 'bg-grade-f',
            self::G => 'bg-grade-g',
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
