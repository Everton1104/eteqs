@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold">Minhas salas</h1>
            <p class="text-muted mb-0">Crie salas de perguntas e respostas para as suas aulas.</p>
        </div>
        <a href="{{ route('professor.salas.create') }}" class="btn btn-primary">+ Nova sala</a>
        <a href="{{ route('professor.salas.arquivadas') }}" class="btn btn-outline-secondary">Salas arquivadas</a>
    </div>

    <div class="row g-3">
        @forelse ($salas as $sala)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <h2 class="h5 card-title mb-1">{{ $sala->titulo }}</h2>
                            <span class="badge bg-secondary">PIN {{ $sala->pin }}</span>
                        </div>
                        @if ($sala->descricao)
                            <p class="text-muted small mb-2">{{ $sala->descricao }}</p>
                        @endif
                        <p class="text-muted small mb-3">
                            <i>{{ $sala->perguntas_count }} pergunta(s)</i>
                        </p>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('professor.salas.show', $sala) }}" class="btn btn-sm btn-primary">Abrir</a>
                            <a href="{{ route('professor.salas.edit', $sala) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
                            <form action="{{ route('professor.salas.arquivar', $sala) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-outline-secondary" title="Mover para salas arquivadas">Arquivar</button>
                            </form>
                            <form action="{{ route('professor.salas.destroy', $sala) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Remover a sala {{ addslashes($sala->titulo) }} e todas as suas perguntas?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Excluir</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body text-center text-muted py-5">
                        Você ainda não tem salas. Clique em <strong>+ Nova sala</strong> para começar.
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
