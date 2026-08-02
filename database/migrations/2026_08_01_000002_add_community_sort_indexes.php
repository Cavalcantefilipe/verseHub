<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('community_posts', function (Blueprint $table) {
            $table->index(
                ['status', 'likes_count', 'id'],
                'community_liked_index'
            );
            $table->index(
                ['status', 'comments_count', 'id'],
                'community_commented_index'
            );
            $table->index(
                ['status', 'created_at', 'id'],
                'community_oldest_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('community_posts', function (Blueprint $table) {
            $table->dropIndex('community_liked_index');
            $table->dropIndex('community_commented_index');
            $table->dropIndex('community_oldest_index');
        });
    }
};
