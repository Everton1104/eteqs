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
        Schema::create('respostas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jogador_id')->constrained('jogadores')->cascadeOnDelete();
            $table->foreignId('pergunta_id')->constrained('perguntas')->cascadeOnDelete();
            $table->foreignId('alternativa_id')->nullable()->constrained('alternativas')->nullOnDelete();
            $table->boolean('correta')->default(false);
            $table->unsignedInteger('tempo_ms')->nullable();
            $table->timestamps();
            $table->unique(['jogador_id', 'pergunta_id']); // uma resposta por jogador/pergunta
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('respostas');
    }
};
