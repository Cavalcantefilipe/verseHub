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
        Schema::create('verses', function (Blueprint $table) {
            $table->id();
            $table->string('book');
            $table->integer('chapter');
            $table->integer('verse_start');
            $table->integer('verse_end')->nullable(); // for verse ranges
            $table->foreignId('version_id')->constrained('bible_versions')->onDelete('cascade');
            $table->string('external_id'); // ID from external Bible API
            $table->timestamps();

            // Indexes for performance
            $table->index(['book', 'chapter', 'verse_start']);
            $table->index(['version_id', 'book', 'chapter']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verses');
    }
};
