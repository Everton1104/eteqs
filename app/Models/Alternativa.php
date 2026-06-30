<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alternativa extends Model
{
    protected $fillable = ['pergunta_id', 'texto', 'cor', 'simbolo', 'correta', 'ordem'];

    protected $casts = [
        'correta' => 'boolean',
    ];

    public function pergunta(): BelongsTo
    {
        return $this->belongsTo(Pergunta::class);
    }
}
