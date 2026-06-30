<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pergunta extends Model
{
    protected $fillable = ['sala_id', 'texto', 'ordem', 'tempo_segundos'];

    public function sala(): BelongsTo
    {
        return $this->belongsTo(Sala::class);
    }

    public function alternativas(): HasMany
    {
        return $this->hasMany(Alternativa::class)->orderBy('ordem');
    }

    /** Alternativa correta. */
    public function alternativaCorreta()
    {
        return $this->alternativas()->where('correta', true)->first();
    }
}
