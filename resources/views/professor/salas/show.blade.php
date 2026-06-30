@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
        <div>
            <a href="{{ route('professor.salas.index') }}" class="text-decoration-none text-muted small">&larr; Minhas salas</a>
            <h1 class="h3 mb-1 fw-bold mt-1">{{ $sala->titulo }}</h1>
            @if ($sala->descricao)
                <p class="text-muted mb-0">{{ $sala->descricao }}</p>
            @endif
        </div>
        <div class="text-end">
            <span class="badge bg-secondary fs-6 mb-2 d-block">PIN: {{ $sala->pin }}</span>
            <a href="{{ route('professor.salas.edit', $sala) }}" class="btn btn-sm btn-outline-secondary">Editar sala</a>
        </div>
    </div>

    <div class="alert alert-light border d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div><strong>{{ $sala->perguntas->count() }}</strong> pergunta(s) nesta sala.</div>
        <div class="d-flex gap-2">
            <a href="{{ route('professor.salas.perguntas.create', $sala) }}" class="btn btn-primary btn-sm">+ Adicionar pergunta</a>
            {{-- Botão "Iniciar sala" ativado na Parte 2 (ao vivo) --}}
            <button class="btn btn-gold btn-sm" disabled title="Disponível na etapa ao vivo">Iniciar sala ao vivo</button>
        </div>
    </div>

    @if ($sala->perguntas->isEmpty())
        <div class="card shadow-sm">
            <div class="card-body text-center text-muted py-5">
                Nenhuma pergunta ainda. Clique em <strong>+ Adicionar pergunta</strong>.
            </div>
        </div>
    @else
        <ol class="list-group list-group-numbered shadow-sm">
            @foreach ($sala->perguntas as $pergunta)
                <li class="list-group-item">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                        <div class="fw-semibold">{{ $pergunta->texto }}</div>
                        <span class="badge bg-light text-dark border">{{ $pergunta->tempo_segundos }}s</span>
                    </div>
                    <div class="row g-2 mb-2">
                        @foreach ($pergunta->alternativas as $alt)
                            <div class="col-md-6">
                                <div class="alt-mini alt-{{ $alt->cor }} {{ $alt->correta ? 'alt-correta' : '' }}">
                                    <span class="alt-shape">{{ \App\Models\Sala::CORES[$alt->ordem]['forma'] ?? '■' }}</span>
                                    <span>{{ $alt->texto }}</span>
                                    @if ($alt->correta) <span class="badge bg-success ms-auto">correta</span> @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('professor.perguntas.edit', $pergunta) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                        <form action="{{ route('professor.perguntas.destroy', $pergunta) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Remover esta pergunta?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Excluir</button>
                        </form>
                    </div>
                </li>
            @endforeach
        </ol>
    @endif
</div>
@endsection
