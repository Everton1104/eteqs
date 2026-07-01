@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <a href="{{ route('professor.salas.show', $sala) }}" class="text-decoration-none text-muted small">&larr; Voltar</a>
                    <h1 class="h4 mb-0 mt-2">Nova pergunta — {{ $sala->titulo }}</h1>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('professor.salas.perguntas.store', $sala) }}">
                        @csrf
                        @include('partials.erros')
                        <div class="mb-3">
                            <label for="texto" class="form-label fw-semibold">Enunciado</label>
                            <textarea id="texto" name="texto" rows="2" class="form-control @error('texto') is-invalid @enderror" required autofocus placeholder="Digite a pergunta...">{{ old('texto') }}</textarea>
                            @error('texto') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-4">
                            <label for="tempo_segundos" class="form-label fw-semibold">Tempo para responder (segundos)</label>
                            <input id="tempo_segundos" type="number" min="10" max="300" name="tempo_segundos" value="{{ old('tempo_segundos', 20) }}" class="form-control @error('tempo_segundos') is-invalid @enderror" required>
                            @error('tempo_segundos') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <label class="form-label fw-semibold">Alternativas <span class="text-muted small">(marque a correta)</span></label>
                        @include('professor.perguntas._alternativas')

                        <div class="d-flex gap-2 justify-content-end mt-4">
                            <a href="{{ route('professor.salas.show', $sala) }}" class="btn btn-link">Cancelar</a>
                            <button class="btn btn-primary">Adicionar pergunta</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
