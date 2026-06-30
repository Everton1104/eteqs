@extends('layouts.app')
@section('title', 'Relatório — ' . $sala->titulo)

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <a href="{{ route('professor.salas.show', $sala) }}" class="text-decoration-none text-muted small">&larr; Sala</a>
            <h1 class="h3 mb-1 fw-bold mt-1">Relatório — {{ $sala->titulo }}</h1>
            <p class="text-muted mb-0">{{ $totalPerguntas }} pergunta(s) · {{ $jogadores->count() }} aluno(s)</p>
        </div>
        <form action="{{ route('professor.salas.arquivar', $sala) }}" method="POST" class="d-inline">
            @csrf
            <button class="btn btn-outline-secondary btn-sm">Arquivar sala</button>
        </form>
    </div>

    @if ($jogadores->isEmpty())
        <div class="card shadow-sm">
            <div class="card-body text-center text-muted py-5">
                Nenhum aluno participou desta sala.
            </div>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Aluno</th>
                            <th class="text-center">Acertos</th>
                            <th class="text-center">Aproveitamento</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($jogadores as $i => $jogador)
                            <tr>
                                <td class="text-muted">{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $jogador->nome_completo }}</td>
                                <td class="text-center">{{ $jogador->acertos }} / {{ $totalPerguntas }}</td>
                                <td class="text-center">
                                    @php $pct = $totalPerguntas > 0 ? round($jogador->acertos / $totalPerguntas * 100) : 0; @endphp
                                    <span class="badge {{ $pct >= 70 ? 'bg-success' : ($pct >= 50 ? 'bg-warning text-dark' : 'bg-danger') }}">{{ $pct }}%</span>
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
