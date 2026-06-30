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
        Schema::create('alternativas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pergunta_id')->constrained()->cascadeOnDelete();
            $table->string('texto');
            $table->string('cor');     // vermelho | azul | amarelo | verde
            $table->string('simbolo'); // triangulo | losango | circulo | quadrado
            $table->boolean('correta')->default(false);
            $table->unsignedTinyInteger('ordem')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alternativas');
    }
};
