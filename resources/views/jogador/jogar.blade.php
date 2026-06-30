@extends('layouts.jogo')
@section('title', 'Jogar — ' . $sala->titulo)

@section('content')
<input type="hidden" id="sala-id" value="{{ $sala->id }}">
<input type="hidden" id="pin" value="{{ $sala->pin }}">
<input type="hidden" id="csrf" value="{{ csrf_token() }}">

{{-- Aguardando --}}
<div id="tela-aguardar" class="card shadow border-0 text-center" style="border-radius: 1rem;">
    <div class="card-body p-5">
        <h1 class="h4 fw-bold mb-2">Olá, {{ $jogador->nome }}!</h1>
        <p class="text-muted mb-0">Aguardando o professor iniciar a pergunta…</p>
        <div class="spinner-border text-primary mt-3" role="status"></div>
    </div>
</div>

{{-- Pergunta em andamento --}}
<div id="tela-pergunta" class="d-none">
    <div class="text-center text-white mb-3">
        <div class="d-inline-block bg-white text-dark rounded-circle fw-bold"
             style="width:54px;height:54px;line-height:54px;font-size:1.5rem;" id="contador">--</div>
        <div class="small" id="progresso"></div>
    </div>
    <div class="card shadow border-0 mb-3" style="border-radius: .9rem;">
        <div class="card-body text-center fs-5 fw-semibold" id="pergunta-texto"></div>
    </div>
    <div class="row g-2" id="alternativas"></div>
</div>

{{-- Resposta enviada --}}
<div id="tela-respondida" class="card shadow border-0 text-center d-none" style="border-radius: 1rem;">
    <div class="card-body p-5">
        <h1 class="h4 fw-bold mb-2">Resposta enviada!</h1>
        <p class="text-muted mb-0">Aguarde o resultado…</p>
        <div class="spinner-border text-primary mt-3" role="status"></div>
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
    let timerId = null;

    function mostrar(id) {
        ['tela-aguardar','tela-pergunta','tela-respondida','tela-resultado','tela-final']
            .forEach(t => document.getElementById(t).classList.add('d-none'));
        document.getElementById(id).classList.remove('d-none');
    }

    function iniciarContagem(terminaEmIso) {
        clearInterval(timerId);
        const fim = new Date(terminaEmIso).getTime();
        timerId = setInterval(() => {
            const restante = Math.max(0, Math.round((fim - Date.now()) / 1000));
            document.getElementById('contador').textContent = restante;
            if (restante <= 0) clearInterval(timerId);
        }, 250);
    }

    function renderPergunta(e) {
        perguntaAtual = e.pergunta_id;
        minhaAlternativa = null;
        document.getElementById('pergunta-texto').textContent = e.texto;
        document.getElementById('progresso').textContent =
            'Pergunta ' + e.ordem + ' de ' + e.total_perguntas;

        const cont = document.getElementById('alternativas');
        cont.innerHTML = '';
        e.alternativas.forEach(a => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-jogador btn-jogador-' + a.cor;
            btn.dataset.id = a.id;
            btn.innerHTML = '<span>' + (FORMAS[a.simbolo] || '■') + '</span>';
            btn.onclick = () => responder(a.id, btn);
            const col = document.createElement('div');
            col.className = 'col-6';
            col.appendChild(btn);
            cont.appendChild(col);
        });

        iniciarContagem(e.termina_em);
        mostrar('tela-pergunta');
    }

    function responder(altId, btn) {
        if (!perguntaAtual) return;
        minhaAlternativa = altId;
        document.querySelectorAll('#alternativas button').forEach(b => b.disabled = true);
        btn.style.opacity = '1';
        btn.style.transform = 'scale(1.03)';
        mostrar('tela-respondida');

        fetch('/j/' + pin + '/responder', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify({ pergunta_id: perguntaAtual, alternativa_id: altId }),
        }).then(r => r.json()).catch(() => {});
    }

    function mostrarResultado(e) {
        clearInterval(timerId);
        const acertei = minhaAlternativa !== null && minhaAlternativa === e.alternativa_correta_id;
        if (acertei) minhaPontuacao++;
        document.getElementById('resultado-icon').textContent = acertei ? '✅' : '❌';
        document.getElementById('resultado-msg').textContent = acertei ? 'Você acertou!' : 'Resposta incorreta';
        document.getElementById('pontuacao-atual').textContent = minhaPontuacao;
        mostrar('tela-resultado');
    }

    function mostrarFinal() {
        clearInterval(timerId);
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
