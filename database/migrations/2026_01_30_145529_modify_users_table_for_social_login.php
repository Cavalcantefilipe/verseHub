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
        Schema::table('users', function (Blueprint $table) {
            $table->string('provider')->nullable()->after('email'); // google, apple, facebook
            $table->string('provider_id')->nullable()->after('provider');
            $table->timestamp('email_verified_at')->nullable()->change();
            $table->string('password')->nullable()->change();

            // Index for social login lookups
            $table->index(['provider', 'provider_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['provider', 'provider_id']);
            $table->dropColumn(['provider', 'provider_id']);
            $table->timestamp('email_verified_at')->nullable(false)->change();
            $table->string('password')->nullable(false)->change();
        });
    }
};
