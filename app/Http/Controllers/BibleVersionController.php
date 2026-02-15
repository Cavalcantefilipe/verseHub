<?php

namespace App\Http\Controllers;

use App\Models\BibleVersion;
use App\Services\BibleVersionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BibleVersionController extends Controller
{
    public function __construct(
        protected BibleVersionService $bibleVersionService
    ) {}

    /**
     * Display a listing of Bible versions.
     */
    public function index(): JsonResponse
    {
        $versions = $this->bibleVersionService->getAllVersions();

        return response()->json([
            'data' => $versions,
            'message' => 'Bible versions retrieved successfully'
        ]);
    }

    /**
     * Store a newly created Bible version.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'slug' => 'required|string|unique:bible_versions,slug|max:10',
            'name' => 'required|string|max:255',
            'language' => 'required|string|max:10',
        ]);

        $version = $this->bibleVersionService->create($validated);

        return response()->json([
            'data' => $version,
            'message' => 'Bible version created successfully'
        ], 201);
    }

    /**
     * Display the specified Bible version.
     */
    public function show(BibleVersion $bibleVersion): JsonResponse
    {
        return response()->json([
            'data' => $bibleVersion->load(['verses' => function ($query) {
                $query->limit(10); // Limit to avoid too much data
            }]),
            'message' => 'Bible version retrieved successfully'
        ]);
    }

    /**
     * Update the specified Bible version.
     */
    public function update(Request $request, BibleVersion $bibleVersion): JsonResponse
    {
        $validated = $request->validate([
            'slug' => 'sometimes|string|unique:bible_versions,slug,' . $bibleVersion->id . '|max:10',
            'name' => 'sometimes|string|max:255',
            'language' => 'sometimes|string|max:10',
        ]);

        $version = $this->bibleVersionService->update($bibleVersion, $validated);

        return response()->json([
            'data' => $version,
            'message' => 'Bible version updated successfully'
        ]);
    }

    /**
     * Remove the specified Bible version.
     */
    public function destroy(BibleVersion $bibleVersion): JsonResponse
    {
        $this->bibleVersionService->delete($bibleVersion);

        return response()->json([
            'message' => 'Bible version deleted successfully'
        ]);
    }
}
