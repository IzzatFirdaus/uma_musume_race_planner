<?php

namespace Database\Factories;

use App\Models\DistanceGrade;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

class DistanceGradeFactory extends Factory
{
    protected $model = DistanceGrade::class;

    public function definition(): array
    {
        // Define realistic aptitude distributions with weighted probabilities
        $aptitudeDistribution = [
            'S' => 0.10,  // Rare high bonus
            'A' => 0.30,  // Standard baseline
            'B' => 0.35,  // Common mid-range
            'C' => 0.15,  // Suboptimal but possible
            'D' => 0.05,  // Rarely used
            'E' => 0.03,
            'F' => 0.02,
            'G' => 0.00,  // Almost never assigned
        ];

        // Helper to pick rank based on weighted distribution
        $pickRank = function () use ($aptitudeDistribution) {
            $rand = mt_rand() / mt_getrandmax();
            $cumulative = 0;

            foreach ($aptitudeDistribution as $rank => $weight) {
                $cumulative += $weight;
                if ($rand <= $cumulative) {
                    return $rank;
                }
            }

            return 'B';
        };

        return [
            'plan_id' => Plan::factory(),
            'distance' => $this->faker->randomElement(['Sprint', 'Mile', 'Medium', 'Long']),
            'grade' => $pickRank(),
        ];
    }
}
