<?php

namespace App\Http\Controllers;

use App\Events\JogadorEntrou;
use App\Http\Requests\RegistroJogadorRequest;
use App\Models\Alternativa;
use App\Models\Jogador;
use App\Models\Resposta;
use App\Models\Sala;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class JogadorController extends Controller
{
    /** Tela de entrada: mostra o formulário de nome/sobrenome. */
    public function entrar(string $pin): View
    {
        $sala = Sala::where('pin', $pin)->firstOrFail();

        if ($sala->status === Sala::STATUS_FINALIZADA) {
            return view('jogador.encerrada', ['sala' => $sala]);
        }

        return view('jogador.entrar', compact('sala'));
    }

    /** Cadastra o jogador e o leva para a tela de jogo. */
    public function registrar(RegistroJogadorRequest $request, string $pin)
    {
        $sala = Sala::where('pin', $pin)->firstOrFail();

        $jogador = Jogador::firstOrCreate(
            [
                'sala_id' => $sala->id,
                'nome' => $request->nome,
                'sobrenome' => $request->sobrenome,
            ],
            ['pontuacao' => 0]
        );

        session(['jogador_id_'.$sala->id => $jogador->id]);

        // Avisa o professor (lobby ao vivo).
        broadcast(new JogadorEntrou(
            salaId: $sala->id,
            jogador: ['id' => $jogador->id, 'nome' => $jogador->nome, 'sobrenome' => $jogador->sobrenome],
            total: $sala->jogadores()->count(),
        ))->toOthers();

        return redirect()->route('jogador.jogar', $pin);
    }

    /** Tela do jogo (mobile): aguarda perguntas, mostra alternativas, envia respostas. */
    public function jogar(string $pin): View|RedirectResponse
    {
        $sala = Sala::where('pin', $pin)->firstOrFail();
        $jogador = $this->jogadorDaSessao($sala);

        if (! $jogador) {
            return redirect()->route('jogador.entrar', $pin);
        }

        return view('jogador.jogar', [
            'sala' => $sala,
            'jogador' => $jogador,
            'csrfToken' => csrf_token(),
        ]);
    }

    /** Recebe a resposta do jogador (uma por pergunta; bloqueada após o tempo). */
    public function responder(Request $request, string $pin)
    {
        $data = $request->validate([
            'pergunta_id' => ['required', 'integer'],
            'alternativa_id' => ['required', 'integer'],
        ]);

        $sala = Sala::where('pin', $pin)->firstOrFail();
        $jogador = $this->jogadorDaSessao($sala);

        if (! $jogador) {
            return response()->json(['erro' => 'Sessão expirada.'], 403);
        }

        $pergunta = $sala->perguntas()->where('id', $data['pergunta_id'])->first();
        $alternativa = Alternativa::where('id', $data['alternativa_id'])
            ->where('pergunta_id', $data['pergunta_id'])
            ->first();

        if (! $pergunta || ! $alternativa) {
            return response()->json(['erro' => 'Pergunta/alternativa inválida.'], 422);
        }

        // Controle de tempo:
        // - primeira resposta: aceita até o tempo acabar;
        // - trocar de alternativa: só até 5s antes do fim.
        if ($sala->pergunta_iniciada_em) {
            $terminaEm = $sala->pergunta_iniciada_em->addSeconds($pergunta->tempo_segundos);
            $jaRespondeu = Resposta::where('jogador_id', $jogador->id)
                ->where('pergunta_id', $pergunta->id)->exists();
            // 1ª resposta: até 3s após o fim (tolerância p/ conexão lenta).
            // Troca: só até 5s antes do fim.
            $limite = $jaRespondeu ? $terminaEm->copy()->subSeconds(5) : $terminaEm->copy()->addSeconds(3);

            if (Carbon::now()->gt($limite)) {
                return response()->json(['erro' => $jaRespondeu ? 'Tempo para trocar encerrado.' : 'Tempo encerrado.'], 410);
            }
        }

        // Uma resposta por jogador/pergunta — mas o aluno pode TROCAR enquanto o
        // tempo não acabou (updateOrCreate atualiza a alternativa escolhida).
        $resposta = Resposta::updateOrCreate(
            ['jogador_id' => $jogador->id, 'pergunta_id' => $pergunta->id],
            [
                'alternativa_id' => $alternativa->id,
                'correta' => $alternativa->correta,
            ]
        );

        return response()->json(['ok' => true, 'alternativa_id' => $alternativa->id]);
    }

    protected function jogadorDaSessao(Sala $sala): ?Jogador
    {
        $id = session('jogador_id_'.$sala->id);

        return $id ? Jogador::find($id) : null;
    }

    /**
     * Estado atual da sala sob a ótica do jogador — usado para retomar
     * de onde parou ao reconectar (refresh, queda de rede, reabrir o link).
     */
    public function estado(string $pin)
    {
        $sala = Sala::where('pin', $pin)->firstOrFail();
        $jogador = $this->jogadorDaSessao($sala);

        if (! $jogador) {
            return response()->json(['erro' => 'Sessão expirada.'], 403);
        }

        $pergunta = $sala->perguntaAtual();
        $terminaEm = ($pergunta && $sala->pergunta_iniciada_em)
            ? $sala->pergunta_iniciada_em->addSeconds($pergunta->tempo_segundos)
            : null;
        $restante = ($pergunta && $sala->pergunta_iniciada_em)
            ? max(0, $terminaEm->timestamp - now()->timestamp)
            : null;

        $resposta = $pergunta
            ? Resposta::where('jogador_id', $jogador->id)->where('pergunta_id', $pergunta->id)->first()
            : null;

        return response()->json([
            'status' => $sala->status,
            'pergunta_id' => $pergunta?->id,
            'ordem' => $pergunta?->ordem,
            'total_perguntas' => $sala->perguntas()->count(),
            'texto' => $pergunta?->texto,
            'tempo_segundos' => $pergunta?->tempo_segundos,
            'termina_em' => $terminaEm?->toIso8601String(),
            'restante' => $restante,
            'alternativas' => $pergunta
                ? $pergunta->alternativas->map(fn ($a) => [
                    'id' => $a->id, 'cor' => $a->cor, 'simbolo' => $a->simbolo, 'ordem' => $a->ordem,
                ])->values()->toArray()
                : [],
            'respondida' => (bool) $resposta,
            'minha_alternativa' => $resposta?->alternativa_id,
            'pontuacao' => $jogador->pontuacao,
        ]);
    }
}
