<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resposta extends Model
{
    protected $fillable = ['jogador_id', 'pergunta_id', 'alternativa_id', 'correta', 'tempo_ms'];

    protected $casts = [
        'correta' => 'boolean',
    ];

    public function jogador(): BelongsTo
    {
        return $this->belongsTo(Jogador::class);
    }

    public function pergunta(): BelongsTo
    {
        return $this->belongsTo(Pergunta::class);
    }

    public function alternativa(): BelongsTo
    {
        return $this->belongsTo(Alternativa::class);
    }
}
