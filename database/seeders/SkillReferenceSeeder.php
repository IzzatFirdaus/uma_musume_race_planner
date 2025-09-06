<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SkillReferenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * This seeder populates the skill_reference table with the master skill list.
     *
     * NOTE: Uses delete() instead of truncate() to avoid foreign key constraint violations,
     * since the skills table references skill_reference via skill_reference_id.
     */
    public function run(): void
    {
        // SAFELY clear the table (preserves foreign key integrity)
        DB::table('skill_reference')->delete();

        // Insert all 46 skill references from sample_data.sql.
        DB::table('skill_reference')->insert([
            // --- Acceleration & Speed Skills (🔺) ---
            ['skill_name' => 'Triumphant Pulse', 'description' => 'High-powered final‑stretch acceleration burst', 'stat_type' => 'Acceleration', 'best_for' => 'End Closers', 'tag' => '🔺'],
            ['skill_name' => 'Red Shift/LP1211‑M', 'description' => 'Final‑corner burst when leading', 'stat_type' => 'Acceleration', 'best_for' => 'Daiwa Scarlet, Maruzensky', 'tag' => '🔺'],
            ['skill_name' => 'Corner Acceleration ○', 'description' => 'Burst of speed during corners', 'stat_type' => 'Acceleration', 'best_for' => 'Corner maneuverers', 'tag' => '🔺'],
            ['skill_name' => 'Corner Adept ○', 'description' => 'Slight speed boost on corners', 'stat_type' => 'Speed', 'best_for' => 'All‑rounders', 'tag' => '🔺'],
            ['skill_name' => 'Slipstream', 'description' => 'Acceleration boost when following close behind another', 'stat_type' => 'Acceleration', 'best_for' => 'Pacer / stalkers', 'tag' => '🔺'],
            ['skill_name' => 'Tail Held High', 'description' => 'Speed boost on final straight', 'stat_type' => 'Speed', 'best_for' => 'Late‑stage front/pacers', 'tag' => '🔺'],
            ['skill_name' => 'Straightaway Adept', 'description' => 'Slight speed burst on straights', 'stat_type' => 'Speed', 'best_for' => 'Front or pace strategies', 'tag' => '🔺'],
            ['skill_name' => 'Straightaway Acceleration', 'description' => 'Acceleration on straights', 'stat_type' => 'Acceleration', 'best_for' => 'Medium‑distance / pace runs', 'tag' => '🔺'],
            ['skill_name' => 'Straightaways ○', 'description' => 'Speed up on straight segments', 'stat_type' => 'Speed', 'best_for' => 'All track types', 'tag' => '🔺'],
            ['skill_name' => 'Corners ○', 'description' => 'Speed boost during corner turns', 'stat_type' => 'Speed', 'best_for' => 'All track types', 'tag' => '🔺'],
            ['skill_name' => 'Fast & Furious', 'description' => 'Mid‑race speed boost', 'stat_type' => 'Speed', 'best_for' => 'Pace/front runners', 'tag' => '🔺'],
            ['skill_name' => 'Shifting Gears', 'description' => 'Acceleration when passing mid‑race', 'stat_type' => 'Acceleration', 'best_for' => 'Front‑runner builds', 'tag' => '🔺'],
            ['skill_name' => 'Speed Star', 'description' => 'Easy‑to‑proc corner speed buff', 'stat_type' => 'Speed', 'best_for' => 'All‑rounders', 'tag' => '🔺'],
            ['skill_name' => 'Inside Scoop', 'description' => 'Corner boost when near inner rail', 'stat_type' => 'Acceleration', 'best_for' => 'Corner‑savvy runners', 'tag' => '🔺'],
            ['skill_name' => 'Pressure', 'description' => 'Slight accel. boost when passing another horse', 'stat_type' => 'Acceleration', 'best_for' => 'Gold Ship, End‑Closer builds', 'tag' => '🔺'],
            ['skill_name' => 'Straightaway Spurt', 'description' => 'Final straight acceleration burst', 'stat_type' => 'Acceleration', 'best_for' => 'Vodka', 'tag' => '🔺'],
            ['skill_name' => 'Unrestrained', 'description' => 'Hold the lead on the final corner', 'stat_type' => 'Speed', 'best_for' => 'Front‑runners', 'tag' => '🔺'],
            ['skill_name' => 'Acceleration', 'description' => 'General pass burst mid‑race', 'stat_type' => 'Acceleration', 'best_for' => 'Front or passing builds', 'tag' => '🔺'],
            ['skill_name' => 'Moxie', 'description' => 'Burst of late‑race acceleration when contested', 'stat_type' => 'Acceleration', 'best_for' => 'Late‑surger / pace‑fallback builds', 'tag' => '🔺'],
            ['skill_name' => 'Concentration', 'description' => 'Reduce time lost to slow starts', 'stat_type' => 'Speed', 'best_for' => 'All runners', 'tag' => '🔺'],
            ['skill_name' => 'Turbo Sprint', 'description' => 'Massive accel. boost in opening phase', 'stat_type' => 'Acceleration', 'best_for' => 'Sprinters', 'tag' => '🔺'],
            ['skill_name' => 'Homestretch Haste', 'description' => 'Small boost at start of final straight', 'stat_type' => 'Speed', 'best_for' => 'Late rallies', 'tag' => '🔺'],
            ['skill_name' => 'Ramp Up', 'description' => 'Gradual speed increase after mid‑race', 'stat_type' => 'Speed', 'best_for' => 'Medium‑distance', 'tag' => '🔺'],
            ['skill_name' => 'Behold Thine Emperor', 'description' => 'Massive corner acceleration when leading', 'stat_type' => 'Acceleration', 'best_for' => 'Elite corner specialists', 'tag' => '🔺'],
            ['skill_name' => 'The Duty of Dignity Calls', 'description' => 'Speed boost when leading late in race', 'stat_type' => 'Speed', 'best_for' => 'Regal‑pacing builds', 'tag' => '🔺'],
            ['skill_name' => 'Vanguard Spirit', 'description' => 'Maintain speed when leading by a big margin', 'stat_type' => 'Speed', 'best_for' => 'Long‑distance front‑running', 'tag' => '🔺'],
            ['skill_name' => 'Taking the Lead', 'description' => 'Burst when surging to the front early‑race', 'stat_type' => 'Speed', 'best_for' => 'Front‑runner builds', 'tag' => '🔺'],
            // --- Recovery Skills (🔋) ---
            ['skill_name' => 'Swinging Maestro', 'description' => 'Recover stamina and improve navigation in corners', 'stat_type' => 'Recovery + Positioning', 'best_for' => 'Long‑distance / corner‑heavy races', 'tag' => '🔋'],
            ['skill_name' => 'Hydrate', 'description' => 'Recover stamina mid‑race', 'stat_type' => 'Recovery', 'best_for' => 'All runners', 'tag' => '🔋'],
            ['skill_name' => 'Race Planner', 'description' => 'Reduce early‑race stamina drain', 'stat_type' => 'Recovery', 'best_for' => 'Mid‑long distance runs', 'tag' => '🔋'],
            ['skill_name' => 'Passing Pro', 'description' => 'Recover stamina when passing', 'stat_type' => 'Recovery', 'best_for' => 'Stalkers', 'tag' => '🔋'],
            ['skill_name' => 'Gourmand', 'description' => 'Recover stamina upon triggering many skills', 'stat_type' => 'Recovery', 'best_for' => 'Skill‑heavy builds', 'tag' => '🔋'],
            ['skill_name' => 'Shake It Out', 'description' => 'Recover fatigue after multiple skills', 'stat_type' => 'Recovery', 'best_for' => 'Combo‑skill builds', 'tag' => '🔋'],
            ['skill_name' => 'Second Wind', 'description' => 'Regain a burst of stamina mid‑race when fatigued', 'stat_type' => 'Recovery', 'best_for' => 'Endurance‑hybrid runners', 'tag' => '🔋'],
            ['skill_name' => 'Iron Will', 'description' => 'Early‑race recovery in crowded tracks', 'stat_type' => 'Recovery', 'best_for' => 'Pack‑runner builds', 'tag' => '🔋'],
            // --- Passive Skills (📊) ---
            ['skill_name' => 'Lone Wolf', 'description' => 'Speed boost if only one of your style', 'stat_type' => 'Passive', 'best_for' => 'Niche/style‑split strategies', 'tag' => '📊'],
            ['skill_name' => 'Right‑Handed ○', 'description' => 'Performance boost on right‑turn tracks', 'stat_type' => 'Passive', 'best_for' => 'Track‑specific races', 'tag' => '📊'],
            ['skill_name' => 'Standard Distance ○', 'description' => 'Boost on standard‑distance races', 'stat_type' => 'Passive', 'best_for' => 'Mile/medium specialists', 'tag' => '📊'],
            ['skill_name' => 'Firm Conditions ○', 'description' => 'Performance boost in firm (dry) conditions', 'stat_type' => 'Passive', 'best_for' => 'Stable weather races', 'tag' => '📊'],
            ['skill_name' => 'Savvy (Style‑based) ○', 'description' => 'Passive boost tied to your running style', 'stat_type' => 'Passive', 'best_for' => 'Depends on run style', 'tag' => '📊'],
            // --- Debuff Skills (⛔) ---
            ['skill_name' => 'Dominator', 'description' => 'Debuff nearby opponents’ power mid‑race', 'stat_type' => 'Debuff', 'best_for' => 'Lead‑protect builds', 'tag' => '⛔'],
            ['skill_name' => 'Intimidate', 'description' => 'Lower stamina of surrounding foes', 'stat_type' => 'Debuff', 'best_for' => 'Pack‑thin suppression', 'tag' => '⛔'],
            ['skill_name' => 'Mystifying Murmur', 'description' => 'Confuse surrounding enemies, lowering their effectiveness', 'stat_type' => 'Debuff', 'best_for' => 'High‑Wit PvP builds', 'tag' => '⛔'],
            ['skill_name' => 'All‑Seeing Eyes', 'description' => 'Late‑race debuff against nearby opponents', 'stat_type' => 'Debuff', 'best_for' => 'End‑battle setups', 'tag' => '⛔'],
            ['skill_name' => 'Stamina Eater', 'description' => 'Reduce stamina of nearby rivals', 'stat_type' => 'Debuff', 'best_for' => 'Long‑distance lead‑holding', 'tag' => '⛔'],
            ['skill_name' => 'Speed Eater', 'description' => 'Reduce speed of opponents around you', 'stat_type' => 'Debuff', 'best_for' => 'Competitive pacing suppression', 'tag' => '⛔'],
            // --- Additional skills from plan_sample_data.sql ---
            ['skill_name' => '∴ Win Q.E.D. Lvl.2', 'description' => 'Placeholder', 'stat_type' => 'Special', 'best_for' => 'All', 'tag' => '⭐'],
            ['skill_name' => 'Wet Conditions ()', 'description' => 'Placeholder', 'stat_type' => 'Condition', 'best_for' => 'Wet tracks', 'tag' => '🌧'],
            ['skill_name' => 'Wet Conditions (())', 'description' => 'Placeholder', 'stat_type' => 'Condition', 'best_for' => 'Wet tracks', 'tag' => '🌧'],
            ['skill_name' => 'Outer Post Proficiency ()', 'description' => 'Placeholder', 'stat_type' => 'Position', 'best_for' => 'Outer post', 'tag' => '🅾️'],
            ['skill_name' => 'In Body and Mind', 'description' => 'Placeholder', 'stat_type' => 'Buff', 'best_for' => 'All', 'tag' => '🧠'],
            ['skill_name' => 'Tactical Tweak', 'description' => 'Placeholder', 'stat_type' => 'Tactic', 'best_for' => 'Mid-race', 'tag' => '🛠'],
            ['skill_name' => 'Xceleration Lvl. 2', 'description' => 'Placeholder', 'stat_type' => 'Acceleration', 'best_for' => 'All', 'tag' => '🔺'],
            ['skill_name' => 'Remove Wet Conditions x', 'description' => 'Placeholder', 'stat_type' => 'Condition', 'best_for' => 'Wet tracks', 'tag' => '❌'],
            ['skill_name' => 'Straightaway Recovery', 'description' => 'Placeholder', 'stat_type' => 'Recovery', 'best_for' => 'Straights', 'tag' => '🔋'],
            ['skill_name' => 'Lay Low', 'description' => 'Placeholder', 'stat_type' => 'Stamina', 'best_for' => 'Back pack', 'tag' => '🛌'],
            ['skill_name' => 'Nimble Navigator', 'description' => 'Placeholder', 'stat_type' => 'Position', 'best_for' => 'Navigation', 'tag' => '🧭'],
            ['skill_name' => 'Early Lead', 'description' => 'Placeholder', 'stat_type' => 'Speed', 'best_for' => 'Front', 'tag' => '🏁'],
            ['skill_name' => 'Final Push', 'description' => 'Placeholder', 'stat_type' => 'Acceleration', 'best_for' => 'Final corner', 'tag' => '🔺'],
            ['skill_name' => 'Slick Surge', 'description' => 'Placeholder', 'stat_type' => 'Acceleration', 'best_for' => 'Late race', 'tag' => '🔺'],
            ['skill_name' => 'Updrafters', 'description' => 'Placeholder', 'stat_type' => 'Acceleration', 'best_for' => 'Late surger', 'tag' => '🔺'],
            ['skill_name' => 'Steadfast', 'description' => 'Placeholder', 'stat_type' => 'Stamina', 'best_for' => 'All', 'tag' => '🔋'],
            ['skill_name' => 'Hesitant End Closers', 'description' => 'Placeholder', 'stat_type' => 'Debuff', 'best_for' => 'Late runners', 'tag' => '⛔'],
            ['skill_name' => 'Cut and Drive! Lvl. 1', 'description' => 'Placeholder', 'stat_type' => 'Unique', 'best_for' => 'Front-run', 'tag' => '⭐'],
            ['skill_name' => 'Focus', 'description' => 'Placeholder', 'stat_type' => 'Buff', 'best_for' => 'Early race', 'tag' => '🧠'],
            ['skill_name' => 'Updrafted', 'description' => 'Placeholder', 'stat_type' => 'Acceleration', 'best_for' => 'Late race', 'tag' => '🔺'],
            ['skill_name' => 'Late Surger Savvy ○', 'description' => 'Placeholder', 'stat_type' => 'Passive', 'best_for' => 'Late surger', 'tag' => '📊'],
            ['skill_name' => 'Late Surger Savvy ⦾', 'description' => 'Placeholder', 'stat_type' => 'Passive', 'best_for' => 'Late surger', 'tag' => '📊'],
            ['skill_name' => 'Fast-Paced', 'description' => 'Placeholder', 'stat_type' => 'Speed', 'best_for' => 'Mid-race', 'tag' => '🔺'],
            ['skill_name' => 'Unyielding Spirit', 'description' => 'Placeholder', 'stat_type' => 'Recovery', 'best_for' => 'Losing lead', 'tag' => '🔋'],
            ['skill_name' => 'Preferred Position', 'description' => 'Placeholder', 'stat_type' => 'Passive', 'best_for' => 'Mid-pack/front', 'tag' => '📊'],
            ['skill_name' => 'Prepared to Pass', 'description' => 'Placeholder', 'stat_type' => 'Buff', 'best_for' => 'Challenging', 'tag' => '🧠'],
            ['skill_name' => 'Deep Breaths', 'description' => 'Placeholder', 'stat_type' => 'Stamina', 'best_for' => 'Mid-race', 'tag' => '🔋'],
            ['skill_name' => 'Frenzied End Closers', 'description' => 'Placeholder', 'stat_type' => 'Debuff', 'best_for' => 'Late opponents', 'tag' => '⛔'],
            ['skill_name' => 'Soft Step', 'description' => 'Placeholder', 'stat_type' => 'Buff', 'best_for' => 'Ground agility', 'tag' => '🦶'],
            ['skill_name' => 'Summer Runner ○', 'description' => 'Placeholder', 'stat_type' => 'Passive', 'best_for' => 'Summer', 'tag' => '📊'],
            ['skill_name' => 'Rainy Days ○', 'description' => 'Placeholder', 'stat_type' => 'Passive', 'best_for' => 'Rain', 'tag' => '📊'],
            ['skill_name' => 'Beeline Burst', 'description' => 'Placeholder', 'stat_type' => 'Acceleration', 'best_for' => 'Mid-stretch', 'tag' => '🔺'],
            ['skill_name' => 'Pace Strategy', 'description' => 'Placeholder', 'stat_type' => 'Passive', 'best_for' => 'Pace', 'tag' => '📊'],
            ['skill_name' => 'Calm in a Crowd', 'description' => 'Placeholder', 'stat_type' => 'Buff', 'best_for' => 'Late game', 'tag' => '🧠'],
            ['skill_name' => 'Rosy Outlook', 'description' => 'Placeholder', 'stat_type' => 'Buff', 'best_for' => 'Morale', 'tag' => '🧠'],
            ['skill_name' => 'Subdued Front Runners', 'description' => 'Placeholder', 'stat_type' => 'Debuff', 'best_for' => 'Front runners', 'tag' => '⛔'],
            ['skill_name' => 'Meticulous Measures', 'description' => 'Placeholder', 'stat_type' => 'Buff', 'best_for' => 'All', 'tag' => '🧠'],
            ['skill_name' => '1,500,000 CC', 'description' => 'Placeholder', 'stat_type' => 'Buff', 'best_for' => 'Uphill', 'tag' => '🧠'],
            ['skill_name' => 'Corazón ☆ Ardiente', 'description' => 'Placeholder', 'stat_type' => 'Unique', 'best_for' => 'All', 'tag' => '⭐'],
            ['skill_name' => 'Hawkeye', 'description' => 'Placeholder', 'stat_type' => 'Buff', 'best_for' => 'Vision', 'tag' => '🦅'],
            ['skill_name' => 'Pace Chaser Straightaways ○', 'description' => 'Placeholder', 'stat_type' => 'Passive', 'best_for' => 'Pace chaser', 'tag' => '📊'],
            ['skill_name' => 'Pace Chaser Straightaways ⦾', 'description' => 'Placeholder', 'stat_type' => 'Passive', 'best_for' => 'Pace chaser', 'tag' => '📊'],
        ]);
    }
}
