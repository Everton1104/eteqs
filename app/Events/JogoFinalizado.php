<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Disparado quando o professor encerra a sala.
 * Os jogadores são redirecionados para a tela de pontuação final.
 */
class JogoFinalizado implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public int $salaId) {}

    public function broadcastOn(): array
    {
        return [new Channel('sala.'.$this->salaId)];
    }

    public function broadcastAs(): string
    {
        return 'jogo.finalizado';
    }
}
