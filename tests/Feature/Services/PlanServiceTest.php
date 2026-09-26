<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Enums\StorageMode;
use App\Models\Condition;
use App\Models\Mood;
use App\Models\Plan;
use App\Models\Strategy;
use App\Models\Umamusume;
use App\Models\User;
use App\Services\PlanService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Tests\TestCase;

class PlanServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PlanService $planService;

    protected Umamusume $character;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => \Database\Seeders\LookupSeeder::class]);

        $this->planService = app(PlanService::class);
        $this->character = Umamusume::factory()->create([
            'name' => 'Special Week',
        ]);
    }

    public function test_create_quick_plan_in_local_mode_returns_payload(): void
    {
        $result = $this->planService->createQuickPlan([
            'title' => 'Special Week Training',
            'character_id' => $this->character->id,
            'storage_mode' => StorageMode::Local->value,
            'career_stage' => 'junior',
            'class' => 'beginner',
        ]);

        $this->assertSame(StorageMode::Local->value, $result['storage_mode']);
        $this->assertNull($result['plan']);
        $this->assertNotNull($result['local_payload']);
        $this->assertTrue(Str::isUuid($result['uuid']));
        $this->assertStringContainsString('/plans/local/', $result['redirect_url']);
        $this->assertSame('Special Week Training', $result['local_payload']['career_run']['plan_title']);
        $this->assertSame('Special Week', $result['local_payload']['career_run']['name']);
        $this->assertSame($this->character->id, $result['local_payload']['character_id']);
    }

    public function test_create_quick_plan_in_account_mode_persists_plan(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $result = $this->planService->createQuickPlan([
            'title' => 'Account Plan Title',
            'character_id' => $this->character->id,
            'storage_mode' => StorageMode::Account->value,
            'career_stage' => 'junior',
            'class' => 'beginner',
        ]);

        $this->assertSame(StorageMode::Account->value, $result['storage_mode']);
        $this->assertNotNull($result['plan']);
        $this->assertNull($result['local_payload']);
        $this->assertSame('Account Plan Title', $result['plan']->plan_title);
        $this->assertSame('Special Week', $result['plan']->name);
        $this->assertSame(StorageMode::Account, $result['plan']->storage_mode);
        $this->assertEquals(5, $result['plan']->attributes()->count());
        $this->assertStringContainsString('/plans/'.$result['plan']->id.'/edit', $result['redirect_url']);
    }

    public function test_create_quick_plan_account_mode_requires_authentication(): void
    {
        $this->expectException(AuthorizationException::class);

        $this->planService->createQuickPlan([
            'title' => 'Unauthorized Plan',
            'character_id' => $this->character->id,
            'storage_mode' => StorageMode::Account->value,
        ]);
    }

    public function test_get_plan_by_id_or_uuid_with_numeric_id(): void
    {
        $plan = Plan::create([
            'name' => 'Lookup Plan',
            'plan_title' => 'Lookup Plan',
            'career_stage' => 'junior',
            'class' => 'beginner',
            'race_name' => '',
            'status' => 'Planning',
            'mood_id' => Mood::query()->where('label', 'NORMAL')->first()->id,
            'condition_id' => Condition::query()->where('label', 'N/A')->first()->id,
            'strategy_id' => Strategy::query()->where('label', 'PACE')->first()->id,
        ]);

        $found = $this->planService->getPlanByIdOrUuid($plan->id);

        $this->assertTrue($found->is($plan));
    }

    public function test_get_plan_by_id_or_uuid_with_local_uuid(): void
    {
        $uuid = (string) Str::uuid();

        $plan = Plan::create([
            'name' => 'Local Lookup Plan',
            'plan_title' => 'Local Lookup Plan',
            'career_stage' => 'junior',
            'class' => 'beginner',
            'race_name' => '',
            'status' => 'Planning',
            'storage_mode' => StorageMode::Local,
            'local_uuid' => $uuid,
            'mood_id' => Mood::query()->where('label', 'NORMAL')->first()->id,
            'condition_id' => Condition::query()->where('label', 'N/A')->first()->id,
            'strategy_id' => Strategy::query()->where('label', 'PACE')->first()->id,
        ]);

        $found = $this->planService->getPlanByIdOrUuid($uuid);

        $this->assertTrue($found->is($plan));
    }

    public function test_get_plan_by_id_or_uuid_throws_when_missing(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->planService->getPlanByIdOrUuid((string) Str::uuid());
    }

    public function test_create_detailed_plan_successfully(): void
    {
        $request = Request::create('/test', 'POST');

        $validated = [
            'plan' => [
                'plan_title' => 'Detailed Test Plan',
                'name' => 'Test Horse Detailed',
                'career_stage' => 'senior',
                'class' => 'gold',
                'race_name' => 'Detailed Test Race',
                'status' => 'Planning',
                'mood_id' => Mood::first()->id,
                'condition_id' => Condition::first()->id,
                'strategy_id' => Strategy::first()->id,
            ],
            'attributes' => [
                ['attribute_name' => 'SPEED', 'value' => 500, 'grade' => 'B'],
                ['attribute_name' => 'STAMINA', 'value' => 400, 'grade' => 'C'],
            ],
            'skills' => [
                ['name' => 'Test Skill', 'sp_cost' => 100, 'acquired' => 'no', 'notes' => 'Test notes'],
            ],
        ];

        $plan = $this->planService->createDetailedPlan($request, $validated);

        $this->assertInstanceOf(Plan::class, $plan);
        $this->assertEquals('Detailed Test Plan', $plan->plan_title);
        $this->assertEquals('Test Horse Detailed', $plan->name);
        $this->assertEquals(2, $plan->attributes()->count());
        $this->assertEquals(1, $plan->skills()->count());
    }

    public function test_delete_plan_successfully(): void
    {
        $plan = Plan::create([
            'name' => 'Test Plan for Deletion',
            'plan_title' => 'Test Plan for Deletion',
            'career_stage' => 'senior',
            'class' => 'gold',
            'race_name' => 'Test Race',
            'status' => 'Planning',
            'storage_mode' => StorageMode::Account,
            'mood_id' => Mood::query()->where('label', 'NORMAL')->first()->id,
            'condition_id' => Condition::query()->where('label', 'N/A')->first()->id,
            'strategy_id' => Strategy::query()->where('label', 'PACE')->first()->id,
        ]);

        $planId = $plan->id;

        $this->planService->deletePlan($plan);

        $this->assertSoftDeleted('plans', ['id' => $planId]);
    }

    public function test_delete_local_plan_record_successfully(): void
    {
        $plan = Plan::create([
            'name' => 'Local Delete Plan',
            'plan_title' => 'Local Delete Plan',
            'career_stage' => 'junior',
            'class' => 'beginner',
            'race_name' => '',
            'status' => 'Planning',
            'storage_mode' => StorageMode::Local,
            'local_uuid' => (string) Str::uuid(),
            'mood_id' => Mood::query()->where('label', 'NORMAL')->first()->id,
            'condition_id' => Condition::query()->where('label', 'N/A')->first()->id,
            'strategy_id' => Strategy::query()->where('label', 'PACE')->first()->id,
        ]);

        $planId = $plan->id;

        $this->planService->deletePlan($plan);

        $this->assertSoftDeleted('plans', ['id' => $planId]);
    }
}
