<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Skills\SkillEditor;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SkillEditorTest extends TestCase
{
    #[Test]
    public function it_can_add_a_skill(): void
    {
        Livewire::test(SkillEditor::class)
            ->call('addSkill')
            ->assertCount('skills', 1);
    }

    #[Test]
    public function it_can_remove_a_skill(): void
    {
        Livewire::test(SkillEditor::class)
            ->set('skills', [['name' => 'Skill 1', 'level' => 1]])
            ->call('removeSkill', 0)
            ->assertCount('skills', 0);
    }

    #[Test]
    public function it_validates_skill_inputs(): void
    {
        Livewire::test(SkillEditor::class)
            ->set('skills', [['name' => '', 'level' => 10]])
            ->call('save')
            ->assertHasErrors(['skills.0.name' => 'required', 'skills.0.level' => 'max']);
    }

    #[Test]
    public function it_dispatches_success_event_on_save(): void
    {
        Livewire::test(SkillEditor::class)
            ->set('skills', [['name' => 'Speed Star', 'level' => 3]])
            ->call('save')
            ->assertDispatched('plan-updated');
    }
}
