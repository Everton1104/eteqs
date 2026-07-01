<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Disparado quando o professor inicia uma pergunta.
 * Os jogadores recebem: texto, alternativas (sem flag de correta) e o prazo (termina_em).
 */
class PerguntaIniciada implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $salaId,
        public int $perguntaId,
        public int $ordem,
        public int $totalPerguntas,
        public string $texto,
        public array $alternativas, // [['id','texto','cor','simbolo','ordem'], ...]
        public int $tempoSegundos,
        public string $terminaEm, // ISO-8601 (UTC)
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel('sala.'.$this->salaId)];
    }

    public function broadcastAs(): string
    {
        return 'pergunta.iniciada';
    }

    public function broadcastWith(): array
    {
        // Ao aluno enviamos apenas o necessário (cor + símbolo). Nunca o enunciado,
        // os textos das alternativas ou qualquer flag de "correta" — para não vazar
        // a resposta mesmo via inspeção de rede/console.
        return [
            'pergunta_id' => $this->perguntaId,
            'ordem' => $this->ordem,
            'total_perguntas' => $this->totalPerguntas,
            'alternativas' => collect($this->alternativas)->map(fn ($a) => [
                'id' => $a['id'], 'cor' => $a['cor'], 'simbolo' => $a['simbolo'], 'ordem' => $a['ordem'],
            ])->values()->toArray(),
            'tempo_segundos' => $this->tempoSegundos,
            'termina_em' => $this->terminaEm,
        ];
    }
}
