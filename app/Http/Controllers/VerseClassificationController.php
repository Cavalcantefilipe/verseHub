<?php

namespace App\Http\Controllers;

use App\Models\BibleVerse;
use App\Models\UserVerseCategory;
use App\Models\Verse;
use App\Models\Category;
use App\Services\VerseClassificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
     *
     * Uses the scalable bible_verses + user_verse_categories structure.
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

        // 1. Find or create the bible verse
        $bibleVerse = BibleVerse::findOrCreateByReference(
            $validated['reference'],
            $validated['version'],
            $validated['text']
        );

        // 2. Sync categories: remove old ones, add new ones
        $existedBefore = UserVerseCategory::where('user_id', $user->id)
            ->where('bible_verse_id', $bibleVerse->id)
            ->exists();

        DB::transaction(function () use ($user, $bibleVerse, $validated) {
            // Delete existing categories for this user+verse
            UserVerseCategory::where('user_id', $user->id)
                ->where('bible_verse_id', $bibleVerse->id)
                ->delete();

            // Insert new categories
            foreach ($validated['category_ids'] as $categoryId) {
                UserVerseCategory::create([
                    'user_id' => $user->id,
                    'bible_verse_id' => $bibleVerse->id,
                    'category_id' => $categoryId,
                ]);
            }
        });

        // Get category details for response
        $categories = Category::whereIn('id', $validated['category_ids'])->get();

        return response()->json([
            'data' => [
                'id' => $bibleVerse->id,
                'reference' => $bibleVerse->reference,
                'text' => $bibleVerse->text,
                'version' => $bibleVerse->version,
                'categories' => $categories,
                'created_at' => $bibleVerse->created_at,
                'updated' => $existedBefore,
            ],
            'message' => $existedBefore
                ? 'Classification updated successfully'
                : 'Classification saved successfully'
        ], $existedBefore ? 200 : 201);
    }

    /**
     * Get a user's existing classification for a specific reference.
     * GET /api/my-classification?reference=...
     *
     * Uses the scalable structure.
     */
    public function getByReference(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reference' => 'required|string|max:255',
        ]);

        $user = Auth::user();

        // Find the bible verse matching this reference
        $bibleVerse = BibleVerse::where('reference', $validated['reference'])->first();

        if (!$bibleVerse) {
            return response()->json([
                'data' => null,
                'message' => 'No classification found for this reference'
            ]);
        }

        // Get user's category classifications for this verse
        $userCategoryIds = UserVerseCategory::where('user_id', $user->id)
            ->where('bible_verse_id', $bibleVerse->id)
            ->pluck('category_id')
            ->toArray();

        if (empty($userCategoryIds)) {
            return response()->json([
                'data' => null,
                'message' => 'No classification found for this reference'
            ]);
        }

        $categories = Category::whereIn('id', $userCategoryIds)->get();

        return response()->json([
            'data' => [
                'id' => $bibleVerse->id,
                'reference' => $bibleVerse->reference,
                'text' => $bibleVerse->text,
                'version' => $bibleVerse->version,
                'categories' => $categories,
                'category_ids' => $userCategoryIds,
                'created_at' => $bibleVerse->created_at,
            ],
            'message' => 'Classification found'
        ]);
    }

    /**
     * Get classifications by authenticated user.
     * GET /api/my-classifications-auth
     *
     * Uses the scalable structure.
     */
    public function getByUser(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Get distinct bible_verse_ids classified by this user, ordered by latest
        $verseIds = UserVerseCategory::where('user_id', $user->id)
            ->select('bible_verse_id')
            ->selectRaw('MAX(created_at) as latest_at')
            ->groupBy('bible_verse_id')
            ->orderByDesc('latest_at')
            ->pluck('bible_verse_id');

        $bibleVerses = BibleVerse::whereIn('id', $verseIds)->get()->keyBy('id');

        // Preload all categories for user in one query
        $allUserCategories = UserVerseCategory::where('user_id', $user->id)
            ->whereIn('bible_verse_id', $verseIds)
            ->get()
            ->groupBy('bible_verse_id');

        $allCategoryIds = $allUserCategories->flatten()->pluck('category_id')->unique();
        $categoriesMap = Category::whereIn('id', $allCategoryIds)->get()->keyBy('id');

        $classifications = $verseIds->map(function ($verseId) use ($bibleVerses, $allUserCategories, $categoriesMap) {
            $bibleVerse = $bibleVerses->get($verseId);
            if (!$bibleVerse) return null;

            $categoryIds = $allUserCategories->get($verseId, collect())->pluck('category_id');
            $categories = $categoryIds->map(fn($id) => $categoriesMap->get($id))->filter()->values();

            return [
                'id' => $bibleVerse->id,
                'reference' => $bibleVerse->reference,
                'text' => $bibleVerse->text,
                'version' => $bibleVerse->version,
                'categories' => $categories,
                'created_at' => $bibleVerse->created_at,
            ];
        })->filter()->values();

        return response()->json([
            'data' => $classifications,
            'message' => 'User classifications retrieved successfully'
        ]);
    }

    /**
     * Get classification stats for a specific verse reference.
     * Returns how many people classified each category for this verse.
     *
     * Uses the scalable structure with proper SQL aggregation.
     */
    public function getVerseStats(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reference' => 'required|string|max:255',
        ]);

        $reference = $validated['reference'];

        // Find bible verse
        $bibleVerse = BibleVerse::where('reference', $reference)->first();

        if (!$bibleVerse) {
            return response()->json([
                'data' => [
                    'reference' => $reference,
                    'total_classifications' => 0,
                    'total_people' => 0,
                    'stats' => [],
                ],
                'message' => 'Verse stats retrieved successfully'
            ]);
        }

        // Count unique users who classified this verse
        $verseClassifications = UserVerseCategory::where('bible_verse_id', $bibleVerse->id)
            ->whereNotNull('user_id')
            ->get();
        $totalPeople = $verseClassifications->pluck('user_id')->unique()->count();

        // Get category counts using proper SQL aggregation
        $categoryCounts = UserVerseCategory::where('bible_verse_id', $bibleVerse->id)
            ->select('category_id', DB::raw('COUNT(*) as count'))
            ->groupBy('category_id')
            ->orderByDesc('count')
            ->get();

        // Get category details
        $categoryIds = $categoryCounts->pluck('category_id')->toArray();
        $categories = Category::whereIn('id', $categoryIds)->get()->keyBy('id');

        $stats = $categoryCounts->map(function ($row) use ($categories, $totalPeople) {
            $category = $categories->get($row->category_id);
            if (!$category) return null;

            return [
                'category_id' => $category->id,
                'category_name' => $category->name,
                'category_icon' => $category->icon,
                'category_color' => $category->color,
                'count' => $row->count,
                'percentage' => $totalPeople > 0
                    ? round(($row->count / $totalPeople) * 100, 1)
                    : 0,
            ];
        })->filter()->values()->toArray();

        return response()->json([
            'data' => [
                'reference' => $reference,
                'total_classifications' => $categoryCounts->sum('count'),
                'total_people' => $totalPeople,
                'stats' => $stats,
            ],
            'message' => 'Verse stats retrieved successfully'
        ]);
    }

    /**
     * Community feed: all classified verses with top 3 categories each.
     * Supports filtering by category_id.
     *
     * Uses the scalable structure with proper SQL queries.
     */
    public function communityFeed(Request $request): JsonResponse
    {
        $categoryFilter = $request->get('category_id');

        // Get distinct verse IDs, optionally filtered by category
        $query = UserVerseCategory::query()->select('bible_verse_id')->distinct();

        if ($categoryFilter) {
            $query->where('category_id', (int) $categoryFilter);
        }

        $verseIds = $query->pluck('bible_verse_id');

        if ($verseIds->isEmpty()) {
            return response()->json([
                'data' => [],
                'message' => 'Community feed retrieved successfully',
                'meta' => ['total_verses' => 0],
            ]);
        }

        // Load all bible verses
        $bibleVerses = BibleVerse::whereIn('id', $verseIds)->get()->keyBy('id');

        // Get all classifications for these verses in one query
        $allClassifications = UserVerseCategory::whereIn('bible_verse_id', $verseIds)->get();

        // Preload all relevant categories
        $allCategoryIds = $allClassifications->pluck('category_id')->unique();
        $categoriesMap = Category::whereIn('id', $allCategoryIds)->get()->keyBy('id');

        $feed = [];
        $grouped = $allClassifications->groupBy('bible_verse_id');

        foreach ($grouped as $verseId => $verseClassifications) {
            $bibleVerse = $bibleVerses->get($verseId);
            if (!$bibleVerse) continue;

            // Count unique people
            $uniquePeople = $verseClassifications
                ->map(fn($c) => $c->user_id ? 'u' . $c->user_id : 'd' . $c->device_id)
                ->unique()
                ->count();

            // Count categories
            $categoryCounts = $verseClassifications
                ->groupBy('category_id')
                ->map(fn($group) => $group->count())
                ->sortDesc();

            // Top 3 categories
            $topCategories = [];
            foreach ($categoryCounts->take(3) as $catId => $count) {
                $cat = $categoriesMap->get($catId);
                if ($cat) {
                    $topCategories[] = [
                        'id' => $cat->id,
                        'name' => $cat->name,
                        'icon' => $cat->icon,
                        'color' => $cat->color,
                        'count' => $count,
                        'percentage' => $uniquePeople > 0
                            ? round(($count / $uniquePeople) * 100, 1)
                            : 0,
                    ];
                }
            }

            $feed[] = [
                'reference' => $bibleVerse->reference,
                'text' => $bibleVerse->text,
                'version' => $bibleVerse->version,
                'total_people' => $uniquePeople,
                'top_categories' => $topCategories,
                'last_classified_at' => $verseClassifications->max('created_at'),
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
