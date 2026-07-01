<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSalaRequest;
use App\Models\Sala;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SalaController extends Controller
{
    /**
     * Lista as salas ativas (não arquivadas) do usuário logado.
     */
    public function index(Request $request): View
    {
        $salas = Sala::doUsuario($request->user()->id)
            ->where('arquivada', false)
            ->withCount('perguntas')
            ->latest()
            ->get();

        return view('professor.salas.index', compact('salas'));
    }

    /**
     * Lista as salas arquivadas (já utilizadas) do usuário.
     */
    public function arquivadas(Request $request): View
    {
        $salas = Sala::doUsuario($request->user()->id)
            ->where('arquivada', true)
            ->withCount(['perguntas', 'jogadores'])
            ->latest()
            ->get();

        return view('professor.salas.arquivadas', compact('salas'));
    }

    public function create(): View
    {
        return view('professor.salas.create');
    }

    public function store(StoreSalaRequest $request): RedirectResponse
    {
        $sala = Sala::create([
            'user_id' => $request->user()->id,
            'titulo' => $request->titulo,
            'descricao' => $request->descricao,
            'pin' => Sala::gerarPinUnico(),
            'status' => Sala::STATUS_AGUARDANDO,
        ]);

        return redirect()
            ->route('professor.salas.show', $sala)
            ->with('success', 'Sala criada. Adicione as perguntas.');
    }

    public function show(Request $request, Sala $sala): View
    {
        $this->authorize('view', $sala);
        $sala->load(['perguntas.alternativas']);

        return view('professor.salas.show', compact('sala'));
    }

    public function edit(Request $request, Sala $sala): View
    {
        $this->authorize('update', $sala);

        return view('professor.salas.edit', compact('sala'));
    }

    public function update(StoreSalaRequest $request, Sala $sala): RedirectResponse
    {
        $this->authorize('update', $sala);

        $sala->update($request->only('titulo', 'descricao'));

        return redirect()
            ->route('professor.salas.show', $sala)
            ->with('success', 'Sala atualizada.');
    }

    public function destroy(Request $request, Sala $sala): RedirectResponse
    {
        $this->authorize('delete', $sala);

        $sala->delete();

        return redirect()
            ->route('professor.salas.index')
            ->with('success', 'Sala removida.');
    }

    /**
     * Arquiva uma sala (move para a listação de salas já utilizadas).
     */
    public function arquivar(Request $request, Sala $sala): RedirectResponse
    {
        $this->authorize('update', $sala);

        $sala->update(['arquivada' => true]);

        return redirect()
            ->route('professor.salas.index')
            ->with('success', 'Sala arquivada.');
    }

    /**
     * Desarquiva uma sala (volta para as salas ativas).
     */
    public function desarquivar(Request $request, Sala $sala): RedirectResponse
    {
        $this->authorize('update', $sala);

        $sala->update(['arquivada' => false]);

        return redirect()
            ->route('professor.salas.arquivadas')
            ->with('success', 'Sala desarquivada.');
    }

    /**
     * Encerra a sala: bloqueia a entrada de novos alunos (status = finalizada).
     */
    public function encerrar(Request $request, Sala $sala): RedirectResponse
    {
        $this->authorize('update', $sala);

        $sala->update([
            'status' => Sala::STATUS_FINALIZADA,
            'pergunta_atual' => null,
            'pergunta_iniciada_em' => null,
        ]);

        return redirect()
            ->route('professor.salas.show', $sala)
            ->with('success', 'Sala encerrada. Ninguém mais pode entrar.');
    }

    /**
     * Duplica a sala (título, descrição e todas as perguntas/alternativas),
     * com um novo PIN e status aguardando.
     */
    public function duplicar(Request $request, Sala $sala): RedirectResponse
    {
        $this->authorize('view', $sala);
        $sala->load(['perguntas.alternativas']);

        $nova = DB::transaction(function () use ($sala, $request) {
            $copia = Sala::create([
                'user_id' => $request->user()->id,
                'titulo' => $sala->titulo.' (cópia)',
                'descricao' => $sala->descricao,
                'pin' => Sala::gerarPinUnico(),
                'status' => Sala::STATUS_AGUARDANDO,
            ]);

            foreach ($sala->perguntas as $pergunta) {
                $novaPergunta = $copia->perguntas()->create([
                    'texto' => $pergunta->texto,
                    'tempo_segundos' => $pergunta->tempo_segundos,
                    'ordem' => $pergunta->ordem,
                ]);
                foreach ($pergunta->alternativas as $alt) {
                    $novaPergunta->alternativas()->create([
                        'texto' => $alt->texto,
                        'cor' => $alt->cor,
                        'simbolo' => $alt->simbolo,
                        'correta' => $alt->correta,
                        'ordem' => $alt->ordem,
                    ]);
                }
            }

            return $copia;
        });

        return redirect()
            ->route('professor.salas.show', $nova)
            ->with('success', 'Sala duplicada (novas perguntas, novo PIN).');
    }
}
