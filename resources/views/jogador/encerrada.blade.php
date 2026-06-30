@extends('layouts.jogo')
@section('title', 'Sala encerrada')

@section('content')
<div class="card shadow border-0 text-center" style="border-radius: 1rem;">
    <div class="card-body p-5">
        <h1 class="h4 fw-bold mb-2">Sala encerrada</h1>
        <p class="text-muted mb-0">A sala <strong>{{ $sala->titulo }}</strong> não está mais recebendo participantes.</p>
    </div>
</div>
@endsection
