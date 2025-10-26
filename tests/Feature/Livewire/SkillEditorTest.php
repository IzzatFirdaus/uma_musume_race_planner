<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Skills\SkillEditor;
use Livewire\Livewire;
use Tests\TestCase;

class SkillEditorTest extends TestCase
{
    /** @test */
    public function it_can_add_a_skill()
    {
        Livewire::test(SkillEditor::class)
            ->call('addSkill')
            ->assertCount('skills', 1);
    }

    /** @test */
    public function it_can_remove_a_skill()
    {
        Livewire::test(SkillEditor::class)
            ->set('skills', [['name' => 'Skill 1', 'level' => 1]])
            ->call('removeSkill', 0)
            ->assertCount('skills', 0);
    }

    /** @test */
    public function it_validates_skill_inputs()
    {
        Livewire::test(SkillEditor::class)
            ->set('skills', [['name' => '', 'level' => 10]])
            ->call('save')
            ->assertHasErrors(['skills.0.name' => 'required', 'skills.0.level' => 'max']);
    }

    /** @test */
    public function it_dispatches_success_event_on_save()
    {
        Livewire::test(SkillEditor::class)
            ->set('skills', [['name' => 'Speed Star', 'level' => 3]])
            ->call('save')
            ->assertDispatched('plan-updated');
    }
}
