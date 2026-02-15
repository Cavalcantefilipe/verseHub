<?php

namespace App\Http\Controllers;

use App\Services\BibleApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class BibleController extends Controller
{
    protected BibleApiService $bibleApiService;

    public function __construct(BibleApiService $bibleApiService)
    {
        $this->bibleApiService = $bibleApiService;
    }

    /**
     * Get all verses from a chapter.
     * GET /api/bible/{version}/{book}/{chapter}
     * Example: /api/bible/nvi/pv/1
     */
    public function getChapter(string $version, string $book, int $chapter): JsonResponse
    {
        try {
            $data = $this->bibleApiService->getChapterVerses($version, $book, $chapter);

            if (!$data) {
                return response()->json([
                    'message' => 'Chapter not found or API error',
                    'error' => 'Unable to retrieve chapter data'
                ], 404);
            }

            return response()->json([
                'data' => $data,
                'message' => 'Chapter retrieved successfully',
                'meta' => [
                    'version' => $version,
                    'book' => $book,
                    'chapter' => $chapter,
                    'verse_count' => count($data['verses'] ?? [])
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving chapter: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error retrieving chapter',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific verse(s) from a chapter.
     * GET /api/bible/{version}/{book}/{chapter}/{verses}
     * Examples:
     * - /api/bible/nvi/pv/1/3 (single verse)
     * - /api/bible/nvi/pv/1/3-10 (verse range)
     */
    public function getVerses(string $version, string $book, int $chapter, string $verses): JsonResponse
    {
        try {
            // Parse verse range (e.g., "3-10" or "16")
            if (str_contains($verses, '-')) {
                [$start, $end] = explode('-', $verses);
                $verseStart = (int) $start;
                $verseEnd = (int) $end;

                if ($verseStart > $verseEnd) {
                    return response()->json([
                        'message' => 'Invalid verse range',
                        'error' => 'Start verse must be less than or equal to end verse'
                    ], 400);
                }
            } else {
                $verseStart = (int) $verses;
                $verseEnd = null;
            }

            $data = $this->bibleApiService->getVerse($version, $book, $chapter, $verseStart, $verseEnd);

            if (!$data || empty($data['verses'])) {
                return response()->json([
                    'message' => 'Verse(s) not found',
                    'error' => 'Unable to retrieve verse data'
                ], 404);
            }

            return response()->json([
                'data' => $data,
                'message' => 'Verse(s) retrieved successfully',
                'meta' => [
                    'version' => $version,
                    'book' => $book,
                    'chapter' => $chapter,
                    'verse_range' => $verseEnd ? "$verseStart-$verseEnd" : (string) $verseStart,
                    'verse_count' => count($data['verses'])
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving verses: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error retrieving verses',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a random verse.
     * GET /api/bible/{version}/random
     * Example: /api/bible/nvi/random
     */
    public function getRandom(string $version): JsonResponse
    {
        try {
            $data = $this->bibleApiService->getRandomVerse($version);

            if (!$data) {
                return response()->json([
                    'message' => 'Unable to retrieve random verse',
                    'error' => 'API error or version not found'
                ], 404);
            }

            return response()->json([
                'data' => $data,
                'message' => 'Random verse retrieved successfully',
                'meta' => [
                    'version' => $version
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving random verse: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error retrieving random verse',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search verses by book, chapter, and optional text query.
     * GET /api/bible/{version}/search?book=pv&chapter=1&q=wisdom
     */
    public function search(Request $request, string $version): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'book' => 'sometimes|string|max:10',
            'chapter' => 'sometimes|integer|min:1',
            'q' => 'sometimes|string|min:2',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $book = $request->query('book');
            $chapter = $request->query('chapter');
            $query = $request->query('q');

            // If book and chapter provided, search within that chapter
            if ($book && $chapter) {
                $data = $this->bibleApiService->getChapterVerses($version, $book, (int) $chapter);

                if ($data && $query) {
                    // Filter verses by search query
                    $data['verses'] = collect($data['verses'])
                        ->filter(function ($verse) use ($query) {
                            return stripos($verse['text'] ?? '', $query) !== false;
                        })
                        ->values()
                        ->toArray();
                }

                return response()->json([
                    'data' => $data,
                    'message' => 'Search completed successfully',
                    'meta' => [
                        'version' => $version,
                        'book' => $book,
                        'chapter' => $chapter,
                        'query' => $query,
                        'result_count' => count($data['verses'] ?? [])
                    ]
                ]);
            }

            return response()->json([
                'message' => 'Please provide at least book and chapter parameters',
                'error' => 'Insufficient search parameters'
            ], 400);
        } catch (\Exception $e) {
            Log::error('Error searching verses: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error searching verses',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available Bible versions.
     * GET /api/bible/versions
     */
    public function getVersions(): JsonResponse
    {
        try {
            $versions = $this->bibleApiService->getAvailableVersions();

            return response()->json([
                'data' => $versions,
                'message' => 'Bible versions retrieved successfully',
                'meta' => [
                    'count' => count($versions)
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving Bible versions: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error retrieving Bible versions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get book information and available chapters.
     * GET /api/bible/{version}/{book}/info
     * Example: /api/bible/nvi/pv/info
     */
    public function getBookInfo(string $version, string $book): JsonResponse
    {
        try {
            // Get first chapter to extract book information
            $data = $this->bibleApiService->getChapterVerses($version, $book, 1);

            if (!$data) {
                return response()->json([
                    'message' => 'Book not found',
                    'error' => 'Unable to retrieve book information'
                ], 404);
            }

            return response()->json([
                'data' => [
                    'book' => $data['book'] ?? null,
                    'version' => $version,
                    'abbreviation' => $book
                ],
                'message' => 'Book information retrieved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving book info: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error retrieving book information',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
