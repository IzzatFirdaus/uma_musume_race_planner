<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Umamusume;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for UmaMusume API endpoints.
 * Task 6.2.1: Test API functionality.
 */
class UmaMusumeResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_paginated_characters(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            Umamusume::create([
                'id' => "char-{$i}",
                'name' => "Character {$i}",
                'rarity' => 3,
            ]);
        }

        $response = $this->getJson('/api/v1/umamusume');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'nickname',
                        'team',
                        'rarity',
                    ],
                ],
                'meta',
                'links',
            ]);
    }

    public function test_show_returns_single_character(): void
    {
        $character = Umamusume::create([
            'id' => 'test-char-1',
            'name' => 'Special Week',
            'nickname' => 'Spe-chan',
            'team' => 'Spica',
            'rarity' => 3,
        ]);

        $response = $this->getJson("/api/v1/umamusume/{$character->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => 'test-char-1',
                    'name' => 'Special Week',
                    'nickname' => 'Spe-chan',
                    'team' => 'Spica',
                    'rarity' => 3,
                ],
            ]);
    }

    public function test_show_returns_404_for_nonexistent_character(): void
    {
        $response = $this->getJson('/api/v1/umamusume/nonexistent-id');

        $response->assertStatus(404);
    }

    public function test_store_creates_new_character(): void
    {
        $data = [
            'name' => 'Silence Suzuka',
            'nickname' => 'Suzuka',
            'team' => 'Spica',
            'rarity' => 3,
        ];

        $response = $this->postJson('/api/v1/umamusume', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'nickname',
                    'team',
                    'rarity',
                ],
            ]);

        $this->assertDatabaseHas('umamusume', [
            'name' => 'Silence Suzuka',
            'nickname' => 'Suzuka',
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->postJson('/api/v1/umamusume', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_update_modifies_character(): void
    {
        $character = Umamusume::create([
            'id' => 'update-test',
            'name' => 'Original Name',
            'rarity' => 2,
        ]);

        $response = $this->putJson("/api/v1/umamusume/{$character->id}", [
            'name' => 'Updated Name',
            'nickname' => 'New Nickname',
            'rarity' => 3,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => 'Updated Name',
                    'nickname' => 'New Nickname',
                    'rarity' => 3,
                ],
            ]);
    }

    public function test_destroy_deletes_character(): void
    {
        $character = Umamusume::create([
            'id' => 'delete-test',
            'name' => 'To Be Deleted',
            'rarity' => 1,
        ]);

        $response = $this->deleteJson("/api/v1/umamusume/{$character->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('umamusume', ['id' => 'delete-test']);
    }

    public function test_search_finds_characters_by_name(): void
    {
        Umamusume::create([
            'id' => 'search-1',
            'name' => 'Special Week',
            'rarity' => 3,
        ]);
        Umamusume::create([
            'id' => 'search-2',
            'name' => 'Silence Suzuka',
            'rarity' => 3,
        ]);

        $response = $this->getJson('/api/v1/umamusume/search?q=Special');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_search_requires_query_parameter(): void
    {
        $response = $this->getJson('/api/v1/umamusume/search');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['q']);
    }
}
