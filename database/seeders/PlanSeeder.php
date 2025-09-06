<?php

namespace Database\Seeders;

use App\Models\Condition;
use App\Models\Mood;
use App\Models\Plan;
use App\Models\SkillReference;
use App\Models\Strategy;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * This seeder populates plans and all related child data from sample_data.sql.
     */
    public function run(): void
    {
        // To prevent foreign key constraint errors, we disable checks.
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate all plan-related tables
        Plan::truncate();
        DB::table('attributes')->truncate();
        DB::table('skills')->truncate();
        DB::table('goals')->truncate();
        DB::table('race_predictions')->truncate();
        DB::table('terrain_grades')->truncate();
        DB::table('distance_grades')->truncate();
        DB::table('style_grades')->truncate();
        DB::table('turns')->truncate();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Find the default user created in DatabaseSeeder to own the plans
        $user = User::where('email', 'test@example.com')->first();
        if (! $user) {
            $this->command->error('Default user not found. Please ensure DatabaseSeeder runs the User factory.');

            return;
        }

        // Replicate SQL variables by fetching IDs with Eloquent
        $moodGoodId = Mood::where('label', 'GOOD')->value('id');
        $moodNormalId = Mood::where('label', 'NORMAL')->value('id');
        $moodNaId = Mood::where('label', 'N/A')->value('id');
        $conditionCharmingId = Condition::where('label', 'CHARMING')->value('id');
        $conditionNaId = Condition::where('label', 'N/A')->value('id');
        $conditionHotTopicId = Condition::where('label', 'HOT TOPIC')->value('id');
        $strategyLateId = Strategy::where('label', 'LATE')->value('id');
        $strategyPaceId = Strategy::where('label', 'PACE')->value('id');
        $strategyFrontId = Strategy::where('label', 'FRONT')->value('id');

        // --- Data for all 8 plans from plan_sample_data.sql ---
        $plans = [
            // 1
            [
                'plan_title' => '[pf. Winning Equation…] Biwa Hayahide Plan',
                'turn_before' => 0,
                'race_name' => 'Tenno Sho (Spring)',
                'name' => '[pf. Winning Equation…] Biwa Hayahide',
                'career_stage' => 'senior',
                'class' => 'silver',
                'total_available_skill_points' => 17,
                'acquire_skill' => 'NO',
                'mood_id' => $moodGoodId,
                'condition_id' => $conditionHotTopicId,
                'energy' => 20,
                'race_day' => 'yes',
                'goal' => 'TOP 3',
                'strategy_id' => $strategyPaceId,
                'growth_rate_speed' => 0,
                'growth_rate_power' => 0,
                'growth_rate_wit' => 20,
                'growth_rate_stamina' => 0,
                'growth_rate_guts' => 10,
                'status' => 'Planning',
            ],
            // 2
            [
                'plan_title' => '[Wild Top Gear] Vodka Plan',
                'race_name' => 'URA Finale Finals',
                'name' => '[Wild Top Gear] Vodka',
                'career_stage' => 'finale',
                'class' => 'star',
                'total_available_skill_points' => null,
                'acquire_skill' => 'NO',
                'mood_id' => null,
                'condition_id' => null,
                'energy' => null,
                'race_day' => 'no',
                'goal' => 'VODKA IS 1ST PLACE',
                'strategy_id' => null,
                'growth_rate_speed' => 10,
                'growth_rate_power' => 20,
                'growth_rate_wit' => 0,
                'growth_rate_stamina' => 0,
                'growth_rate_guts' => 0,
                'status' => 'Finished',
            ],
            // 3
            [
                'plan_title' => '[Wild Top Gear] Vodka Plan',
                'race_name' => 'URA Finale Finals',
                'name' => '[Wild Top Gear] Vodka',
                'career_stage' => 'finale',
                'class' => 'platinum',
                'total_available_skill_points' => 347,
                'acquire_skill' => 'YES',
                'mood_id' => null,
                'condition_id' => null,
                'energy' => null,
                'race_day' => 'no',
                'goal' => 'SHE WON 1ST',
                'strategy_id' => null,
                'growth_rate_speed' => 10,
                'growth_rate_power' => 20,
                'growth_rate_wit' => 0,
                'growth_rate_stamina' => 0,
                'growth_rate_guts' => 0,
                'status' => 'Finished',
            ],
            // 4
            [
                'plan_title' => '[Peak Blue] Daiwa Scarlet Plan',
                'race_name' => 'URA Finale Finals',
                'name' => '[Peak Blue] Daiwa Scarlet',
                'career_stage' => 'finale',
                'class' => 'star',
                'total_available_skill_points' => 75,
                'acquire_skill' => 'YES',
                'mood_id' => null,
                'condition_id' => null,
                'energy' => null,
                'race_day' => 'no',
                'goal' => 'SHE’S 2ND PLACE',
                'strategy_id' => $strategyFrontId,
                'growth_rate_speed' => 10,
                'growth_rate_power' => 0,
                'growth_rate_wit' => 0,
                'growth_rate_stamina' => 0,
                'growth_rate_guts' => 20,
                'status' => 'Finished',
            ],
            // 5
            [
                'plan_title' => '[Beyond the Horizon] Tokai Teio Plan',
                'race_name' => 'Tenno Sho (Spring)',
                'name' => '[Beyond the Horizon] Tokai Teio',
                'career_stage' => 'classic',
                'class' => 'gold',
                'total_available_skill_points' => 38,
                'acquire_skill' => 'NO',
                'mood_id' => $moodNormalId,
                'condition_id' => null,
                'energy' => 30,
                'race_day' => 'yes',
                'goal' => 'TOP 3',
                'strategy_id' => $strategyPaceId,
                'growth_rate_speed' => 10,
                'growth_rate_power' => 0,
                'growth_rate_wit' => 0,
                'growth_rate_stamina' => 10,
                'growth_rate_guts' => 10,
                'status' => 'Planning',
            ],
            // 6
            [
                'plan_title' => '[Bestest Prize 𝆕] Haru Urara Plan',
                'race_name' => 'JBC SPRINT',
                'name' => '[Bestest Prize 𝆕] Haru Urara',
                'career_stage' => 'senior',
                'class' => 'silver',
                'total_available_skill_points' => 174,
                'acquire_skill' => 'YES',
                'mood_id' => $moodGoodId,
                'condition_id' => null,
                'energy' => 20,
                'race_day' => 'yes',
                'goal' => 'MUST 1ST',
                'strategy_id' => $strategyLateId,
                'growth_rate_speed' => 0,
                'growth_rate_power' => 0,
                'growth_rate_wit' => 0,
                'growth_rate_stamina' => 0,
                'growth_rate_guts' => 20,
                'status' => 'Planning',
            ],
            // 7
            [
                'plan_title' => '[Bestest Prize 𝆕] Haru Urara Plan',
                'race_name' => 'URA Finale Qualifier',
                'name' => '[Bestest Prize 𝆕] Haru Urara',
                'career_stage' => 'finale',
                'class' => 'silver',
                'total_available_skill_points' => 4,
                'acquire_skill' => 'NO',
                'mood_id' => $moodGoodId,
                'condition_id' => $conditionCharmingId,
                'energy' => 20,
                'race_day' => 'yes',
                'goal' => '1ST',
                'strategy_id' => $strategyLateId,
                'growth_rate_speed' => 0,
                'growth_rate_power' => 10,
                'growth_rate_wit' => 0,
                'growth_rate_stamina' => 0,
                'growth_rate_guts' => 20,
                'status' => 'Planning',
            ],
            // 8
            [
                'plan_title' => '[El☆Número 1] El Condor Pasa Plan',
                'turn_before' => 12,
                'race_name' => 'Kyodo News Hai',
                'name' => '[El☆Número 1] El Condor Pasa',
                'career_stage' => 'junior',
                'class' => 'beginner',
                'total_available_skill_points' => 38,
                'acquire_skill' => 'NO',
                'mood_id' => $moodGoodId,
                'condition_id' => null,
                'energy' => 50,
                'race_day' => 'no',
                'goal' => 'TOP 5',
                'strategy_id' => $strategyPaceId,
                'growth_rate_speed' => 20,
                'growth_rate_power' => 0,
                'growth_rate_wit' => 10,
                'growth_rate_stamina' => 0,
                'growth_rate_guts' => 0,
                'status' => 'Planning',
            ],
        ];

        foreach ($plans as $planData) {
            $planData['user_id'] = $user->id;
            $plan = Plan::create($planData);
            // You can add attributes, skills, grades, etc. for each plan here
        }
    }

    /**
     * Helper function to create a skill for a plan.
     * Replicates the GetSkillReferenceId SQL function using firstOrCreate.
     *
     * @param  Plan  $plan  The plan to attach the skill to.
     * @param  string  $skillName  The name of the skill.
     * @param  array  $referenceData  Data to use if creating the reference (description, tag).
     * @param  array  $skillData  Data for the skill itself (sp_cost, acquired, notes).
     */
    private function createSkill(Plan $plan, string $skillName, array $referenceData, array $skillData): void
    {
        $skillRef = SkillReference::firstOrCreate(['skill_name' => $skillName], $referenceData);

        $plan->skills()->create(array_merge($skillData, ['skill_reference_id' => $skillRef->id]));
    }
}
