<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('category_groups', function (Blueprint $table) {
            $table->string('classification_kind', 24)->default('context')->after('slug');
            $table->string('selection_prompt', 160)->nullable()->after('classification_kind');
            $table->unsignedTinyInteger('selection_limit')->nullable()->after('selection_prompt');
        });

        $now = now();

        DB::table('category_groups')->updateOrInsert(
            ['slug' => 'sentimentos'],
            [
                'name' => 'Sentimentos',
                'classification_kind' => 'emotion',
                'selection_prompt' => 'O que você sentiu ao ler este versículo?',
                'selection_limit' => 3,
                'icon' => 'heart-outline',
                'color' => '#db2777',
                'display_order' => 0,
                'created_by_user_id' => null,
                'status' => 'approved',
                'approved_by_user_id' => null,
                'approved_at' => $now,
                'rejected_reason' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $groupId = DB::table('category_groups')->where('slug', 'sentimentos')->value('id');

        $emotions = [
            ['slug' => 'sentindo-paz', 'name' => 'Em paz', 'icon' => 'leaf-outline', 'color' => '#059669'],
            ['slug' => 'sentindo-esperanca', 'name' => 'Com esperança', 'icon' => 'sunny-outline', 'color' => '#ca8a04'],
            ['slug' => 'sentindo-gratidao', 'name' => 'Com gratidão', 'icon' => 'heart-outline', 'color' => '#db2777'],
            ['slug' => 'sentindo-coragem', 'name' => 'Com coragem', 'icon' => 'shield-checkmark-outline', 'color' => '#2563eb'],
            ['slug' => 'sentindo-ansiedade', 'name' => 'Com ansiedade', 'icon' => 'pulse-outline', 'color' => '#ea580c'],
            ['slug' => 'sentindo-tristeza', 'name' => 'Com tristeza', 'icon' => 'sad-outline', 'color' => '#475569'],
            ['slug' => 'sentindo-medo', 'name' => 'Com medo', 'icon' => 'alert-circle-outline', 'color' => '#64748b'],
            ['slug' => 'sentindo-solidao', 'name' => 'Só', 'icon' => 'person-outline', 'color' => '#7c3aed'],
            ['slug' => 'sentindo-cansaco', 'name' => 'Cansado', 'icon' => 'bed-outline', 'color' => '#a16207'],
            ['slug' => 'sentindo-culpa', 'name' => 'Com culpa', 'icon' => 'rainy-outline', 'color' => '#991b1b'],
            ['slug' => 'sentindo-raiva', 'name' => 'Com raiva', 'icon' => 'flame-outline', 'color' => '#dc2626'],
            ['slug' => 'sentindo-confusao', 'name' => 'Confuso', 'icon' => 'help-circle-outline', 'color' => '#4f46e5'],
        ];

        foreach ($emotions as $order => $emotion) {
            DB::table('categories')->updateOrInsert(
                ['slug' => $emotion['slug']],
                [
                    'category_group_id' => $groupId,
                    'name' => $emotion['name'],
                    'description' => null,
                    'icon' => $emotion['icon'],
                    'color' => $emotion['color'],
                    'created_by_user_id' => null,
                    'status' => 'approved',
                    'approved_by_user_id' => null,
                    'approved_at' => $now,
                    'rejected_reason' => null,
                    'display_order' => $order + 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        $groupId = DB::table('category_groups')->where('slug', 'sentimentos')->value('id');
        if ($groupId) {
            DB::table('categories')->where('category_group_id', $groupId)->delete();
            DB::table('category_groups')->where('id', $groupId)->delete();
        }

        Schema::table('category_groups', function (Blueprint $table) {
            $table->dropColumn(['classification_kind', 'selection_prompt', 'selection_limit']);
        });
    }
};
