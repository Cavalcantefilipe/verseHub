<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * New scalable classification structure.
     *
     * Instead of storing category_ids as a JSON array in public_classifications,
     * we now use:
     *   - bible_verses: unique verse content (reference + version = unique)
     *   - user_verse_categories: pivot table (user + verse + category = unique classification)
     *
     * This allows proper SQL JOINs, indexes, and scalable queries.
     */
    public function up(): void
    {
        // 1. Bible verses table — stores unique verse text by reference+version
        Schema::create('bible_verses', function (Blueprint $table) {
            $table->id();
            $table->string('reference');        // e.g. "Provérbios 1:1-3"
            $table->text('text');               // verse text
            $table->string('version', 10);      // NVI, ACF, ARA, etc
            $table->timestamps();

            // A reference+version pair should be unique
            $table->unique(['reference', 'version']);
            $table->index('reference');
            $table->index('version');
        });

        // 2. User-verse-category pivot table — one row per classification
        Schema::create('user_verse_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();    // null for anonymous/device-only
            $table->string('device_id')->nullable();               // for legacy anonymous classifications
            $table->foreignId('bible_verse_id')->constrained('bible_verses')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->timestamps();

            // Prevent duplicate: same user can't classify same verse with same category twice
            $table->unique(['user_id', 'bible_verse_id', 'category_id'], 'uvc_user_verse_category_unique');
            // For anonymous: same device can't classify same verse with same category twice
            $table->unique(['device_id', 'bible_verse_id', 'category_id'], 'uvc_device_verse_category_unique');

            // Performance indexes
            $table->index('user_id');
            $table->index('device_id');
            $table->index(['bible_verse_id', 'category_id']);
            $table->index(['user_id', 'created_at']);
            $table->index('category_id');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_verse_categories');
        Schema::dropIfExists('bible_verses');
    }
};
