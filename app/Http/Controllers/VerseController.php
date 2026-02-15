<?php

namespace App\Http\Controllers;

use App\Models\Verse;
use App\Models\Category;
use App\Services\VerseService;
use App\Services\BibleApiService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VerseController extends Controller
{
    public function __construct(
        protected VerseService $verseService,
        protected BibleApiService $bibleApiService
    ) {}

    /**
     * Display a listing of verses.
     */
    public function index(Request $request): JsonResponse
    {
        // This could be extended with filters, pagination, etc.
        $verses = Verse::with(['version', 'classifications.category'])
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'data' => $verses,
            'message' => 'Verses retrieved successfully'
        ]);
    }

    /**
     * Store a newly created verse reference.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'book' => 'required|string|max:50',
            'chapter' => 'required|integer|min:1',
            'verse_start' => 'required|integer|min:1',
            'verse_end' => 'nullable|integer|min:1',
            'version_id' => 'required|exists:bible_versions,id',
            'external_id' => 'required|string|max:255',
        ]);

        $verse = $this->verseService->createVerse($validated);

        return response()->json([
            'data' => $verse->load('version'),
            'message' => 'Verse reference created successfully'
        ], 201);
    }

    /**
     * Display the specified verse with text from API.
     */
    public function show(Verse $verse): JsonResponse
    {
        $verse->load(['version', 'classifications.category', 'stats']);

        // Parse book abbreviation from external_id or book name
        $bookAbbrev = $this->getBookAbbreviation($verse->book);

        // Fetch verse text from external API
        $verseData = $this->bibleApiService->getVerse(
            $verse->version->slug,
            $bookAbbrev,
            $verse->chapter,
            $verse->verse_start,
            $verse->verse_end
        );

        return response()->json([
            'data' => [
                'verse' => $verse,
                'text' => $verseData['verses'] ?? null,
                'reference' => $verse->getReference(),
                'book_info' => $verseData['book'] ?? null,
            ],
            'message' => 'Verse retrieved successfully'
        ]);
    }

    /**
     * Get book abbreviation from book name.
     */
    private function getBookAbbreviation(string $bookName): string
    {
        // Map common book names to abbreviations
        $bookMap = [
            'genesis' => 'gn',
            'gênesis' => 'gn',
            'exodus' => 'ex',
            'êxodo' => 'ex',
            'leviticus' => 'lv',
            'levítico' => 'lv',
            'numbers' => 'nm',
            'números' => 'nm',
            'deuteronomy' => 'dt',
            'deuteronômio' => 'dt',
            'joshua' => 'js',
            'josué' => 'js',
            'judges' => 'jz',
            'juízes' => 'jz',
            'ruth' => 'rt',
            'rute' => 'rt',
            '1 samuel' => '1sm',
            '1samuel' => '1sm',
            '2 samuel' => '2sm',
            '2samuel' => '2sm',
            '1 kings' => '1rs',
            '1 reis' => '1rs',
            '1reis' => '1rs',
            '2 kings' => '2rs',
            '2 reis' => '2rs',
            '2reis' => '2rs',
            'psalms' => 'sl',
            'salmos' => 'sl',
            'proverbs' => 'pv',
            'provérbios' => 'pv',
            'isaiah' => 'is',
            'isaías' => 'is',
            'jeremiah' => 'jr',
            'jeremias' => 'jr',
            'matthew' => 'mt',
            'mateus' => 'mt',
            'mark' => 'mc',
            'marcos' => 'mc',
            'luke' => 'lc',
            'lucas' => 'lc',
            'john' => 'jo',
            'joão' => 'jo',
            'acts' => 'at',
            'atos' => 'at',
            'romans' => 'rm',
            'romanos' => 'rm',
            '1 corinthians' => '1co',
            '1 coríntios' => '1co',
            '1coríntios' => '1co',
            '2 corinthians' => '2co',
            '2 coríntios' => '2co',
            '2coríntios' => '2co',
            'galatians' => 'gl',
            'gálatas' => 'gl',
            'ephesians' => 'ef',
            'efésios' => 'ef',
            'philippians' => 'fp',
            'filipenses' => 'fp',
            'colossians' => 'cl',
            'colossenses' => 'cl',
            '1 thessalonians' => '1ts',
            '1 tessalonicenses' => '1ts',
            '2 thessalonians' => '2ts',
            '2 tessalonicenses' => '2ts',
            'hebrews' => 'hb',
            'hebreus' => 'hb',
            'james' => 'tg',
            'tiago' => 'tg',
            '1 peter' => '1pe',
            '1 pedro' => '1pe',
            '2 peter' => '2pe',
            '2 pedro' => '2pe',
            '1 john' => '1jo',
            '1 joão' => '1jo',
            '2 john' => '2jo',
            '2 joão' => '2jo',
            '3 john' => '3jo',
            '3 joão' => '3jo',
            'revelation' => 'ap',
            'apocalipse' => 'ap',
        ];

        $normalized = strtolower(trim($bookName));
        return $bookMap[$normalized] ?? $normalized;
    }

    /**
     * Search verses by book, chapter, verse range.
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'book' => 'required|string|max:50',
            'chapter' => 'required|integer|min:1',
            'verse_start' => 'nullable|integer|min:1',
            'verse_end' => 'nullable|integer|min:1',
            'version_slug' => 'nullable|string|exists:bible_versions,slug',
        ]);

        $verses = $this->verseService->searchVerses(
            $validated['book'],
            $validated['chapter'],
            $validated['verse_start'] ?? null,
            $validated['verse_end'] ?? null,
            $validated['version_slug'] ?? null
        );

        return response()->json([
            'data' => $verses,
            'message' => 'Verses search completed successfully'
        ]);
    }

    /**
     * Get verses by category.
     */
    public function byCategory(Category $category, Request $request): JsonResponse
    {
        $verses = $this->verseService->getVersesByCategory(
            $category->id,
            $request->get('per_page', 20)
        );

        return response()->json([
            'data' => $verses,
            'message' => "Verses for category '{$category->name}' retrieved successfully"
        ]);
    }

    /**
     * Update the specified verse reference.
     */
    public function update(Request $request, Verse $verse): JsonResponse
    {
        $validated = $request->validate([
            'book' => 'sometimes|string|max:50',
            'chapter' => 'sometimes|integer|min:1',
            'verse_start' => 'sometimes|integer|min:1',
            'verse_end' => 'nullable|integer|min:1',
            'version_id' => 'sometimes|exists:bible_versions,id',
            'external_id' => 'sometimes|string|max:255',
        ]);

        $verse->update($validated);

        return response()->json([
            'data' => $verse->load('version'),
            'message' => 'Verse reference updated successfully'
        ]);
    }

    /**
     * Remove the specified verse reference.
     */
    public function destroy(Verse $verse): JsonResponse
    {
        $verse->delete();

        return response()->json([
            'message' => 'Verse reference deleted successfully'
        ]);
    }
}
