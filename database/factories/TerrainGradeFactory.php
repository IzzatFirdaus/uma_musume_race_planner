<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\TerrainGrade;
use Illuminate\Database\Eloquent\Factories\Factory;

class TerrainGradeFactory extends Factory
{
    protected $model = TerrainGrade::class;

    public function definition(): array
    {
        // Weighted aptitude distribution per terrain type
        $aptitudeWeights = [
            'A' => 0.60,
            'B' => 0.20,
            'C' => 0.10,
            'D' => 0.05,
            'E' => 0.03,
            'G' => 0.02,
            'S' => 0.00, // No innate S; only achievable through Sparks
        ];

        // Helper to pick grade based on weights
        $pickGrade = function () use ($aptitudeWeights) {
            $rand = mt_rand() / mt_getrandmax();
            $cumulative = 0.0;
            foreach ($aptitudeWeights as $grade => $weight) {
                $cumulative += $weight;
                if ($rand <= $cumulative) {
                    return $grade;
                }
            }

            return 'A';
        };

        // Pick terrain type
        $terrain = $this->faker->randomElement(['Turf', 'Dirt']);

        // Default aptitude: Turf tends to be higher; Dirt tends lower
        $grade = match ($terrain) {
            'Turf' => $pickGrade(),
            'Dirt' => $pickGrade(), // Use same weights or adjust if desired
        };

        return [
            'plan_id' => Plan::factory(),
            'terrain' => $terrain,
            'grade' => $grade,
        ];
    }
}
