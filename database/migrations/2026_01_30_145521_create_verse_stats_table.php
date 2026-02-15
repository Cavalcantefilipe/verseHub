<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('verse_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('verse_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->integer('votes')->default(0);
            $table->timestamp('updated_at');

            // Unique combination to avoid duplicates
            $table->unique(['verse_id', 'category_id']);

            // Index for fast queries
            $table->index(['category_id', 'votes']);
            $table->index(['verse_id', 'votes']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verse_stats');
    }
};
