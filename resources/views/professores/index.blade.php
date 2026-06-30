@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold">Professores</h1>
            <p class="text-muted mb-0">Cadastre e gerencie os professores que poderão criar quizzes.</p>
        </div>
        <a href="{{ route('professores.create') }}" class="btn btn-primary">
            + Novo professor
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            {{-- Busca --}}
            <form method="GET" action="{{ route('professores.index') }}" class="row g-2 mb-4">
                <div class="col-sm-8 col-md-6">
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Buscar por nome ou e-mail...">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-outline-primary">Buscar</button>
                    @if (request('q'))
                        <a href="{{ route('professores.index') }}" class="btn btn-link">Limpar</a>
                    @endif
                </div>
            </form>

            {{-- Tabela --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($professores as $professor)
                            <tr>
                                <td class="fw-semibold">{{ $professor->name }}</td>
                                <td>{{ $professor->email }}</td>
                                <td class="text-end">
                                    <a href="{{ route('professores.edit', $professor) }}" class="btn btn-sm btn-outline-primary">Editar</a>

                                    <form action="{{ route('professores.destroy', $professor) }}" method="POST" class="d-inline" onsubmit="return confirm('Remover o professor {{ addslashes($professor->name) }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">
                                    Nenhum professor cadastrado. Clique em <strong>Novo professor</strong>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $professores->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
