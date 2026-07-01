@extends('layouts.jogo')
@section('title', 'Jogar — ' . $sala->titulo)

@push('head')
<style>
    /* 4 quadrantes ocupando a tela toda — só cor + símbolo */
    #quadrantes {
        position: fixed;
        inset: 0;
        z-index: 1080;
        display: grid;
        grid-template-columns: 1fr 1fr;
        grid-template-rows: 1fr 1fr;
        gap: 4px;
    }
    #quadrantes button {
        border: 0;
        margin: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: clamp(3.5rem, 18vw, 9rem);
        line-height: 1;
        color: #fff;
        cursor: pointer;
        transition: filter .08s, transform .05s;
    }
    #quadrantes button:active { transform: scale(.98); }
    #quadrantes button.bloqueado { cursor: default; }
    #quadrantes button.escolhido { filter: brightness(1.12); box-shadow: inset 0 0 0 6px rgba(255,255,255,.6); }
    #quadrantes button.dim { filter: grayscale(.6) brightness(.7); }
    .q-vermelho { background:#e21b3c; }
    .q-azul     { background:#1368ce; }
    .q-amarelo  { background:#d89e00; }
    .q-verde    { background:#26890c; }
</style>
@endpush

@section('content')
<input type="hidden" id="sala-id" value="{{ $sala->id }}">
<input type="hidden" id="pin" value="{{ $sala->pin }}">
<input type="hidden" id="csrf" value="{{ csrf_token() }}">

{{-- Aguardando --}}
<div id="tela-aguardar" class="card shadow border-0 text-center" style="border-radius: 1rem;">
    <div class="card-body p-5">
        <h1 class="h4 fw-bold mb-2">Olá, {{ $jogador->nome }}!</h1>
        <p class="text-muted mb-0">Aguardando o professor iniciar…</p>
        <div class="spinner-border text-primary mt-3" role="status"></div>
    </div>
</div>

{{-- Pergunta: apenas os 4 quadrantes (cor + símbolo) --}}
<div id="tela-pergunta" class="d-none">
    <div id="quadrantes"></div>
</div>

{{-- Resposta enviada --}}
<div id="tela-respondida" class="card shadow border-0 text-center d-none" style="border-radius: 1rem;">
    <div class="card-body p-5">
        <div class="display-3 mb-1">⏳</div>
        <h1 class="h4 fw-bold mb-1">Resposta enviada!</h1>
        <p class="text-muted mb-0">Aguarde o resultado…</p>
    </div>
</div>

{{-- Resultado da pergunta --}}
<div id="tela-resultado" class="card shadow border-0 text-center d-none" style="border-radius: 1rem;">
    <div class="card-body p-5">
        <div id="resultado-icon" class="display-3 mb-2"></div>
        <h1 class="h4 fw-bold mb-2" id="resultado-msg"></h1>
        <p class="text-muted mb-0">Pontuação: <strong id="pontuacao-atual">{{ $jogador->pontuacao }}</strong></p>
    </div>
</div>

{{-- Fim de jogo --}}
<div id="tela-final" class="card shadow border-0 text-center d-none" style="border-radius: 1rem;">
    <div class="card-body p-5">
        <h1 class="h4 fw-bold mb-2">Jogo encerrado!</h1>
        <p class="text-muted">Sua pontuação final:</p>
        <div class="display-3 fw-bold text-primary" id="pontuacao-final">{{ $jogador->pontuacao }}</div>
    </div>
</div>

<script>
(function () {
    const salaId = parseInt(document.getElementById('sala-id').value, 10);
    const pin = document.getElementById('pin').value;
    const csrf = document.getElementById('csrf').value;
    const FORMAS = { triangulo: '▲', losango: '◆', circulo: '●', quadrado: '■' };

    let perguntaAtual = null;
    let minhaAlternativa = null;
    let minhaPontuacao = {{ (int) $jogador->pontuacao }};

    function mostrar(id) {
        ['tela-aguardar','tela-pergunta','tela-respondida','tela-resultado','tela-final']
            .forEach(t => document.getElementById(t).classList.add('d-none'));
        document.getElementById(id).classList.remove('d-none');
    }

    function renderPergunta(e) {
        perguntaAtual = e.pergunta_id;
        minhaAlternativa = null;

        const cont = document.getElementById('quadrantes');
        cont.innerHTML = '';
        e.alternativas.forEach(a => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'q-' + a.cor;
            btn.dataset.id = a.id;
            btn.textContent = FORMAS[a.simbolo] || '■';
            btn.onclick = () => responder(a.id, btn);
            cont.appendChild(btn);
        });

        mostrar('tela-pergunta');
    }

    function responder(altId, btn) {
        if (!perguntaAtual) return;
        minhaAlternativa = altId;
        document.querySelectorAll('#quadrantes button').forEach(b => {
            b.classList.add('bloqueado');
            if (b !== btn) b.classList.add('dim');
        });
        btn.classList.add('escolhido');

        fetch('/j/' + pin + '/responder', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify({ pergunta_id: perguntaAtual, alternativa_id: altId }),
        }).then(r => r.json()).catch(() => {});

        setTimeout(() => mostrar('tela-respondida'), 450);
    }

    function mostrarResultado(e) {
        const acertei = minhaAlternativa !== null && minhaAlternativa === e.alternativa_correta_id;
        if (acertei) minhaPontuacao++;
        document.getElementById('resultado-icon').textContent = acertei ? '✅' : '❌';
        document.getElementById('resultado-msg').textContent = acertei ? 'Você acertou!' : 'Resposta incorreta';
        document.getElementById('pontuacao-atual').textContent = minhaPontuacao;
        mostrar('tela-resultado');
    }

    function mostrarFinal() {
        document.getElementById('pontuacao-final').textContent = minhaPontuacao;
        mostrar('tela-final');
    }

    window.Echo.channel('sala.' + salaId)
        .listen('.pergunta.iniciada', renderPergunta)
        .listen('.pergunta.finalizada', mostrarResultado)
        .listen('.jogo.finalizado', mostrarFinal);
})();
</script>
@endsection
