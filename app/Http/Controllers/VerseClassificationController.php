<?php

namespace App\Http\Controllers;

use App\Models\Verse;
use App\Models\Category;
use App\Services\VerseClassificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\PublicClassification;
use Illuminate\Support\Facades\Auth;

class VerseClassificationController extends Controller
{
    public function __construct(
        protected VerseClassificationService $classificationService
    ) {}

    /**
     * Get user's classifications.
     */
    public function index(Request $request): JsonResponse
    {
        $classifications = $this->classificationService->getUserClassifications(
            Auth::user(),
            $request->get('per_page', 20)
        );

        return response()->json([
            'data' => $classifications,
            'message' => 'User classifications retrieved successfully'
        ]);
    }

    /**
     * Classify a verse with a category.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'verse_id' => 'required|exists:verses,id',
            'category_id' => 'required|exists:categories,id',
            'confidence_level' => 'nullable|integer|min:1|max:5',
        ]);

        $verse = Verse::findOrFail($validated['verse_id']);
        $category = Category::findOrFail($validated['category_id']);

        $classification = $this->classificationService->classifyVerse(
            Auth::user(),
            $verse,
            $category,
            $validated['confidence_level'] ?? null
        );

        return response()->json([
            'data' => $classification->load(['verse.version', 'category']),
            'message' => 'Verse classified successfully'
        ], 201);
    }

    /**
     * Get classifications for a specific verse.
     */
    public function show(Verse $verse): JsonResponse
    {
        $classifications = $this->classificationService->getVerseClassifications($verse);

        return response()->json([
            'data' => $classifications,
            'message' => 'Verse classifications retrieved successfully'
        ]);
    }

    /**
     * Remove a classification.
     */
    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'verse_id' => 'required|exists:verses,id',
            'category_id' => 'required|exists:categories,id',
        ]);

        $verse = Verse::findOrFail($validated['verse_id']);
        $category = Category::findOrFail($validated['category_id']);

        $removed = $this->classificationService->removeClassification(
            Auth::user(),
            $verse,
            $category
        );

        if (!$removed) {
            return response()->json([
                'message' => 'Classification not found'
            ], 404);
        }

        return response()->json([
            'message' => 'Classification removed successfully'
        ]);
    }

    /**
     * Check if user has classified a verse in a category.
     */
    public function checkClassification(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'verse_id' => 'required|exists:verses,id',
            'category_id' => 'required|exists:categories,id',
        ]);

        $verse = Verse::findOrFail($validated['verse_id']);
        $category = Category::findOrFail($validated['category_id']);

        $hasClassified = $this->classificationService->hasUserClassified(
            Auth::user(),
            $verse,
            $category
        );

        return response()->json([
            'data' => [
                'has_classified' => $hasClassified
            ],
            'message' => 'Classification status retrieved successfully'
        ]);
    }

    /**
     * Authenticated classification (requires Google login).
     * Uses user_id from JWT token.
     * POST /api/classify-auth
     */
    public function classifyAuth(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reference' => 'required|string|max:255',
            'text' => 'required|string',
            'version' => 'required|string|max:10',
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'exists:categories,id',
        ]);

        $user = Auth::user();

        // Upsert: update if user already classified this reference, otherwise create
        $classification = PublicClassification::updateOrCreate(
            [
                'user_id' => $user->id,
                'reference' => $validated['reference'],
            ],
            [
                'device_id' => 'user-' . $user->id,
                'text' => $validated['text'],
                'version' => $validated['version'],
                'category_ids' => $validated['category_ids'],
            ]
        );

        $wasRecentlyCreated = $classification->wasRecentlyCreated;

        // Get category details
        $categories = Category::whereIn('id', $validated['category_ids'])->get();

        return response()->json([
            'data' => [
                'id' => $classification->id,
                'reference' => $classification->reference,
                'text' => $classification->text,
                'version' => $classification->version,
                'categories' => $categories,
                'created_at' => $classification->created_at,
                'updated' => !$wasRecentlyCreated,
            ],
            'message' => $wasRecentlyCreated
                ? 'Classification saved successfully'
                : 'Classification updated successfully'
        ], $wasRecentlyCreated ? 201 : 200);
    }

    /**
     * Get a user's existing classification for a specific reference.
     * GET /api/my-classification?reference=...
     */
    public function getByReference(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reference' => 'required|string|max:255',
        ]);

        $user = Auth::user();

        $classification = PublicClassification::where('user_id', $user->id)
            ->where('reference', $validated['reference'])
            ->first();

        if (!$classification) {
            return response()->json([
                'data' => null,
                'message' => 'No classification found for this reference'
            ]);
        }

        $categories = Category::whereIn('id', $classification->category_ids)->get();

        return response()->json([
            'data' => [
                'id' => $classification->id,
                'reference' => $classification->reference,
                'text' => $classification->text,
                'version' => $classification->version,
                'categories' => $categories,
                'category_ids' => $classification->category_ids,
                'created_at' => $classification->created_at,
            ],
            'message' => 'Classification found'
        ]);
    }

    /**
     * Get classifications by authenticated user.
     * GET /api/my-classifications-auth
     */
    public function getByUser(Request $request): JsonResponse
    {
        $user = Auth::user();

        $classifications = PublicClassification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($classification) {
                $categories = Category::whereIn('id', $classification->category_ids)->get();
                return [
                    'id' => $classification->id,
                    'reference' => $classification->reference,
                    'text' => $classification->text,
                    'version' => $classification->version,
                    'categories' => $categories,
                    'created_at' => $classification->created_at,
                ];
            });

        return response()->json([
            'data' => $classifications,
            'message' => 'User classifications retrieved successfully'
        ]);
    }

    /**
     * Public classification (no auth required).
     * Stores classification with device_id for identification.
     */
    public function classifyPublic(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => 'required|string|max:255',
            'reference' => 'required|string|max:255',
            'text' => 'required|string',
            'version' => 'required|string|max:10',
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'exists:categories,id',
        ]);

        $classification = PublicClassification::create([
            'device_id' => $validated['device_id'],
            'reference' => $validated['reference'],
            'text' => $validated['text'],
            'version' => $validated['version'],
            'category_ids' => $validated['category_ids'],
        ]);

        // Get category details
        $categories = Category::whereIn('id', $validated['category_ids'])->get();

        return response()->json([
            'data' => [
                'id' => $classification->id,
                'reference' => $classification->reference,
                'text' => $classification->text,
                'version' => $classification->version,
                'categories' => $categories,
                'created_at' => $classification->created_at,
            ],
            'message' => 'Classification saved successfully'
        ], 201);
    }

    /**
     * Get classifications by device ID.
     */
    public function getByDevice(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => 'required|string|max:255',
        ]);

        $classifications = PublicClassification::where('device_id', $validated['device_id'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($classification) {
                $categories = Category::whereIn('id', $classification->category_ids)->get();
                return [
                    'id' => $classification->id,
                    'reference' => $classification->reference,
                    'text' => $classification->text,
                    'version' => $classification->version,
                    'categories' => $categories,
                    'created_at' => $classification->created_at,
                ];
            });

        return response()->json([
            'data' => $classifications,
            'message' => 'Classifications retrieved successfully'
        ]);
    }

    /**
     * Get classification stats for a specific verse reference.
     * Returns how many people classified each category for this verse.
     */
    public function getVerseStats(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reference' => 'required|string|max:255',
        ]);

        $reference = $validated['reference'];

        // Get all classifications for this reference
        $classifications = PublicClassification::where('reference', $reference)->get();

        $totalClassifications = $classifications->count();

        // Count how many times each category was used
        $categoryCounts = [];
        foreach ($classifications as $classification) {
            $categoryIds = is_array($classification->category_ids)
                ? $classification->category_ids
                : json_decode($classification->category_ids, true) ?? [];

            foreach ($categoryIds as $catId) {
                if (!isset($categoryCounts[$catId])) {
                    $categoryCounts[$catId] = 0;
                }
                $categoryCounts[$catId]++;
            }
        }

        // Sort by count descending
        arsort($categoryCounts);

        // Get category details and build stats
        $categoryIds = array_keys($categoryCounts);
        $categories = Category::whereIn('id', $categoryIds)->get()->keyBy('id');

        $stats = [];
        foreach ($categoryCounts as $catId => $count) {
            $category = $categories->get($catId);
            if ($category) {
                $percentage = $totalClassifications > 0
                    ? round(($count / $totalClassifications) * 100, 1)
                    : 0;

                $stats[] = [
                    'category_id' => $category->id,
                    'category_name' => $category->name,
                    'category_icon' => $category->icon,
                    'category_color' => $category->color,
                    'count' => $count,
                    'percentage' => $percentage,
                ];
            }
        }

        return response()->json([
            'data' => [
                'reference' => $reference,
                'total_classifications' => $totalClassifications,
                'total_people' => $totalClassifications,
                'stats' => $stats,
            ],
            'message' => 'Verse stats retrieved successfully'
        ]);
    }

    /**
     * Community feed: all classified verses with top 3 categories each.
     * Supports filtering by category_id.
     */
    public function communityFeed(Request $request): JsonResponse
    {
        $categoryFilter = $request->get('category_id');

        // Get all public classifications grouped by reference
        $query = PublicClassification::query();

        // If filtering by category, only get classifications that include that category
        if ($categoryFilter) {
            $query->whereJsonContains('category_ids', (int) $categoryFilter);
        }

        $allClassifications = $query->orderBy('created_at', 'desc')->get();

        // Group by reference
        $grouped = $allClassifications->groupBy('reference');

        $feed = [];
        foreach ($grouped as $reference => $classifications) {
            $totalPeople = $classifications->count();

            // Get the first classification text/version as representative
            $first = $classifications->first();

            // Count categories across all classifications for this reference
            $categoryCounts = [];
            foreach ($classifications as $c) {
                $catIds = is_array($c->category_ids)
                    ? $c->category_ids
                    : (json_decode($c->category_ids, true) ?? []);

                foreach ($catIds as $catId) {
                    $categoryCounts[$catId] = ($categoryCounts[$catId] ?? 0) + 1;
                }
            }

            // Sort by count descending and take top 3
            arsort($categoryCounts);
            $top3Ids = array_slice(array_keys($categoryCounts), 0, 3, true);

            $categories = Category::whereIn('id', $top3Ids)->get()->keyBy('id');

            $topCategories = [];
            foreach ($top3Ids as $catId) {
                $cat = $categories->get($catId);
                if ($cat) {
                    $topCategories[] = [
                        'id' => $cat->id,
                        'name' => $cat->name,
                        'icon' => $cat->icon,
                        'color' => $cat->color,
                        'count' => $categoryCounts[$catId],
                        'percentage' => round(($categoryCounts[$catId] / $totalPeople) * 100, 1),
                    ];
                }
            }

            $feed[] = [
                'reference' => $reference,
                'text' => $first->text,
                'version' => $first->version,
                'total_people' => $totalPeople,
                'top_categories' => $topCategories,
                'last_classified_at' => $classifications->max('created_at'),
            ];
        }

        // Sort by total_people descending (most popular first)
        usort($feed, fn($a, $b) => $b['total_people'] <=> $a['total_people']);

        return response()->json([
            'data' => $feed,
            'message' => 'Community feed retrieved successfully',
            'meta' => [
                'total_verses' => count($feed),
            ],
        ]);
    }
}
