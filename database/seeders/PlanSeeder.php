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

        // --- Data for [Bestest Prize 𝆕] Haru Urara ---
        $urara = Plan::create([
            'user_id' => $user->id,
            'plan_title' => '[Bestest Prize 𝆕] Haru Urara Plan',
            'turn_before' => 0,
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
            'growth_rate_power' => 10,
            'growth_rate_guts' => 20,
            'status' => 'Planning',
            'source' => 'Manual Input Data 1',
        ]);

        $urara->attributes()->createMany([
            ['attribute_name' => 'SPEED', 'value' => 423, 'grade' => 'C'],
            ['attribute_name' => 'STAMINA', 'value' => 276, 'grade' => 'E+'],
            ['attribute_name' => 'POWER', 'value' => 461, 'grade' => 'C'],
            ['attribute_name' => 'GUTS', 'value' => 448, 'grade' => 'C'],
            ['attribute_name' => 'WIT', 'value' => 264, 'grade' => 'E+'],
        ]);

        $this->createSkill($urara, 'Super Duper Stoked Lvl.1', ['description' => '(Unique Burst)'], ['acquired' => 'yes', 'notes' => '(Unique Burst)']);
        $this->createSkill($urara, '∴ Win Q.E.D.', ['description' => 'Received from legacy. — powerful finishing burst'], ['acquired' => 'yes', 'notes' => 'Received from legacy. — powerful finishing burst']);
        $this->createSkill($urara, 'Summer Runner ○', [], ['sp_cost' => 63, 'acquired' => 'no']);
        // ... more skills for Urara

        $urara->terrainGrades()->createMany([['terrain' => 'Turf', 'grade' => 'C'], ['terrain' => 'Dirt', 'grade' => 'A']]);
        $urara->distanceGrades()->createMany([['distance' => 'Sprint', 'grade' => 'A'], ['distance' => 'Mile', 'grade' => 'A'], ['distance' => 'Medium', 'grade' => 'G'], ['distance' => 'Long', 'grade' => 'G']]);
        $urara->styleGrades()->createMany([['style' => 'Front', 'grade' => 'G'], ['style' => 'Pace', 'grade' => 'G'], ['style' => 'Late', 'grade' => 'A'], ['style' => 'End', 'grade' => 'B']]);

        $urara->racePredictions()->create(['race_name' => 'URA Finale Qualifier', 'venue' => 'KYOTO', 'ground' => 'DIRT', 'distance' => '1400M', 'track_condition' => 'SPRINT', 'direction' => 'RIGHT', 'speed' => '△', 'stamina' => 'X', 'power' => '○', 'guts' => '○', 'wit' => 'X', 'comment' => 'Your trainee isn’t a bad runner, but she may be outclassed. However, she might have a chance depending on the condition of her opponents.']);

        $urara->goals()->createMany([['goal' => 'JUNIOR MAKE DEBUT', 'result' => 'RUN RACE'], ['goal' => 'RUN RACE', 'result' => '2ND']/* ... more goals ... */]);

        // --- Data for [El☆Número 1] El Condor Pasa ---
        $elCondor = Plan::create([
            'user_id' => $user->id,
            'plan_title' => '[El☆Número 1] El Condor Pasa Plan',
            'turn_before' => 12,
            'race_name' => 'Kyodo News Hai',
            'name' => '[El☆Número 1] El Condor Pasa',
            'career_stage' => 'junior',
            'class' => 'beginner',
            'time_of_day' => 'EARLY',
            'month' => 'AUG',
            'total_available_skill_points' => 75,
            'acquire_skill' => 'NO',
            'mood_id' => $moodNormalId,
            'condition_id' => $conditionNaId,
            'energy' => 80,
            'race_day' => 'no',
            'goal' => 'TOP 5',
            'strategy_id' => $strategyPaceId,
            'growth_rate_speed' => 20,
            'growth_rate_wit' => 10,
            'status' => 'Active',
            'source' => 'Manual Input Data 2',
        ]);
        // ... add attributes, skills, grades, etc. for El Condor Pasa

        // --- Repeat for all other 4 plans from sample_data.sql ---
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
