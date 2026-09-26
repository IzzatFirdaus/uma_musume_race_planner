<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Enums\StorageMode;
use App\Livewire\QuickCreatePlan;
use App\Models\Umamusume;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class QuickCreatePlanTest extends TestCase
{
    use RefreshDatabase;

    protected Umamusume $character;

    protected function setUp(): void
    {
        parent::setUp();

        $this->character = Umamusume::factory()->create([
            'name' => 'Special Week',
        ]);
    }

    public function test_modal_opens_with_default_values(): void
    {
        Livewire::test(QuickCreatePlan::class)
            ->call('openModal')
            ->assertSet('showModal', true)
            ->assertSet('storageMode', StorageMode::Local->value)
            ->assertSet('careerStage', 'junior');
    }

    public function test_validation_requires_title_character_and_storage_mode(): void
    {
        Livewire::test(QuickCreatePlan::class)
            ->set('title', '')
            ->set('characterId', null)
            ->call('save')
            ->assertHasErrors(['title', 'characterId']);
    }

    public function test_save_dispatches_local_plan_event_for_local_mode(): void
    {
        Livewire::test(QuickCreatePlan::class)
            ->set('title', 'Local Training Plan')
            ->set('characterId', $this->character->id)
            ->set('storageMode', StorageMode::Local->value)
            ->call('save')
            ->assertDispatched('create-local-plan')
            ->assertSet('showModal', false);
    }

    public function test_save_redirects_for_account_mode_when_authenticated(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->artisan('db:seed', ['--class' => \Database\Seeders\LookupSeeder::class]);

        Livewire::test(QuickCreatePlan::class)
            ->set('title', 'Account Training Plan')
            ->set('characterId', $this->character->id)
            ->set('storageMode', StorageMode::Account->value)
            ->call('save')
            ->assertRedirect();
    }

    public function test_account_mode_requires_authentication(): void
    {
        Livewire::test(QuickCreatePlan::class)
            ->set('title', 'Account Training Plan')
            ->set('characterId', $this->character->id)
            ->set('storageMode', StorageMode::Account->value)
            ->call('save')
            ->assertHasErrors(['storageMode']);
    }
}
