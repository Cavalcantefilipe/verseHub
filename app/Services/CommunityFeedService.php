<?php

namespace App\Services;

use App\Models\CommunityPost;
use Illuminate\Support\Facades\DB;

class CommunityFeedService
{
    public function refreshVerse(int $bibleVerseId): ?CommunityPost
    {
        $aggregate = DB::table('user_verse_categories as uvc')
            ->join('categories as c', function ($join) {
                $join->on('c.id', '=', 'uvc.category_id')->where('c.status', 'approved');
            })
            ->where('uvc.bible_verse_id', $bibleVerseId)
            ->selectRaw('COUNT(*) as classifications_count')
            ->selectRaw('COUNT(DISTINCT uvc.user_id) as classifiers_count')
            ->selectRaw('MAX(uvc.created_at) as last_activity_at')
            ->first();

        if (! $aggregate || (int) $aggregate->classifications_count === 0) {
            CommunityPost::where('bible_verse_id', $bibleVerseId)->delete();

            return null;
        }

        return CommunityPost::updateOrCreate(
            ['bible_verse_id' => $bibleVerseId],
            [
                'classifiers_count' => (int) $aggregate->classifiers_count,
                'classifications_count' => (int) $aggregate->classifications_count,
                'last_activity_at' => $aggregate->last_activity_at,
                'status' => 'published',
            ]
        );
    }
}
