<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('email_verified_at');
            $table->boolean('can_create_categories')->default(true)->after('is_admin');
            $table->unsignedInteger('custom_categories_count')->default(0)->after('can_create_categories');

            $table->index('is_admin');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_admin']);
            $table->dropColumn(['is_admin', 'can_create_categories', 'custom_categories_count']);
        });
    }
};
