<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Migrate data from public_classifications (JSON category_ids)
     * to the new normalized structure (bible_verses + user_verse_categories).
     *
     * The old table public_classifications is NOT deleted — it stays as backup.
     * Uses chunking for performance with large datasets.
     */
    public function up(): void
    {
        // Check if old table exists
        if (!DB::getSchemaBuilder()->hasTable('public_classifications')) {
            return;
        }

        $total = DB::table('public_classifications')->count();
        if ($total === 0) {
            return;
        }

        $verseCache = []; // "reference|version" => bible_verse_id

        // Pre-load existing category IDs for fast validation
        $validCategoryIds = DB::table('categories')->pluck('id')->flip()->toArray();

        DB::table('public_classifications')->orderBy('id')->chunk(200, function ($classifications) use (
            &$verseCache,
            $validCategoryIds
        ) {
            foreach ($classifications as $classification) {
                $reference = $classification->reference;
                $version = $classification->version;
                $text = $classification->text;
                $userId = $classification->user_id;
                $deviceId = $classification->device_id;
                $createdAt = $classification->created_at;
                $updatedAt = $classification->updated_at;

                // Decode category_ids
                $categoryIds = is_string($classification->category_ids)
                    ? json_decode($classification->category_ids, true)
                    : $classification->category_ids;

                if (empty($categoryIds) || !is_array($categoryIds)) {
                    continue;
                }

                // 1. Find or create bible_verse
                $cacheKey = "{$reference}|{$version}";
                if (!isset($verseCache[$cacheKey])) {
                    $existing = DB::table('bible_verses')
                        ->where('reference', $reference)
                        ->where('version', $version)
                        ->first();

                    if ($existing) {
                        $verseCache[$cacheKey] = $existing->id;
                    } else {
                        $verseId = DB::table('bible_verses')->insertGetId([
                            'reference' => $reference,
                            'text' => $text,
                            'version' => $version,
                            'created_at' => $createdAt,
                            'updated_at' => $updatedAt,
                        ]);
                        $verseCache[$cacheKey] = $verseId;
                    }
                }

                $bibleVerseId = $verseCache[$cacheKey];

                // 2. Create one user_verse_categories row per category_id
                foreach ($categoryIds as $categoryId) {
                    // Validate category exists using pre-loaded set
                    if (!isset($validCategoryIds[$categoryId])) {
                        continue;
                    }

                    // Check for duplicates
                    $query = DB::table('user_verse_categories')
                        ->where('bible_verse_id', $bibleVerseId)
                        ->where('category_id', $categoryId);

                    if ($userId) {
                        $query->where('user_id', $userId);
                    } else {
                        $query->where('device_id', $deviceId)->whereNull('user_id');
                    }

                    if (!$query->exists()) {
                        DB::table('user_verse_categories')->insert([
                            'user_id' => $userId,
                            'device_id' => $deviceId,
                            'bible_verse_id' => $bibleVerseId,
                            'category_id' => $categoryId,
                            'created_at' => $createdAt,
                            'updated_at' => $updatedAt,
                        ]);
                    }
                }
            }
        });
    }

    /**
     * Reverse the migration — clear the new tables (data goes back to public_classifications).
     */
    public function down(): void
    {
        DB::table('user_verse_categories')->truncate();
        DB::table('bible_verses')->truncate();
    }
};
