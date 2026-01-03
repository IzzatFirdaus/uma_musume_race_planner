<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Umamusume;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * UmaMusume Service Class
 *
 * Handles CRUD operations and business logic for Uma Musume characters.
 * Implements FR-1.1: Create, read, update, delete Uma Musume characters.
 */
class UmaMusumeService
{
    private const CACHE_DURATION = 3600;
    private const CACHE_KEY_ALL = 'umamusume_all';
    private const CACHE_KEY_SEARCH = 'umamusume_search_';

    /**
     * Get all characters with optional pagination.
     */
    public function getAll(int $perPage = 20): LengthAwarePaginator
    {
        return Umamusume::orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Get all characters as a collection (cached).
     */
    public function getAllCached(): Collection
    {
        return Cache::remember(self::CACHE_KEY_ALL, self::CACHE_DURATION, function () {
            return Umamusume::orderBy('name')->get();
        });
    }

    /**
     * Find a character by ID.
     */
    public function findById(string $id): ?Umamusume
    {
        return Umamusume::find($id);
    }

    /**
     * Find a character by ID or fail.
     */
    public function findOrFail(string $id): Umamusume
    {
        return Umamusume::findOrFail($id);
    }

    /**
     * Search characters by name (EN or JP).
     * Implements FR-4B.9: Character search uses same autocomplete patterns.
     */
    public function search(string $query, int $limit = 10): Collection
    {
        $cacheKey = self::CACHE_KEY_SEARCH . md5($query . $limit);

        return Cache::remember($cacheKey, 300, function () use ($query, $limit) {
            return Umamusume::where('name', 'LIKE', "%{$query}%")
                ->orWhere('nickname', 'LIKE', "%{$query}%")
                ->orderBy('name')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Create a new character.
     */
    public function create(array $data): Umamusume
    {
        if (!isset($data['id'])) {
            $data['id'] = Str::uuid()->toString();
        }

        $character = Umamusume::create($data);
        $this->clearCache();

        return $character;
    }

    /**
     * Update an existing character.
     */
    public function update(Umamusume $character, array $data): Umamusume
    {
        $character->update($data);
        $this->clearCache();

        return $character->fresh();
    }

    /**
     * Delete a character.
     */
    public function delete(Umamusume $character): bool
    {
        $this->deleteCharacterImages($character);
        $result = (bool) $character->delete();
        $this->clearCache();

        return $result;
    }

    /**
     * Get characters by team.
     */
    public function getByTeam(string $team): Collection
    {
        return Umamusume::where('team', $team)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get characters by rarity.
     */
    public function getByRarity(int $rarity): Collection
    {
        return Umamusume::where('rarity', $rarity)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get unique teams for filtering.
     */
    public function getUniqueTeams(): array
    {
        return Umamusume::whereNotNull('team')
            ->distinct()
            ->pluck('team')
            ->sort()
            ->values()
            ->toArray();
    }

    /**
     * Get character statistics.
     */
    public function getStatistics(): array
    {
        return Cache::remember('umamusume_statistics', self::CACHE_DURATION, function () {
            return [
                'total_characters' => Umamusume::count(),
                'by_rarity' => Umamusume::selectRaw('rarity, COUNT(*) as count')
                    ->groupBy('rarity')
                    ->pluck('count', 'rarity')
                    ->toArray(),
                'by_team' => Umamusume::selectRaw('team, COUNT(*) as count')
                    ->whereNotNull('team')
                    ->groupBy('team')
                    ->pluck('count', 'team')
                    ->toArray(),
            ];
        });
    }

    /**
     * Delete character images from storage.
     */
    private function deleteCharacterImages(Umamusume $character): void
    {
        $images = $character->images;
        if (!$images) {
            return;
        }

        $imagePaths = [];
        if (isset($images['portrait'])) {
            $imagePaths[] = $images['portrait'];
        }
        if (isset($images['thumbnail'])) {
            $imagePaths[] = $images['thumbnail'];
        }
        if (isset($images['full'])) {
            $imagePaths[] = $images['full'];
        }

        foreach ($imagePaths as $path) {
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    /**
     * Clear all character-related caches.
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY_ALL);
        Cache::forget('umamusume_statistics');
        // Note: Search cache keys are time-limited and will expire naturally
    }
}
