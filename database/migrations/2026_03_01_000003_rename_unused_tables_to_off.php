<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rename unused/legacy tables to *_off suffix.
     *
     * These tables are no longer used by the current app:
     *   - public_classifications: replaced by bible_verses + user_verse_categories
     *   - bible_versions: was a local cache, app now uses external Bible API
     *   - verses: was a local cache, app now uses external Bible API
     *   - verse_classifications: old classification system, replaced
     *   - verse_stats: old stats system, now computed from user_verse_categories
     */
    public function up(): void
    {
        $tables = [
            'public_classifications',
            'bible_versions',
            'verses',
            'verse_classifications',
            'verse_stats',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasTable("{$table}_off")) {
                Schema::rename($table, "{$table}_off");
            }
        }
    }

    /**
     * Reverse: rename *_off tables back to original names.
     */
    public function down(): void
    {
        $tables = [
            'public_classifications',
            'bible_versions',
            'verses',
            'verse_classifications',
            'verse_stats',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable("{$table}_off") && !Schema::hasTable($table)) {
                Schema::rename("{$table}_off", $table);
            }
        }
    }
};
