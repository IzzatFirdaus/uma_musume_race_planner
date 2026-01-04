<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\UmaMusumeCollection;
use App\Http\Resources\Api\V1\UmaMusumeResource;
use App\Services\UmaMusumeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * UmaMusume API Controller
 *
 * Handles CRUD operations for Uma Musume characters via API.
 * Implements API Design requirements from design.md.
 */
class UmaMusumeController extends Controller
{
    public function __construct(
        private readonly UmaMusumeService $umaMusumeService
    ) {}

    /**
     * Display a listing of all characters.
     * GET /api/v1/uma-musume
     */
    public function index(Request $request): UmaMusumeCollection
    {
        $perPage = min((int) $request->input('per_page', 20), 100);
        $characters = $this->umaMusumeService->getAll($perPage);

        return new UmaMusumeCollection($characters);
    }

    /**
     * Store a newly created character.
     * POST /api/v1/umamusume
     */
    public function store(Request $request): UmaMusumeResource|JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nickname' => 'nullable|string|max:255',
            'team' => 'nullable|string|max:255',
            'release_batch' => 'nullable|string|max:100',
            'cv' => 'nullable|string|max:255',
            'birthday' => 'nullable|string|max:50',
            'height_cm' => 'nullable|integer|min:100|max:250',
            'weight' => 'nullable|string|max:50',
            'three_sizes' => 'nullable|array',
            'images' => 'nullable|array',
            'rarity' => 'nullable|integer|min:1|max:5',
            'growth_rates' => 'nullable|array',
            'aptitudes' => 'nullable|array',
            'base_stats' => 'nullable|array',
            'unique_skill' => 'nullable|array',
            'skills' => 'nullable|array',
            'career_goals' => 'nullable|array',
            'tags' => 'nullable|array',
            'ui' => 'nullable|array',
            'links' => 'nullable|array',
        ]);

        // Generate UUID if not provided
        if (! isset($validated['id'])) {
            $validated['id'] = Str::uuid()->toString();
        }

        $character = $this->umaMusumeService->create($validated);

        return (new UmaMusumeResource($character))->response()->setStatusCode(201);
    }

    /**
     * Display the specified character.
     * GET /api/v1/uma-musume/{id}
     */
    public function show(string $id): UmaMusumeResource|JsonResponse
    {
        $character = $this->umaMusumeService->findById($id);

        if (! $character) {
            return response()->json([
                'error' => 'Character not found',
                'message' => "No Uma Musume found with ID: {$id}",
            ], 404);
        }

        return new UmaMusumeResource($character);
    }

    /**
     * Update the specified character.
     * PUT /api/v1/uma-musume/{id}
     */
    public function update(Request $request, string $id): UmaMusumeResource|JsonResponse
    {
        $character = $this->umaMusumeService->findById($id);

        if (! $character) {
            return response()->json([
                'error' => 'Character not found',
                'message' => "No Uma Musume found with ID: {$id}",
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'nickname' => 'nullable|string|max:255',
            'team' => 'nullable|string|max:255',
            'release_batch' => 'nullable|string|max:100',
            'cv' => 'nullable|string|max:255',
            'birthday' => 'nullable|string|max:50',
            'height_cm' => 'nullable|integer|min:100|max:250',
            'weight' => 'nullable|string|max:50',
            'three_sizes' => 'nullable|array',
            'images' => 'nullable|array',
            'rarity' => 'nullable|integer|min:1|max:5',
            'growth_rates' => 'nullable|array',
            'aptitudes' => 'nullable|array',
            'base_stats' => 'nullable|array',
            'unique_skill' => 'nullable|array',
            'skills' => 'nullable|array',
            'career_goals' => 'nullable|array',
            'tags' => 'nullable|array',
            'ui' => 'nullable|array',
            'links' => 'nullable|array',
        ]);

        $character = $this->umaMusumeService->update($character, $validated);

        return new UmaMusumeResource($character);
    }

    /**
     * Remove the specified character.
     * DELETE /api/v1/uma-musume/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        $character = $this->umaMusumeService->findById($id);

        if (! $character) {
            return response()->json([
                'error' => 'Character not found',
                'message' => "No Uma Musume found with ID: {$id}",
            ], 404);
        }

        $this->umaMusumeService->delete($character);

        return response()->json(null, 204);
    }

    /**
     * Search characters by name.
     * GET /api/v1/umamusume/search?q={query}
     */
    public function search(Request $request): JsonResponse
    {
        // Validate query parameter - return 422 with proper structure if missing
        if (! $request->has('q') || $request->input('q') === null || $request->input('q') === '') {
            return response()->json([
                'message' => 'The q field is required.',
                'errors' => ['q' => ['The q field is required.']],
            ], 422);
        }

        $validated = $request->validate([
            'q' => 'required|string|min:1|max:100',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $query = $validated['q'];
        $limit = $validated['limit'] ?? 10;

        $characters = $this->umaMusumeService->search($query, $limit);

        return response()->json([
            'data' => UmaMusumeResource::collection($characters),
            'meta' => [
                'query' => $query,
                'count' => $characters->count(),
                'cached' => true,
            ],
        ]);
    }
}
