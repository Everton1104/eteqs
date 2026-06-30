@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-semibold">
                    <a href="{{ route('professores.index') }}" class="text-decoration-none text-muted small">&larr; Voltar</a>
                    <h1 class="h4 mb-0 mt-2">Editar professor</h1>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('professores.update', $professor) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">Nome</label>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $professor->name) }}" required autofocus>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">E-mail</label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $professor->email) }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label for="password" class="form-label fw-semibold">Nova senha <span class="text-muted small">(opcional)</span></label>
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password">
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted">Deixe em branco para manter a senha atual.</small>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="password_confirmation" class="form-label fw-semibold">Confirmar nova senha</label>
                                <input id="password_confirmation" type="password" class="form-control" name="password_confirmation">
                            </div>
                        </div>

                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('professores.index') }}" class="btn btn-link">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Salvar alterações</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
