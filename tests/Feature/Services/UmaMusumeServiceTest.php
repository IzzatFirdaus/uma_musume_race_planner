<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Models\Umamusume;
use App\Services\UmaMusumeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Feature tests for UmaMusumeService.
 * Task 6.1.2: Test service methods.
 */
class UmaMusumeServiceTest extends TestCase
{
    use RefreshDatabase;

    protected UmaMusumeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(UmaMusumeService::class);
    }

    public function test_create_character_successfully(): void
    {
        $data = [
            'name' => 'Special Week',
            'nickname' => 'Spe-chan',
            'team' => 'Spica',
            'rarity' => 3,
        ];

        $character = $this->service->create($data);

        $this->assertInstanceOf(Umamusume::class, $character);
        $this->assertEquals('Special Week', $character->name);
        $this->assertEquals('Spe-chan', $character->nickname);
        $this->assertEquals('Spica', $character->team);
        $this->assertEquals(3, $character->rarity);
        $this->assertNotNull($character->id);
    }

    public function test_create_character_with_custom_id(): void
    {
        $data = [
            'id' => 'custom-uuid-123',
            'name' => 'Silence Suzuka',
            'rarity' => 3,
        ];

        $character = $this->service->create($data);

        $this->assertEquals('custom-uuid-123', $character->id);
    }

    public function test_find_by_id_returns_character(): void
    {
        $created = $this->service->create([
            'name' => 'Tokai Teio',
            'rarity' => 3,
        ]);

        $found = $this->service->findById($created->id);

        $this->assertNotNull($found);
        $this->assertEquals('Tokai Teio', $found->name);
    }

    public function test_find_by_id_returns_null_for_nonexistent(): void
    {
        $found = $this->service->findById('nonexistent-id');

        $this->assertNull($found);
    }

    public function test_update_character_successfully(): void
    {
        $character = $this->service->create([
            'name' => 'Mejiro McQueen',
            'rarity' => 3,
        ]);

        $updated = $this->service->update($character, [
            'nickname' => 'McQueen',
            'team' => 'Rigil',
        ]);

        $this->assertEquals('McQueen', $updated->nickname);
        $this->assertEquals('Rigil', $updated->team);
        $this->assertEquals('Mejiro McQueen', $updated->name);
    }

    public function test_delete_character_successfully(): void
    {
        $character = $this->service->create([
            'name' => 'Rice Shower',
            'rarity' => 2,
        ]);

        $id = $character->id;
        $result = $this->service->delete($character);

        $this->assertTrue($result);
        $this->assertNull($this->service->findById($id));
    }

    public function test_search_by_name(): void
    {
        $this->service->create(['name' => 'Special Week', 'rarity' => 3]);
        $this->service->create(['name' => 'Silence Suzuka', 'rarity' => 3]);
        $this->service->create(['name' => 'Tokai Teio', 'rarity' => 3]);

        Cache::flush();

        $results = $this->service->search('Special');

        $this->assertCount(1, $results);
        $this->assertEquals('Special Week', $results->first()->name);
    }

    public function test_search_by_nickname(): void
    {
        $this->service->create([
            'name' => 'Special Week',
            'nickname' => 'Spe-chan',
            'rarity' => 3,
        ]);

        Cache::flush();

        $results = $this->service->search('Spe-chan');

        $this->assertCount(1, $results);
        $this->assertEquals('Special Week', $results->first()->name);
    }

    public function test_get_by_team(): void
    {
        $this->service->create(['name' => 'Special Week', 'team' => 'Spica', 'rarity' => 3]);
        $this->service->create(['name' => 'Silence Suzuka', 'team' => 'Spica', 'rarity' => 3]);
        $this->service->create(['name' => 'Mejiro McQueen', 'team' => 'Rigil', 'rarity' => 3]);

        $spicaMembers = $this->service->getByTeam('Spica');

        $this->assertCount(2, $spicaMembers);
    }

    public function test_get_by_rarity(): void
    {
        $this->service->create(['name' => 'Special Week', 'rarity' => 3]);
        $this->service->create(['name' => 'Rice Shower', 'rarity' => 2]);
        $this->service->create(['name' => 'Tokai Teio', 'rarity' => 3]);

        $threeStars = $this->service->getByRarity(3);

        $this->assertCount(2, $threeStars);
    }

    public function test_get_unique_teams(): void
    {
        $this->service->create(['name' => 'Special Week', 'team' => 'Spica', 'rarity' => 3]);
        $this->service->create(['name' => 'Mejiro McQueen', 'team' => 'Rigil', 'rarity' => 3]);
        $this->service->create(['name' => 'Vodka', 'team' => 'Spica', 'rarity' => 3]);

        $teams = $this->service->getUniqueTeams();

        $this->assertCount(2, $teams);
        $this->assertContains('Spica', $teams);
        $this->assertContains('Rigil', $teams);
    }

    public function test_get_statistics(): void
    {
        $this->service->create(['name' => 'Special Week', 'team' => 'Spica', 'rarity' => 3]);
        $this->service->create(['name' => 'Rice Shower', 'team' => 'Rigil', 'rarity' => 2]);
        $this->service->create(['name' => 'Tokai Teio', 'team' => 'Spica', 'rarity' => 3]);

        Cache::flush();

        $stats = $this->service->getStatistics();

        $this->assertEquals(3, $stats['total_characters']);
        $this->assertArrayHasKey('by_rarity', $stats);
        $this->assertArrayHasKey('by_team', $stats);
    }

    public function test_get_all_paginated(): void
    {
        for ($i = 1; $i <= 25; $i++) {
            $this->service->create(['name' => "Character {$i}", 'rarity' => 3]);
        }

        $paginated = $this->service->getAll(10);

        $this->assertEquals(10, $paginated->count());
        $this->assertEquals(25, $paginated->total());
        $this->assertEquals(3, $paginated->lastPage());
    }

    public function test_clear_cache(): void
    {
        Cache::put('umamusume_all', 'test_value', 3600);
        Cache::put('umamusume_statistics', 'test_stats', 3600);

        $this->service->clearCache();

        $this->assertNull(Cache::get('umamusume_all'));
        $this->assertNull(Cache::get('umamusume_statistics'));
    }
}
