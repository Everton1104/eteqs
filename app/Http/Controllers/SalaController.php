<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSalaRequest;
use App\Models\Sala;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
}
