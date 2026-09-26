<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Enums\SkillStatus;
use App\Livewire\Skills\SkillEditor;
use App\Models\Condition;
use App\Models\Mood;
use App\Models\Plan;
use App\Models\SkillReference;
use App\Models\Strategy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Feature tests for SkillEditor Livewire component.
 * Implements REQ-SKILL-1.2, REQ-SKILL-1.3, REQ-SKILL-1.4, REQ-SKILL-1.5.
 */
class SkillEditorTest extends TestCase
{
    use RefreshDatabase;

    protected Plan $plan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => \Database\Seeders\LookupSeeder::class]);

        $this->plan = Plan::create([
            'name' => 'Test Horse',
            'plan_title' => 'Test Plan',
            'career_stage' => 'senior',
            'class' => 'gold',
            'race_name' => 'Test Race',
            'status' => 'Planning',
            'mood_id' => Mood::first()?->id,
            'condition_id' => Condition::first()?->id,
            'strategy_id' => Strategy::first()?->id,
        ]);
    }

    #[Test]
    public function it_can_add_a_skill(): void
    {
        Livewire::test(SkillEditor::class, ['plan' => $this->plan])
            ->call('addSkill')
            ->assertCount('skills', 1)
            ->assertSet('skills.0.status', SkillStatus::Suggested->value);
    }

    #[Test]
    public function it_can_remove_a_skill(): void
    {
        Livewire::test(SkillEditor::class, ['plan' => $this->plan])
            ->set('skills', [[
                'id' => null,
                'skill_reference_id' => null,
                'name' => 'Test Skill',
                'name_jp' => null,
                'status' => SkillStatus::Suggested->value,
                'turn_acquired' => null,
                'sp_cost' => null,
                'notes' => null,
                'tag' => null,
            ]])
            ->call('removeSkill', 0)
            ->assertCount('skills', 0);
    }

    #[Test]
    public function it_validates_turn_acquired_required_for_acquired_status(): void
    {
        // Implements REQ-SKILL-1.3: turn_acquired required when status is Acquired
        Livewire::test(SkillEditor::class, ['plan' => $this->plan])
            ->set('skills', [[
                'id' => null,
                'skill_reference_id' => null,
                'name' => 'Test Skill',
                'name_jp' => null,
                'status' => SkillStatus::Acquired->value,
                'turn_acquired' => null, // Missing turn_acquired
                'sp_cost' => 100,
                'notes' => null,
                'tag' => null,
            ]])
            ->call('save')
            ->assertHasErrors(['skills.0.turn_acquired' => 'required_if']);
    }

    #[Test]
    public function it_validates_turn_acquired_range(): void
    {
        Livewire::test(SkillEditor::class, ['plan' => $this->plan])
            ->set('skills', [[
                'id' => null,
                'skill_reference_id' => null,
                'name' => 'Test Skill',
                'name_jp' => null,
                'status' => SkillStatus::Acquired->value,
                'turn_acquired' => 100, // Exceeds max of 78
                'sp_cost' => 100,
                'notes' => null,
                'tag' => null,
            ]])
            ->call('save')
            ->assertHasErrors(['skills.0.turn_acquired' => 'max']);
    }

    #[Test]
    public function it_clears_turn_acquired_when_status_not_acquired(): void
    {
        Livewire::test(SkillEditor::class, ['plan' => $this->plan])
            ->set('skills', [[
                'id' => null,
                'skill_reference_id' => null,
                'name' => 'Test Skill',
                'name_jp' => null,
                'status' => SkillStatus::Acquired->value,
                'turn_acquired' => 15,
                'sp_cost' => 100,
                'notes' => null,
                'tag' => null,
            ]])
            ->call('updateStatus', 0, SkillStatus::Skipped->value)
            ->assertSet('skills.0.turn_acquired', null);
    }

    #[Test]
    public function it_handles_skill_selected_event(): void
    {
        $skillRef = SkillReference::create([
            'skill_name' => 'Speed Star',
            'name_jp' => 'スピードスター',
            'description' => 'Increases speed',
            'tag' => '⚡',
        ]);

        Livewire::test(SkillEditor::class, ['plan' => $this->plan])
            ->dispatch('skill-selected', [
                'id' => $skillRef->id,
                'name' => 'Speed Star',
                'name_jp' => 'スピードスター',
                'tag' => '⚡',
            ])
            ->assertCount('skills', 1)
            ->assertSet('skills.0.name', 'Speed Star')
            ->assertSet('skills.0.skill_reference_id', $skillRef->id);
    }

    #[Test]
    public function it_dispatches_success_event_on_save(): void
    {
        Livewire::test(SkillEditor::class, ['plan' => $this->plan])
            ->set('skills', [[
                'id' => null,
                'skill_reference_id' => null,
                'name' => 'Speed Star',
                'name_jp' => null,
                'status' => SkillStatus::Suggested->value,
                'turn_acquired' => null,
                'sp_cost' => 100,
                'notes' => null,
                'tag' => null,
            ]])
            ->call('save')
            ->assertDispatched('toast', type: 'success');
    }
}
