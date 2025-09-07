<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\StyleGrade;
use Illuminate\Database\Eloquent\Factories\Factory;

class StyleGradeFactory extends Factory
{
    protected $model = StyleGrade::class;

    public function definition(): array
    {
        // Realistic style options
        $styles = ['Front Runner', 'Pace Chaser', 'Late Surger', 'End Closer'];

        // Style grades with weighted likelihoods (B/A common, S rare)
        $gradeDistribution = [
            'S' => 0.10,
            'A' => 0.30,
            'B' => 0.40,
            'C' => 0.15,
            'D' => 0.04,
            'E' => 0.008,
            'F' => 0.002,
        ];

        // Helper to pick weighted grade
        $pickWeightedGrade = function () use ($gradeDistribution) {
            $rand = mt_rand() / mt_getrandmax();
            $cumulative = 0;
            foreach ($gradeDistribution as $grade => $weight) {
                $cumulative += $weight;
                if ($rand <= $cumulative) {
                    return $grade;
                }
            }

            return 'B'; // Default fallback
        };

        return [
            'plan_id' => Plan::factory(),
            'style' => $this->faker->randomElement($styles),
            'grade' => $pickWeightedGrade(),
        ];
    }
}
