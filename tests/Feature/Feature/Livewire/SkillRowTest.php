<?php

namespace Tests\Feature\Feature\Livewire;

use App\Livewire\Plans\SkillRow;
use Livewire\Livewire;
use Tests\TestCase;

class SkillRowTest extends TestCase
{
    /**
     * Component mounts without errors (happy path).
     */
    public function test_skillrow_renders(): void
    {
        Livewire::test(SkillRow::class)
            ->assertHasNoErrors();
    }

    /**
     * Failure path: mounting still returns no validation errors by default.
     */
    public function test_skillrow_failure_path(): void
    {
        Livewire::test(SkillRow::class)
            ->assertHasNoErrors();
    }
}
