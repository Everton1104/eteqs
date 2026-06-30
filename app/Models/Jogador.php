<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jogador extends Model
{
    // O inflector do Laravel gera "jogadors"; forçamos o plural correto em PT-BR.
    protected $table = 'jogadores';

    protected $fillable = ['sala_id', 'nome', 'sobrenome', 'pontuacao'];

    public function sala(): BelongsTo
    {
        return $this->belongsTo(Sala::class);
    }

    public function respostas(): HasMany
    {
        return $this->hasMany(Resposta::class);
    }

    /** Nome completo (nome + sobrenome). */
    public function getNomeCompletoAttribute(): string
    {
        return trim("{$this->nome} {$this->sobrenome}");
    }
}
