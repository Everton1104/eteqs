<?php

namespace App\Http\Controllers;

use App\Events\JogoFinalizado;
use App\Events\PerguntaFinalizada;
use App\Events\PerguntaIniciada;
use App\Models\Jogador;
use App\Models\Resposta;
use App\Models\Sala;
use App\Support\Qr;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class SalaAoVivoController extends Controller
{
    /** Inicia a sala: abre o lobby para os alunos entrarem. */
    public function iniciar(Request $request, Sala $sala): RedirectResponse
    {
        $this->authorize('update', $sala);

        if ($sala->perguntas()->count() === 0) {
            return back()->with('error', 'Adicione pelo menos uma pergunta antes de iniciar.');
        }

        $sala->update([
            'status' => Sala::STATUS_ATIVA,
            'pergunta_atual' => null,
            'pergunta_iniciada_em' => null,
        ]);

        return redirect()->route('professor.salas.aovivo', $sala);
    }

    /** Tela de controle ao vivo (lobby -> pergunta -> resultados -> relatório). */
    public function aoVivo(Request $request, Sala $sala): View
    {
        $this->authorize('view', $sala);
        $sala->load(['perguntas', 'jogadores']);

        $entrarUrl = route('jogador.entrar', $sala->pin);

        return view('professor.salas.aovivo', [
            'sala' => $sala,
            'qrSvg' => Qr::svg($entrarUrl, 240),
            'entrarUrl' => $entrarUrl,
            'estadoInicial' => $this->estadoDaSala($sala),
        ]);
    }

    /** Avança para a próxima pergunta e avisa os jogadores. */
    public function proximaPergunta(Request $request, Sala $sala): JsonResponse
    {
        $this->authorize('update', $sala);

        $perguntas = $sala->perguntas()->orderBy('ordem')->get();
        $idx = $perguntas->search(fn ($p) => $p->id === (int) $sala->pergunta_atual);
        $nextIdx = $idx === false ? 0 : $idx + 1;

        if (! isset($perguntas[$nextIdx])) {
            return response()->json(['fim' => true]);
        }

        $pergunta = $perguntas[$nextIdx];
        $iniciadaEm = Carbon::now();
        $terminaEm = $iniciadaEm->copy()->addSeconds($pergunta->tempo_segundos);

        $sala->update([
            'pergunta_atual' => $pergunta->id,
            'pergunta_iniciada_em' => $iniciadaEm,
        ]);

        broadcast(new PerguntaIniciada(
            salaId: $sala->id,
            perguntaId: $pergunta->id,
            ordem: $pergunta->ordem,
            totalPerguntas: $perguntas->count(),
            texto: $pergunta->texto,
            alternativas: $pergunta->alternativas->map(fn ($a) => [
                'id' => $a->id, 'texto' => $a->texto, 'cor' => $a->cor,
                'simbolo' => $a->simbolo, 'ordem' => $a->ordem,
            ])->values()->toArray(),
            tempoSegundos: $pergunta->tempo_segundos,
            terminaEm: $terminaEm->toIso8601String(),
        ));

        return response()->json([
            'pergunta_id' => $pergunta->id,
            'ordem' => $pergunta->ordem,
            'total_perguntas' => $perguntas->count(),
            'texto' => $pergunta->texto,
            'tempo_segundos' => $pergunta->tempo_segundos,
            'termina_em' => $terminaEm->toIso8601String(),
            'eh_ultima' => $nextIdx === $perguntas->count() - 1,
        ]);
    }

    /** Encerra a pergunta atual: trava respostas, pontua e mostra o gráfico. */
    public function finalizarPergunta(Request $request, Sala $sala): JsonResponse
    {
        $this->authorize('update', $sala);

        $pergunta = $sala->perguntaAtual();
        if (! $pergunta) {
            return response()->json(['erro' => 'Nenhuma pergunta em andamento.'], 422);
        }

        $correta = $pergunta->alternativaCorreta();

        $resultado = $pergunta->alternativas->map(function ($a) use ($pergunta) {
            return [
                'alternativa_id' => $a->id,
                'cor' => $a->cor,
                'ordem' => $a->ordem,
                'correta' => (bool) $a->correta,
                'qtd' => Resposta::where('pergunta_id', $pergunta->id)
                    ->where('alternativa_id', $a->id)->count(),
            ];
        })->values()->toArray();

        if ($correta) {
            Resposta::where('pergunta_id', $pergunta->id)->update(['correta' => false]);
            Resposta::where('pergunta_id', $pergunta->id)
                ->where('alternativa_id', $correta->id)
                ->update(['correta' => true]);

            $acertadores = Resposta::where('pergunta_id', $pergunta->id)
                ->where('alternativa_id', $correta->id)
                ->pluck('jogador_id');

            Jogador::whereIn('id', $acertadores)->increment('pontuacao');
        }

        $totalRespostas = Resposta::where('pergunta_id', $pergunta->id)->count();
        $totalJogadores = $sala->jogadores()->count();

        $sala->update(['pergunta_iniciada_em' => null]);

        broadcast(new PerguntaFinalizada(
            salaId: $sala->id,
            perguntaId: $pergunta->id,
            alternativaCorretaId: $correta?->id,
            resultado: $resultado,
            totalRespostas: $totalRespostas,
            totalJogadores: $totalJogadores,
        ));

        return response()->json([
            'resultado' => $resultado,
            'total_respostas' => $totalRespostas,
            'total_jogadores' => $totalJogadores,
        ]);
    }

    /** Encerra o jogo. */
    public function finalizar(Request $request, Sala $sala): JsonResponse
    {
        $this->authorize('update', $sala);

        $sala->update([
            'status' => Sala::STATUS_FINALIZADA,
            'pergunta_atual' => null,
            'pergunta_iniciada_em' => null,
        ]);

        broadcast(new JogoFinalizado($sala->id));

        return response()->json([
            'finalizado' => true,
            'relatorio_url' => route('professor.salas.relatorio', $sala),
        ]);
    }

    /** Relatório final: acertos de cada aluno. */
    public function relatorio(Request $request, Sala $sala): View
    {
        $this->authorize('view', $sala);
        $sala->load(['perguntas.alternativas', 'jogadores']);

        $jogadores = $sala->jogadores()
            ->withCount(['respostas as acertos' => fn ($q) => $q->where('correta', true)])
            ->orderByDesc('pontuacao')
            ->orderBy('nome')
            ->get();

        $totalPerguntas = $sala->perguntas->count();

        return view('professor.salas.relatorio', compact('sala', 'jogadores', 'totalPerguntas'));
    }

    /** Estado serializado da sala para a tela do professor (JS). */
    protected function estadoDaSala(Sala $sala): array
    {
        return [
            'sala_id' => $sala->id,
            'status' => $sala->status,
            'pergunta_atual' => $sala->pergunta_atual,
            'pergunta_iniciada_em' => $sala->pergunta_iniciada_em?->toIso8601String(),
        ];
    }
}
