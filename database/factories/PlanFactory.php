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

    public function definition(): array
    {
        // Assign career stage weights
        $careerStages = ['predebut', 'junior', 'classic', 'senior', 'ura finale'];
        $careerStage = $this->faker->randomElement($careerStages);

        // Strategy reflects real running tactics
        $strategies = ['Front Runner', 'Pace Chaser', 'Late Surger', 'End Closer'];

        // Mood values weighted realistically
        $mood = $this->faker->randomElement(array_merge(
            array_fill(0, 4, 'Great'),
            array_fill(0, 3, 'Good'),
            ['Normal', 'Bad', 'Awful']
        ));

        return [
            'user_id' => User::factory(),
            'plan_title' => ucfirst($this->faker->words(3, true)),
            'turn_before' => $this->faker->numberBetween(0, 72), // Reflects Career Mode turns
            'race_name' => ucfirst($this->faker->words(3, true)),
            'name' => ucfirst($this->faker->name()),
            'career_stage' => strtoupper($careerStage),
            'class' => strtoupper($this->faker->randomElement(['debut', 'maiden', 'op', 'g3', 'g2', 'g1'])),
            'time_of_day' => $this->faker->randomElement(['EARLY', 'MIDDAY', 'LATE']),
            'month' => $this->faker->monthName(),
            'total_available_skill_points' => $this->faker->numberBetween(30, 100), // Realistic mid-career range
            'acquire_skill' => $this->faker->randomElement(['YES', 'NO']),
            'mood_id' => Mood::factory()->create(['label' => strtoupper($mood)])->id,
            'condition_id' => Condition::factory()->create()->id,
            'energy' => $this->faker->numberBetween(20, 100),
            'race_day' => $this->faker->randomElement(['yes', 'no']),
            'goal' => $this->faker->sentence(4),
            'strategy_id' => Strategy::factory()->create(['label' => $this->faker->randomElement($strategies)])->id,
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
