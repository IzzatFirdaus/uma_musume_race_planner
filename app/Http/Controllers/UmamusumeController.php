<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Umamusume;

class UmamusumeController extends Controller
{
    /**
     * Display the Umamusume roster page.
     */
    public function index()
    {
        $umamusume = Umamusume::orderBy('name')->get();

        return view('characters', compact('umamusume'));
    }

    /**
     * Get a specific Umamusume by ID for API calls.
     */
    public function show(string $id)
    {
        $umamusume = Umamusume::where('id', $id)->first();

        if (! $umamusume) {
            return response()->json(['error' => 'Umamusume not found'], 404);
        }

        return response()->json($umamusume);
    }

    /**
     * Get all Umamusume for API calls.
     */
    public function apiIndex()
    {
        $umamusume = Umamusume::orderBy('name')->get();

        return response()->json($umamusume);
    }
}
