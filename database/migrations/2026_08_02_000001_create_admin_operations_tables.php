<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_banned')->default(false)->after('is_admin');
            $table->timestamp('banned_at')->nullable()->after('is_banned');
            $table->string('banned_reason', 500)->nullable()->after('banned_at');
            $table->index(['is_banned', 'created_at']);
        });

        Schema::table('community_comments', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false)->after('status');
            $table->index(['community_post_id', 'status', 'is_featured', 'id'], 'community_comment_moderation_index');
        });

        Schema::create('daily_verses', function (Blueprint $table) {
            $table->id();
            $table->date('publish_date');
            $table->unsignedTinyInteger('position')->default(1);
            $table->string('reference', 120);
            $table->string('version', 10)->default('NVI');
            $table->text('text');
            $table->string('book_abbrev', 10)->nullable();
            $table->string('book_name', 100)->nullable();
            $table->unsignedSmallInteger('chapter')->nullable();
            $table->unsignedSmallInteger('verse_number')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['publish_date', 'position']);
            $table->index(['publish_date', 'is_active', 'position']);
        });

        Schema::create('push_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->char('token_hash', 64)->unique();
            $table->text('token');
            $table->string('platform', 10);
            $table->string('device_name', 120)->nullable();
            $table->string('app_version', 30)->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'enabled']);
            $table->index(['enabled', 'platform', 'last_seen_at']);
        });

        Schema::create('push_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('title', 80);
            $table->string('body', 240);
            $table->string('audience', 30)->default('all');
            $table->json('audience_data')->nullable();
            $table->json('data')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->unsignedInteger('target_count')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->text('last_error')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['status', 'scheduled_at']);
        });

        Schema::create('admin_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 80);
            $table->string('target_type', 80);
            $table->unsignedBigInteger('target_id')->nullable();
            $table->json('context')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['target_type', 'target_id', 'created_at']);
            $table->index(['admin_user_id', 'created_at']);
            $table->index(['action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_audit_logs');
        Schema::dropIfExists('push_campaigns');
        Schema::dropIfExists('push_devices');
        Schema::dropIfExists('daily_verses');

        Schema::table('community_comments', function (Blueprint $table) {
            $table->dropIndex('community_comment_moderation_index');
            $table->dropColumn('is_featured');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_banned', 'created_at']);
            $table->dropColumn(['is_banned', 'banned_at', 'banned_reason']);
        });
    }
};
