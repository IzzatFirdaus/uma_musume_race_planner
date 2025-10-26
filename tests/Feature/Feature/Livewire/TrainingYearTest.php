<?php

namespace Tests\Feature\Feature\Livewire;

use App\Livewire\Plans\TrainingYear;
use Livewire\Livewire;
use Tests\TestCase;

class TrainingYearTest extends TestCase
{
    public function test_trainingyear_renders(): void
    {
        Livewire::test(TrainingYear::class)
            ->assertHasNoErrors();
    }

    public function test_trainingyear_failure_path(): void
    {
        Livewire::test(TrainingYear::class)
            ->assertHasNoErrors();
    }
}
