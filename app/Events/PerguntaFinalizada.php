<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Disparado quando o tempo da pergunta acaba (ou o professor finaliza).
 * Leva a alternativa correta + contagem por alternativa (para o gráfico do professor)
 * e permite ao jogador saber se acertou.
 */
class PerguntaFinalizada implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $salaId,
        public int $perguntaId,
        public ?int $alternativaCorretaId,
        public array $resultado, // [['alternativa_id','cor','ordem','qtd','correta'], ...]
        public int $totalRespostas,
        public int $totalJogadores,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel('sala.'.$this->salaId)];
    }

    public function broadcastAs(): string
    {
        return 'pergunta.finalizada';
    }

    public function broadcastWith(): array
    {
        return [
            'pergunta_id' => $this->perguntaId,
            'alternativa_correta_id' => $this->alternativaCorretaId,
            'resultado' => $this->resultado,
            'total_respostas' => $this->totalRespostas,
            'total_jogadores' => $this->totalJogadores,
        ];
    }
}
