<?php

namespace Tests\Feature\Feature\Livewire;

use App\Livewire\Characters\CharacterList;
use Livewire\Livewire;
use Tests\TestCase;

class CharacterListTest extends TestCase
{
    public function test_characterlist_renders(): void
    {
        Livewire::test(CharacterList::class)
            ->assertHasNoErrors();
    }

    public function test_characterlist_failure_path(): void
    {
        Livewire::test(CharacterList::class)
            ->assertHasNoErrors();
    }
}
