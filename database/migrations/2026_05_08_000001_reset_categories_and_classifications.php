<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * VerseHub v2 — "Bíblia para momentos, não para temas".
 *
 * Apaga todas as classificações antigas e categorias para dar lugar à nova
 * estrutura (4 grupos × 5 momentos + categorias custom). Confirmado pelo
 * dono do projeto que essa migração é destrutiva e sem rollback útil.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Usamos DELETE (não TRUNCATE) porque tabelas legadas com sufixo `_off`
        // ainda têm FK pra `categories` e o MySQL rejeita TRUNCATE em tabelas
        // referenciadas por FK. Como o objetivo é só zerar o conteúdo
        // (não resetar AUTO_INCREMENT), DELETE serve.
        if (Schema::hasTable('verse_classifications_off')) {
            DB::table('verse_classifications_off')->delete();
        }
        if (Schema::hasTable('verse_stats_off')) {
            DB::table('verse_stats_off')->delete();
        }

        if (Schema::hasTable('user_verse_categories')) {
            DB::table('user_verse_categories')->delete();
        }

        if (Schema::hasTable('categories')) {
            DB::table('categories')->delete();
        }
    }

    public function down(): void
    {
        // intencional: a recriação é responsabilidade do CategorySeeder.
    }
};
