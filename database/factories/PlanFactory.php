<?php

namespace Database\Factories;

use App\Models\Condition;
use App\Models\Mood;
use App\Models\Plan;
use App\Models\Strategy;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlanFactory extends Factory
{
    protected $model = Plan::class;

    /**
     * Get or create a Mood record to avoid unique constraint violations.
     */
    private function getOrCreateMood(): int
    {
        $labels = ['AWFUL', 'BAD', 'NORMAL', 'GOOD', 'GREAT'];
        $label = $this->faker->randomElement($labels);

        return Mood::firstOrCreate(['label' => $label])->id;
    }

    /**
     * Get or create a Condition record to avoid unique constraint violations.
     */
    private function getOrCreateCondition(): int
    {
        $labels = [
            'MIGRAINE',
            'DRY SKIN',
            'INSOMNIA',
            'SLOW METABOLISM',
            'SLACKER',
            'UNDER THE WEATHER',
            'SPRING BUD',
            'SUSPICIOUS CLOUDS',
        ];
        $label = $this->faker->randomElement($labels);

        return Condition::firstOrCreate(['label' => $label])->id;
    }

    /**
     * Get or create a Strategy record to avoid unique constraint violations.
     */
    private function getOrCreateStrategy(): int
    {
        $labels = ['FRONT', 'PACE', 'LATE', 'END'];
        $label = $this->faker->randomElement($labels);

        return Strategy::firstOrCreate(['label' => $label])->id;
    }

    public function definition(): array
    {
        // Assign career stage weights
        $careerStages = ['predebut', 'junior', 'classic', 'senior', 'finale'];
        $careerStage = $this->faker->randomElement($careerStages);

        return [
            'user_id' => User::factory(),
            'plan_title' => ucfirst($this->faker->words(3, true)),
            'turn_before' => $this->faker->numberBetween(0, 72), // Reflects Career Mode turns
            'race_name' => ucfirst($this->faker->words(3, true)),
            'name' => ucfirst($this->faker->name()),
            'career_stage' => $careerStage,
            'class' => $this->faker->randomElement(['debut', 'maiden', 'beginner', 'bronze', 'silver', 'gold', 'platinum', 'star', 'legend']),
            'time_of_day' => $this->faker->randomElement(['early', 'midday', 'late']),
            'month' => $this->faker->monthName(),
            'total_available_skill_points' => $this->faker->numberBetween(30, 100), // Realistic mid-career range
            'acquire_skill' => $this->faker->randomElement(['YES', 'NO']),
            'mood_id' => $this->getOrCreateMood(),
            'condition_id' => $this->getOrCreateCondition(),
            'energy' => $this->faker->numberBetween(20, 100),
            'race_day' => $this->faker->randomElement(['yes', 'no']),
            'goal' => $this->faker->sentence(4),
            'strategy_id' => $this->getOrCreateStrategy(),
            'growth_rate_speed' => $this->faker->numberBetween(15, 25),
            'growth_rate_stamina' => $this->faker->numberBetween(10, 25),
            'growth_rate_power' => $this->faker->numberBetween(10, 20),
            'growth_rate_guts' => $this->faker->numberBetween(0, 10),
            'growth_rate_wit' => $this->faker->numberBetween(10, 20),
            'status' => $this->faker->randomElement(['Planning', 'Active', 'Finished', 'Abandoned']),
            'source' => $this->faker->word(),
            'trainee_image_path' => $this->faker->imageUrl(640, 480, 'animals', true),
            'deleted_at' => null,
        ];
    }
}
