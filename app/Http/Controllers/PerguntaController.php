<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePerguntaRequest;
use App\Http\Requests\UpdatePerguntaRequest;
use App\Models\Pergunta;
use App\Models\Sala;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PerguntaController extends Controller
{
    public function create(Sala $sala): View
    {
        $this->authorize('update', $sala);

        return view('professor.perguntas.create', [
            'sala' => $sala,
            'cores' => Sala::CORES,
        ]);
    }

    public function store(StorePerguntaRequest $request, Sala $sala): RedirectResponse
    {
        $this->authorize('update', $sala);

        $this->salvarPergunta($request, $sala);

        return redirect()
            ->route('professor.salas.show', $sala)
            ->with('success', 'Pergunta adicionada.');
    }

    public function edit(Pergunta $pergunta): View
    {
        $this->authorize('update', $pergunta->sala);
        $pergunta->load('alternativas');

        return view('professor.perguntas.edit', [
            'sala' => $pergunta->sala,
            'pergunta' => $pergunta,
            'cores' => Sala::CORES,
        ]);
    }

    public function update(UpdatePerguntaRequest $request, Pergunta $pergunta): RedirectResponse
    {
        $this->authorize('update', $pergunta->sala);
        $sala = $pergunta->sala;

        DB::transaction(function () use ($request, $pergunta) {
            $pergunta->update([
                'texto' => $request->texto,
                'tempo_segundos' => $request->tempo_segundos,
            ]);

            // Recria as 4 alternativas (sempre fixas por posição).
            $pergunta->alternativas()->delete();
            $this->criarAlternativas($pergunta, $request);
        });

        return redirect()
            ->route('professor.salas.show', $sala)
            ->with('success', 'Pergunta atualizada.');
    }

    public function destroy(Pergunta $pergunta): RedirectResponse
    {
        $this->authorize('update', $pergunta->sala);
        $sala = $pergunta->sala;

        $pergunta->delete();

        return redirect()
            ->route('professor.salas.show', $sala)
            ->with('success', 'Pergunta removida.');
    }

    /**
     * Cria a pergunta + 4 alternativas (cores/símbolos fixos) em transação.
     */
    protected function salvarPergunta(StorePerguntaRequest|UpdatePerguntaRequest $request, Sala $sala): void
    {
        DB::transaction(function () use ($request, $sala) {
            $ordem = ($sala->perguntas()->max('ordem') ?? 0) + 1;

            $pergunta = $sala->perguntas()->create([
                'texto' => $request->texto,
                'tempo_segundos' => $request->tempo_segundos,
                'ordem' => $ordem,
            ]);

            $this->criarAlternativas($pergunta, $request);
        });
    }

    /**
     * Cria as 4 alternativas com (cor, símbolo) fixos por posição 1..4.
     */
    protected function criarAlternativas(Pergunta $pergunta, $request): void
    {
        foreach (Sala::CORES as $posicao => $info) {
            $pergunta->alternativas()->create([
                'texto' => $request->alternativas[$posicao]['texto'],
                'cor' => $info['cor'],
                'simbolo' => $info['simbolo'],
                'ordem' => $posicao,
                'correta' => ((int) $request->correta) === $posicao,
            ]);
        }
    }
}
