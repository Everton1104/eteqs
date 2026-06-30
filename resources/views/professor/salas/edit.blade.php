@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <a href="{{ route('professor.salas.show', $sala) }}" class="text-decoration-none text-muted small">&larr; Voltar</a>
                    <h1 class="h4 mb-0 mt-2">Editar sala</h1>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('professor.salas.update', $sala) }}">
                        @csrf @method('PUT')
                        <div class="mb-3">
                            <label for="titulo" class="form-label fw-semibold">Título da sala</label>
                            <input id="titulo" type="text" class="form-control @error('titulo') is-invalid @enderror" name="titulo" value="{{ old('titulo', $sala->titulo) }}" required>
                            @error('titulo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="descricao" class="form-label fw-semibold">Descrição <span class="text-muted small">(opcional)</span></label>
                            <textarea id="descricao" class="form-control" name="descricao" rows="2">{{ old('descricao', $sala->descricao) }}</textarea>
                        </div>
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('professor.salas.show', $sala) }}" class="btn btn-link">Cancelar</a>
                            <button class="btn btn-primary">Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
