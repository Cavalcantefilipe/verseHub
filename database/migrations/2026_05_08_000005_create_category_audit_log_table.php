<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_audit_log', function (Blueprint $table) {
            $table->id();
            $table->enum('target_type', ['category', 'category_group', 'user']);
            $table->unsignedBigInteger('target_id');
            $table->foreignId('admin_user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('action', [
                'approved',
                'rejected',
                'blocked_creator',
                'unblocked_creator',
                'promoted_admin',
                'demoted_admin',
                'edited',
                'created',
            ]);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['target_type', 'target_id']);
            $table->index('admin_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_audit_log');
    }
};
