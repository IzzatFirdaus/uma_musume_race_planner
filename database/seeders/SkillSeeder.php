<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SkillReference;
use Illuminate\Database\Seeder;

/**
 * Skill Reference Seeder
 *
 * Seeds the skill_reference table with sample skills including bilingual names.
 * Implements REQ-SKILL-1.1: Bilingual search support.
 */
class SkillSeeder extends Seeder
{
    /**
     * Sample skills with English and Japanese names.
     */
    private const SKILLS = [
        [
            'skill_name' => 'Corner Recovery',
            'name_jp' => 'コーナー回復',
            'description' => 'Recovers stamina when running in corners',
            'stat_type' => 'Stamina',
            'tag' => '🏃',
        ],
        [
            'skill_name' => 'Straight Recovery',
            'name_jp' => '直線回復',
            'description' => 'Recovers stamina when running on straights',
            'stat_type' => 'Stamina',
            'tag' => '🏃',
        ],
        [
            'skill_name' => 'Speed Demon',
            'name_jp' => 'スピードデーモン',
            'description' => 'Increases speed in the final stretch',
            'stat_type' => 'Speed',
            'tag' => '⚡',
        ],
        [
            'skill_name' => 'Power Surge',
            'name_jp' => 'パワーサージ',
            'description' => 'Boosts power when overtaking',
            'stat_type' => 'Power',
            'tag' => '💪',
        ],
        [
            'skill_name' => 'Gutsy Spirit',
            'name_jp' => '根性スピリット',
            'description' => 'Increases guts in difficult situations',
            'stat_type' => 'Guts',
            'tag' => '🔥',
        ],
        [
            'skill_name' => 'Witty Strategy',
            'name_jp' => '賢い戦略',
            'description' => 'Improves wit for better positioning',
            'stat_type' => 'Wit',
            'tag' => '🧠',
        ],
        [
            'skill_name' => 'Early Acceleration',
            'name_jp' => '先行加速',
            'description' => 'Boosts acceleration at the start',
            'stat_type' => 'Speed',
            'tag' => '🚀',
        ],
        [
            'skill_name' => 'Endurance Master',
            'name_jp' => '持久力マスター',
            'description' => 'Greatly improves stamina retention',
            'stat_type' => 'Stamina',
            'tag' => '♾️',
        ],
        [
            'skill_name' => 'Final Sprint',
            'name_jp' => 'ラストスプリント',
            'description' => 'Massive speed boost in final stretch',
            'stat_type' => 'Speed',
            'tag' => '🏁',
        ],
        [
            'skill_name' => 'Tactical Mind',
            'name_jp' => '戦術的思考',
            'description' => 'Improves positioning and strategy',
            'stat_type' => 'Wit',
            'tag' => '🎯',
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::SKILLS as $skill) {
            SkillReference::firstOrCreate(
                ['skill_name' => $skill['skill_name']],
                [
                    'name_jp' => $skill['name_jp'],
                    'description' => $skill['description'],
                    'stat_type' => $skill['stat_type'],
                    'tag' => $skill['tag'],
                ]
            );
        }

        $this->command->info('Skill references seeded successfully.');
    }
}
