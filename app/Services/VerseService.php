<?php

namespace App\Services;

use App\Models\Verse;
use App\Models\BibleVersion;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class VerseService
{
    /**
     * Search verses by book, chapter, and verse range.
     */
    public function searchVerses(
        string $book,
        int $chapter,
        ?int $verseStart = null,
        ?int $verseEnd = null,
        ?string $versionSlug = null
    ): Collection {
        $query = Verse::with(['version', 'classifications.category'])
            ->where('book', $book)
            ->where('chapter', $chapter);

        if ($verseStart) {
            $query->where('verse_start', '>=', $verseStart);
        }

        if ($verseEnd) {
            $query->where(function ($q) use ($verseEnd) {
                $q->where('verse_end', '<=', $verseEnd)
                    ->orWhere('verse_start', '<=', $verseEnd);
            });
        }

        if ($versionSlug) {
            $query->whereHas('version', function ($q) use ($versionSlug) {
                $q->where('slug', $versionSlug);
            });
        }

        return $query->orderBy('verse_start')->get();
    }

    /**
     * Get verses by category with pagination.
     */
    public function getVersesByCategory(int $categoryId, int $perPage = 20): LengthAwarePaginator
    {
        return Verse::with(['version', 'stats' => function ($query) use ($categoryId) {
            $query->where('category_id', $categoryId);
        }])
            ->whereHas('classifications', function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->paginate($perPage);
    }

    /**
     * Create a verse reference (without storing text).
     */
    public function createVerse(array $data): Verse
    {
        return Verse::create($data);
    }

    /**
     * Find or create a verse reference.
     */
    public function findOrCreateVerse(
        string $book,
        int $chapter,
        int $verseStart,
        ?int $verseEnd,
        int $versionId,
        string $externalId
    ): Verse {
        return Verse::firstOrCreate([
            'book' => $book,
            'chapter' => $chapter,
            'verse_start' => $verseStart,
            'verse_end' => $verseEnd,
            'version_id' => $versionId,
        ], [
            'external_id' => $externalId,
        ]);
    }

    /**
     * Get popular verses (most classified).
     */
    public function getPopularVerses(int $limit = 10): Collection
    {
        return Verse::with(['version'])
            ->withCount('classifications')
            ->orderBy('classifications_count', 'desc')
            ->limit($limit)
            ->get();
    }
}
