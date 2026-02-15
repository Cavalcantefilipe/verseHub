<?php

namespace App\Services;

use App\Models\VerseClassification;
use App\Models\VerseStat;
use App\Models\User;
use App\Models\Verse;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class VerseClassificationService
{
    /**
     * Create or update a verse classification.
     */
    public function classifyVerse(
        User $user,
        Verse $verse,
        Category $category,
        ?int $confidenceLevel = null
    ): VerseClassification {
        $classification = VerseClassification::updateOrCreate([
            'user_id' => $user->id,
            'verse_id' => $verse->id,
            'category_id' => $category->id,
        ], [
            'confidence_level' => $confidenceLevel,
        ]);

        // Update stats asynchronously
        $this->updateVerseStats($verse->id, $category->id);

        return $classification;
    }

    /**
     * Remove a classification.
     */
    public function removeClassification(User $user, Verse $verse, Category $category): bool
    {
        $classification = VerseClassification::where([
            'user_id' => $user->id,
            'verse_id' => $verse->id,
            'category_id' => $category->id,
        ])->first();

        if ($classification) {
            $deleted = $classification->delete();

            if ($deleted) {
                // Update stats
                $this->updateVerseStats($verse->id, $category->id);
            }

            return $deleted;
        }

        return false;
    }

    /**
     * Get classifications by user.
     */
    public function getUserClassifications(User $user, int $perPage = 20)
    {
        return VerseClassification::with(['verse.version', 'category'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get verse classifications with stats.
     */
    public function getVerseClassifications(Verse $verse): Collection
    {
        return VerseClassification::with(['user', 'category'])
            ->where('verse_id', $verse->id)
            ->get();
    }

    /**
     * Update verse statistics (called after classification changes).
     */
    protected function updateVerseStats(int $verseId, int $categoryId): void
    {
        $count = VerseClassification::where('verse_id', $verseId)
            ->where('category_id', $categoryId)
            ->count();

        VerseStat::updateOrCreate([
            'verse_id' => $verseId,
            'category_id' => $categoryId,
        ], [
            'votes' => $count,
            'updated_at' => now(),
        ]);
    }

    /**
     * Get top verses for a category.
     */
    public function getTopVersesForCategory(Category $category, int $limit = 10): Collection
    {
        return Verse::with(['version'])
            ->whereHas('stats', function ($query) use ($category) {
                $query->where('category_id', $category->id);
            })
            ->join('verse_stats', 'verses.id', '=', 'verse_stats.verse_id')
            ->where('verse_stats.category_id', $category->id)
            ->orderBy('verse_stats.votes', 'desc')
            ->limit($limit)
            ->select('verses.*', 'verse_stats.votes')
            ->get();
    }

    /**
     * Check if user has classified a verse in a category.
     */
    public function hasUserClassified(User $user, Verse $verse, Category $category): bool
    {
        return VerseClassification::where([
            'user_id' => $user->id,
            'verse_id' => $verse->id,
            'category_id' => $category->id,
        ])->exists();
    }
}
