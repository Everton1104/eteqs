<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Disparado quando um jogador se cadastra na sala.
 * O professor (no lobby) vê o nome aparecer e o total atualizar ao vivo.
 */
class JogadorEntrou implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $salaId,
        public array $jogador, // ['id' => , 'nome' => , 'sobrenome' => ]
        public int $total,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel('sala.'.$this->salaId)];
    }

    public function broadcastAs(): string
    {
        return 'jogador.entrou';
    }

    public function broadcastWith(): array
    {
        return [
            'jogador' => $this->jogador,
            'total' => $this->total,
        ];
    }
}
