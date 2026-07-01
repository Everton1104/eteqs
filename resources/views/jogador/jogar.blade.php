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
    #quadrantes button.escolhido { filter: brightness(1.12); box-shadow: inset 0 0 0 6px rgba(255,255,255,.6); }
    #quadrantes.travado button { cursor: default; }
    #quadrantes.travado button:not(.escolhido) { filter: brightness(.4) saturate(.4); }
    #quadrantes.travado button.escolhido { box-shadow: inset 0 0 0 6px rgba(255,255,255,.85); }
    /* cronômetro e aviso sobrepostos aos quadrantes */
    #contador-topo { position: fixed; top: 10px; left: 0; right: 0; text-align: center; z-index: 1090; pointer-events: none; }
    #contador-topo span { background: rgba(0,0,0,.55); color:#fff; padding:.3rem 1rem; border-radius:999px; font-weight:700; font-size:1.1rem; }
    #resposta-hint { position: fixed; bottom: 12px; left: 0; right: 0; text-align: center; z-index: 1090; pointer-events: none; }
    #resposta-hint span { background: rgba(0,0,0,.7); color:#fff; padding:.35rem .9rem; border-radius:999px; font-size:.85rem; }
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
    <div id="contador-topo" class="d-none"><span id="contador-texto">--</span></div>
    <div id="resposta-hint" class="d-none"><span id="resposta-hint-texto">Resposta enviada · toque para trocar antes do tempo acabar</span></div>
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

{{-- DEBUG temporário: mostra o estado do tempo real/polling --}}
<div id="debug" style="position:fixed;bottom:0;left:0;right:0;background:rgba(0,0,0,.82);color:#0f0;font:11px monospace;padding:3px 8px;z-index:3000;pointer-events:none;">iniciando…</div>

<script>
(function () {
    const salaId = parseInt(document.getElementById('sala-id').value, 10);
    const pin = document.getElementById('pin').value;
    const csrf = document.getElementById('csrf').value;
    const FORMAS = { triangulo: '▲', losango: '◆', circulo: '●', quadrado: '■' };

    // DEBUG temporário
    function dbg(msg) {
        const el = document.getElementById('debug');
        if (el) el.textContent = msg;
        console.log('[ETEQS]', msg);
    }
    dbg('página carregada');

    let perguntaAtual = null;
    let minhaAlternativa = null;
    let minhaPontuacao = {{ (int) $jogador->pontuacao }};
    let trocaBloqueada = false;
    let perguntaInicioMs = null;
    let perguntaTempo = null;
    let travaId = null;

    function mostrar(id) {
        ['tela-aguardar','tela-pergunta','tela-respondida','tela-resultado','tela-final']
            .forEach(t => document.getElementById(t).classList.add('d-none'));
        document.getElementById(id).classList.remove('d-none');
    }

    function renderPergunta(e) {
        perguntaAtual = e.pergunta_id;
        minhaAlternativa = null;
        trocaBloqueada = false;
        // Cronômetro local: a partir do recebimento (imune a desvio de relógio).
        // Se vier 'restante' (servidor, ex.: reconexão), retroage o início p/ bater.
        perguntaTempo = e.tempo_segundos || 30;
        const restanteInicial = (e.restante != null) ? e.restante : perguntaTempo;
        perguntaInicioMs = Date.now() - (perguntaTempo - restanteInicial) * 1000;

        const cont = document.getElementById('quadrantes');
        cont.classList.remove('travado');
        cont.innerHTML = '';
        e.alternativas.forEach(a => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'q-' + a.cor;
            btn.dataset.id = a.id;
            btn.textContent = FORMAS[a.simbolo] || '■';
            btn.onclick = () => responder(a.id);
            cont.appendChild(btn);
        });

        setHint(null);
        // Retomando uma escolha já feita (reconexão): marca e permite trocar.
        if (e.minha_alternativa) { marcarEscolhido(e.minha_alternativa); }
        iniciarTrava();
        mostrar('tela-pergunta');
        dbg('render pergunta ' + e.pergunta_id + ' · alts=' + (e.alternativas ? e.alternativas.length : 0));
    }

    function marcarEscolhido(altId) {
        minhaAlternativa = altId;
        document.querySelectorAll('#quadrantes button').forEach(b => {
            b.classList.toggle('escolhido', String(b.dataset.id) === String(altId));
        });
    }

    function setHint(msg) {
        const box = document.getElementById('resposta-hint');
        const txt = document.getElementById('resposta-hint-texto');
        if (msg) { txt.textContent = msg; box.classList.remove('d-none'); }
        else { box.classList.add('d-none'); }
    }

    function responder(altId) {
        if (!perguntaAtual || trocaBloqueada) return;
        marcarEscolhido(altId);
        setHint('Resposta enviada · toque em outra para trocar');

        fetch('/j/' + pin + '/responder', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ pergunta_id: perguntaAtual, alternativa_id: altId }),
        }).then(r => r.json()).then(d => {
            if (d && d.erro && d.erro.indexOf('trocar') !== -1) { bloquearTroca(); }
        }).catch(() => {});
    }

    // Cronômetro + trava (tempo decorrido local): troca bloqueada 5s antes do
    // fim (só se já respondeu); primeira resposta fica aberta até o fim.
    function iniciarTrava() {
        clearInterval(travaId);
        document.getElementById('contador-topo').classList.remove('d-none');
        travaId = setInterval(() => {
            const restante = perguntaTempo - (Date.now() - perguntaInicioMs) / 1000;
            document.getElementById('contador-texto').textContent = Math.max(0, Math.ceil(restante)) + 's';
            const respondida = minhaAlternativa !== null;
            if (respondida && restante <= 5) { bloquearTroca(); }
            if (!respondida && restante <= 0) { bloquearTroca(); }
            if (restante <= -4) { clearInterval(travaId); }
        }, 250);
    }

    function bloquearTroca() {
        if (trocaBloqueada) return;
        trocaBloqueada = true;
        document.getElementById('quadrantes').classList.add('travado');
        setHint(minhaAlternativa === null
            ? 'Tempo encerrado · aguardando o resultado'
            : 'Resposta confirmada · aguardando o resultado');
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

    // ----- Retomar de onde parou (ao reconectar) -----
    async function retomarEstado() {
        try {
            const r = await fetch('/j/' + pin + '/estado', { headers: { 'Accept': 'application/json' } });
            if (r.status === 403) return; // sem sessão: continua em "aguardando"
            const d = await r.json();

            if (d.status === 'finalizada') { return mostrarFinal(); }
            if (!d.pergunta_id) { return mostrar('tela-aguardar'); }

            minhaPontuacao = d.pontuacao; // sincroniza com o servidor

            // Tempo já acabou (cálculo do servidor): trava e aguarda o resultado.
            if (d.restante != null && d.restante <= 0) {
                minhaAlternativa = d.minha_alternativa;
                perguntaAtual = d.pergunta_id;
                return mostrar('tela-respondida');
            }

            // Ainda há tempo: mostra os quadrantes. Se já respondeu, a escolha
            // fica marcada e pode trocar (até 5s antes do fim).
            renderPergunta(d);
        } catch (_) { /* sem rede: fica no estado atual e tenta via Echo */ }
    }
    retomarEstado();

    // Backup caso o evento em tempo real se perca (conexão lenta/tarde):
    // recupera a pergunta atual a cada 2,5s, sem re-renderizar a mesma.
    async function sincronizar() {
        try {
            const r = await fetch('/j/' + pin + '/estado', { headers: { Accept: 'application/json' } });
            if (!r.ok) { dbg('sync HTTP ' + r.status); return; }
            const d = await r.json();
            dbg('sync · status=' + d.status + ' pergunta=' + d.pergunta_id + ' alts=' + (d.alternativas ? d.alternativas.length : 0));
            if (d.status === 'finalizada') { return mostrarFinal(); }
            if (d.pergunta_id && d.pergunta_id !== perguntaAtual) { renderPergunta(d); }
        } catch (e) { dbg('sync erro ' + e); }
    }
    setInterval(sincronizar, 2500);

    // ----- Tempo real: só liga quando o Echo estiver pronto -----
    function ligarEcho() {
        if (!window.Echo) { return setTimeout(ligarEcho, 80); }
        window.Echo.channel('sala.' + salaId)
            .listen('.pergunta.iniciada', (e) => { dbg('evento .pergunta.iniciada ' + e.pergunta_id + ' alts=' + (e.alternativas ? e.alternativas.length : 0)); if (e.pergunta_id !== perguntaAtual) renderPergunta(e); })
            .listen('.pergunta.finalizada', (e) => { dbg('evento .pergunta.finalizada'); mostrarResultado(e); })
            .listen('.jogo.finalizado', () => { dbg('evento .jogo.finalizado'); mostrarFinal(); });
        dbg('echo inscrito em sala.' + salaId);
        if (window.Echo.connector && window.Echo.connector.pusher && window.Echo.connector.pusher.connection) {
            window.Echo.connector.pusher.connection.bind('state_change', (s) => dbg('echo ' + s.current));
        }
    }
    ligarEcho();
})();
</script>
@endsection
