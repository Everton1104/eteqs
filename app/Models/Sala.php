<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Sala extends Model
{
    /** Status possíveis. */
    public const STATUS_AGUARDANDO = 'aguardando';
    public const STATUS_ATIVA = 'ativa';
    public const STATUS_FINALIZADA = 'finalizada';

    /**
     * Cores/símbolos das 4 alternativas (padrão Kahoot), por posição 1..4.
     */
    public const CORES = [
        1 => ['cor' => 'vermelho', 'simbolo' => 'triangulo', 'hex' => '#e21b3c', 'forma' => '▲'],
        2 => ['cor' => 'azul',     'simbolo' => 'losango',   'hex' => '#1368ce', 'forma' => '◆'],
        3 => ['cor' => 'amarelo',  'simbolo' => 'circulo',   'hex' => '#d89e00', 'forma' => '●'],
        4 => ['cor' => 'verde',    'simbolo' => 'quadrado',  'hex' => '#26890c', 'forma' => '■'],
    ];

    protected $fillable = [
        'user_id', 'titulo', 'descricao', 'pin', 'status',
        'pergunta_atual', 'pergunta_iniciada_em', 'arquivada',
    ];

    protected $casts = [
        'pergunta_iniciada_em' => 'datetime',
        'arquivada' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function perguntas(): HasMany
    {
        return $this->hasMany(Pergunta::class)->orderBy('ordem');
    }

    public function jogadores(): HasMany
    {
        return $this->hasMany(Jogador::class);
    }

    /** Pergunta atualmente em andamento na sala. */
    public function perguntaAtual()
    {
        return $this->perguntas()->where('id', $this->pergunta_atual)->first();
    }

    /** Scope: salas de um dado usuário (professor). */
    public function scopeDoUsuario($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /** Gera um PIN numérico único de 6 dígitos. */
    public static function gerarPinUnico(): string
    {
        do {
            $pin = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (self::where('pin', $pin)->exists());

        return $pin;
    }
}
