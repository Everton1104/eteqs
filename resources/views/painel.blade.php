@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="painel-hero mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h1 class="h3 mb-1 fw-bold">Olá, {{ Auth::user()->name }}!</h1>
                <p class="mb-0 opacity-75">Painel do Administrador &middot; Sistema de Perguntas e Respostas</p>
            </div>
            <span class="badge bg-light text-dark px-3 py-2 fs-6">
                <span class="eteqs-gold">&#9679;</span> ETEQS
            </span>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <a href="{{ route('professor.home') }}" class="text-decoration-none">
                <div class="card h-100 shadow-sm border-primary">
                    <div class="card-body">
                        <h2 class="h5 card-title text-dark">Minhas salas</h2>
                        <p class="card-text text-muted">Crie salas com perguntas de múltipla escolha, entre na sala e inicie o jogo ao vivo.</p>
                        <span class="btn btn-primary">Gerenciar salas</span>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6">
            <a href="{{ route('professores.index') }}" class="text-decoration-none">
                <div class="card h-100 shadow-sm border-primary">
                    <div class="card-body">
                        <h2 class="h5 card-title text-dark">Professores</h2>
                        <p class="card-text text-muted">Cadastre e remova os professores do sistema.</p>
                        <span class="btn btn-primary">Gerenciar professores</span>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
