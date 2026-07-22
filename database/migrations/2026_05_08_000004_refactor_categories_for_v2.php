<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('category_group_id')->nullable()->after('id')
                ->constrained('category_groups')->nullOnDelete();

            $table->foreignId('created_by_user_id')->nullable()->after('color')
                ->constrained('users')->nullOnDelete();
            $table->enum('status', ['approved', 'pending', 'rejected'])->default('approved')->after('created_by_user_id');
            $table->foreignId('approved_by_user_id')->nullable()->after('status')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by_user_id');
            $table->text('rejected_reason')->nullable()->after('approved_at');
            $table->unsignedInteger('display_order')->default(0)->after('rejected_reason');

            $table->index(['status', 'category_group_id']);
            $table->index(['created_by_user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['status', 'category_group_id']);
            $table->dropIndex(['created_by_user_id', 'status']);

            $table->dropConstrainedForeignId('category_group_id');
            $table->dropConstrainedForeignId('created_by_user_id');
            $table->dropConstrainedForeignId('approved_by_user_id');

            $table->dropColumn(['status', 'approved_at', 'rejected_reason', 'display_order']);
        });
    }
};
