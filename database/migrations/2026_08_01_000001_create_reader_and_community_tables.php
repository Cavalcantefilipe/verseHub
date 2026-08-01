<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bible_passages', function (Blueprint $table) {
            $table->id();
            $table->string('version', 10);
            $table->string('book_abbrev', 10);
            $table->string('book_name', 100);
            $table->unsignedSmallInteger('chapter');
            $table->unsignedSmallInteger('verse_number');
            $table->text('text');
            $table->timestamps();

            $table->unique(['version', 'book_abbrev', 'chapter', 'verse_number'], 'passage_location_unique');
            $table->index(['version', 'book_abbrev', 'chapter'], 'passage_chapter_index');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE bible_passages ADD FULLTEXT INDEX passage_text_fulltext (text)');
        }

        Schema::create('user_reading_states', function (Blueprint $table) {
            $table->foreignId('user_id')->primary()->constrained('users')->cascadeOnDelete();
            $table->string('version', 10)->default('nvi');
            $table->string('book_abbrev', 10);
            $table->string('book_name', 100);
            $table->unsignedSmallInteger('chapter');
            $table->unsignedSmallInteger('verse_number')->nullable();
            $table->timestamps();
            $table->index('updated_at');
        });

        Schema::create('user_saved_verses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('reference');
            $table->string('version', 10);
            $table->text('text');
            $table->boolean('is_favorite')->default(false);
            $table->string('highlight_color', 20)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'reference', 'version'], 'saved_verse_user_reference_unique');
            $table->index(['user_id', 'updated_at']);
        });

        Schema::create('user_public_profiles', function (Blueprint $table) {
            $table->foreignId('user_id')->primary()->constrained('users')->cascadeOnDelete();
            $table->string('display_name', 80)->nullable();
            $table->string('avatar_url')->nullable();
            $table->string('bio', 240)->nullable();
            $table->boolean('is_public')->default(false);
            $table->boolean('show_ranking')->default(false);
            $table->timestamps();
            $table->index(['is_public', 'show_ranking']);
        });

        Schema::create('community_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bible_verse_id')->unique()->constrained('bible_verses')->cascadeOnDelete();
            $table->unsignedInteger('classifiers_count')->default(0);
            $table->unsignedInteger('classifications_count')->default(0);
            $table->unsignedInteger('likes_count')->default(0);
            $table->unsignedInteger('comments_count')->default(0);
            $table->string('status', 20)->default('published');
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'last_activity_at', 'id'], 'community_recent_index');
            $table->index(['status', 'classifiers_count', 'id'], 'community_popular_index');
        });

        Schema::create('community_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_post_id')->constrained('community_posts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['community_post_id', 'user_id']);
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('community_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_post_id')->constrained('community_posts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('body', 1000);
            $table->string('status', 20)->default('published');
            $table->timestamps();
            $table->index(['community_post_id', 'status', 'id'], 'community_comment_feed_index');
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('community_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->string('reportable_type', 40);
            $table->unsignedBigInteger('reportable_id');
            $table->string('reason', 40);
            $table->string('details', 500)->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamps();
            $table->unique(['reporter_id', 'reportable_type', 'reportable_id'], 'community_report_unique');
            $table->index(['status', 'created_at']);
            $table->index(['reportable_type', 'reportable_id']);
        });

        DB::table('bible_verses')->orderBy('id')->chunkById(500, function ($verses) {
            $verseIds = $verses->pluck('id');
            $aggregates = DB::table('user_verse_categories as uvc')
                ->join('categories as c', function ($join) {
                    $join->on('c.id', '=', 'uvc.category_id')->where('c.status', 'approved');
                })
                ->whereIn('uvc.bible_verse_id', $verseIds)
                ->groupBy('uvc.bible_verse_id')
                ->get([
                    'uvc.bible_verse_id',
                    DB::raw('COUNT(*) as classifications_count'),
                    DB::raw('COUNT(DISTINCT uvc.user_id) as classifiers_count'),
                    DB::raw('MAX(uvc.created_at) as last_activity_at'),
                ]);
            $now = now();
            $rows = $aggregates->map(fn ($row) => [
                'bible_verse_id' => $row->bible_verse_id,
                'classifiers_count' => $row->classifiers_count,
                'classifications_count' => $row->classifications_count,
                'likes_count' => 0,
                'comments_count' => 0,
                'status' => 'published',
                'last_activity_at' => $row->last_activity_at,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all();
            if ($rows !== []) {
                DB::table('community_posts')->insert($rows);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_reports');
        Schema::dropIfExists('community_comments');
        Schema::dropIfExists('community_likes');
        Schema::dropIfExists('community_posts');
        Schema::dropIfExists('user_public_profiles');
        Schema::dropIfExists('user_saved_verses');
        Schema::dropIfExists('user_reading_states');
        Schema::dropIfExists('bible_passages');
    }
};
