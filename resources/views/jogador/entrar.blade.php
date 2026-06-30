@extends('layouts.jogo')
@section('title', 'Entrar na sala')

@section('content')
<div class="card shadow border-0" style="border-radius: 1rem;">
    <div class="card-body p-4">
        <h1 class="h4 text-center fw-bold mb-1">{{ $sala->titulo }}</h1>
        <p class="text-center text-muted mb-4">Informe seus dados para participar.</p>

        <form method="POST" action="{{ route('jogador.registrar', $sala->pin) }}">
            @csrf
            @include('partials.erros')

            <div class="mb-3">
                <label for="nome" class="form-label fw-semibold">Nome</label>
                <input id="nome" type="text" name="nome" value="{{ old('nome') }}"
                       class="form-control form-control-lg @error('nome') is-invalid @enderror"
                       required autofocus placeholder="Seu nome">
                @error('nome') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-4">
                <label for="sobrenome" class="form-label fw-semibold">Sobrenome</label>
                <input id="sobrenome" type="text" name="sobrenome" value="{{ old('sobrenome') }}"
                       class="form-control form-control-lg @error('sobrenome') is-invalid @enderror"
                       required placeholder="Seu sobrenome">
                @error('sobrenome') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100 fw-semibold">Entrar na sala</button>
        </form>
    </div>
</div>
@endsection
