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
        Schema::create('public_classifications', function (Blueprint $table) {
            $table->id();
            $table->string('device_id'); // Identificador único do dispositivo
            $table->string('reference'); // Ex: "Provérbios 1:1-3"
            $table->text('text'); // Texto do versículo
            $table->string('version', 10); // NVI, ACF, ARA, etc
            $table->json('category_ids'); // Array de IDs das categorias
            $table->timestamps();

            $table->index('device_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('public_classifications');
    }
};
