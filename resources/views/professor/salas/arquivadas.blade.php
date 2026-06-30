@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <a href="{{ route('professor.salas.index') }}" class="text-decoration-none text-muted small">&larr; Salas ativas</a>
            <h1 class="h3 mb-1 fw-bold mt-1">Salas arquivadas</h1>
            <p class="text-muted mb-0">Histórico de salas já utilizadas em aulas.</p>
        </div>
    </div>

    @if ($salas->isEmpty())
        <div class="card shadow-sm">
            <div class="card-body text-center text-muted py-5">
                Nenhuma sala arquivada ainda.
            </div>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Sala</th>
                            <th>PIN</th>
                            <th class="text-center">Perguntas</th>
                            <th class="text-center">Alunos</th>
                            <th class="text-center">Criada em</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($salas as $sala)
                            <tr>
                                <td class="fw-semibold">{{ $sala->titulo }}</td>
                                <td><span class="badge bg-secondary">{{ $sala->pin }}</span></td>
                                <td class="text-center">{{ $sala->perguntas_count }}</td>
                                <td class="text-center">{{ $sala->jogadores_count }}</td>
                                <td class="text-center text-muted small">{{ $sala->created_at->format('d/m/Y') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('professor.salas.show', $sala) }}" class="btn btn-sm btn-outline-primary">Ver</a>
                                    <form action="{{ route('professor.salas.desarquivar', $sala) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-secondary" title="Voltar para salas ativas">Desarquivar</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
