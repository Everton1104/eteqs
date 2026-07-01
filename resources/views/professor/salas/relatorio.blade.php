@extends('layouts.app')
@section('title', 'Relatório — ' . $sala->titulo)

@push('head')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
@endpush

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
        @php
            $totais = $jogadores->pluck('acertos');
            $media = $totalPerguntas > 0 ? round($totais->avg() / $totalPerguntas * 100) : 0;
        @endphp

        {{-- Gráfico geral --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h2 class="h5 mb-0 fw-bold">Visão geral</h2>
            </div>
            <div class="card-body">
                <div class="row text-center mb-3 g-3">
                    <div class="col"><div class="fw-bold fs-4 text-primary">{{ $media }}%</div><div class="text-muted small">Aproveitamento médio da turma</div></div>
                    <div class="col"><div class="fw-bold fs-4">{{ $totais->max() }}/{{ $totalPerguntas }}</div><div class="text-muted small">Maior pontuação</div></div>
                    <div class="col"><div class="fw-bold fs-4">{{ $totais->min() }}/{{ $totalPerguntas }}</div><div class="text-muted small">Menor pontuação</div></div>
                </div>
                <canvas id="grafico-geral" height="80"></canvas>
            </div>
        </div>

        {{-- Tabela detalhada --}}
        <div class="card shadow-sm">
            <div class="card-header bg-white"><h2 class="h6 mb-0 fw-bold">Acertos por aluno</h2></div>
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
                        @foreach ($jogadores as $jogador)
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

@if (! $jogadores->isEmpty())
<script>
(function () {
    const dados = @json($jogadores->map(fn($j) => ['nome' => $j->nome_completo, 'acertos' => (int) $j->acertos])->values());
    const total = {{ $totalPerguntas }};

    new Chart(document.getElementById('grafico-geral'), {
        type: 'bar',
        data: {
            labels: dados.map(d => d.nome),
            datasets: [{
                label: 'Acertos',
                data: dados.map(d => d.acertos),
                backgroundColor: '#0d47a1',
                borderRadius: 6,
            }],
        },
        options: {
            indexAxis: 'y',
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, max: total, ticks: { stepSize: 1 } },
            },
        },
    });
})();
</script>
@endif
@endsection
