@php
    $sucesso = session('success');
    $erro = session('error');
@endphp

@if ($sucesso)
    <div class="container">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ $sucesso }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    </div>
@endif

@if ($erro)
    <div class="container">
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ $erro }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    </div>
@endif
