@extends('layouts.app')
@section('title', 'Ao vivo — ' . $sala->titulo)

@push('head')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <style>
        .alt-proj {
            display: flex; align-items: center; gap: .75rem;
            padding: 1rem 1.25rem; border-radius: .6rem; color: #fff;
            font-size: 1.15rem; font-weight: 600; min-height: 4.5rem;
        }
        .alt-proj-shape {
            display: inline-flex; align-items: center; justify-content: center;
            width: 2rem; height: 2rem; font-size: 1.4rem; flex-shrink: 0;
        }
        .alt-proj-vermelho { background:#e21b3c; }
        .alt-proj-azul     { background:#1368ce; }
        .alt-proj-amarelo  { background:#d89e00; }
        .alt-proj-verde    { background:#26890c; }
        /* QR/pin/link sempre visíveis durante a atividade */
        #qr-painel {
            position: fixed; top: 64px; right: 12px; z-index: 1020;
            width: 190px; padding: .6rem .6rem .4rem; text-align: center;
        }
        #qr-painel img, #qr-painel svg { width: 160px; height: 160px; }
        @media (max-width: 720px) {
            #qr-painel { width: 130px; top: 56px; }
            #qr-painel img, #qr-painel svg { width: 108px; height: 108px; }
        }
    </style>
@endpush

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<input type="hidden" id="sala-id" value="{{ $sala->id }}">
<input type="hidden" id="proxima-url" value="{{ parse_url(route('professor.salas.proxima', $sala), PHP_URL_PATH) }}">
<input type="hidden" id="finalizar-pergunta-url" value="{{ parse_url(route('professor.salas.finalizarPergunta', $sala), PHP_URL_PATH) }}">
<input type="hidden" id="finalizar-url" value="{{ parse_url(route('professor.salas.finalizarJogo', $sala), PHP_URL_PATH) }}">

<div class="container py-4">

    {{-- QR/PIN/link sempre visíveis (lobby, pergunta e resultados) --}}
    <div id="qr-painel" class="card shadow-sm">
        <div class="fw-bold small mb-1">Entrar na sala</div>
        {!! $qrSvg !!}
        <div class="mt-1"><span class="badge bg-secondary">PIN {{ $sala->pin }}</span></div>
        <a href="{{ $entrarUrl }}" target="_blank" rel="noopener" class="small text-decoration-none d-block text-break mt-1">{{ $entrarUrl }}</a>
    </div>

    {{-- ===== LOBBY ===== --}}
    <div id="tela-lobby">
        <div class="row g-4 align-items-center">
            <div class="col-md-5 text-center">
                <div class="card shadow-sm p-3 d-inline-block">
                    {!! $qrSvg !!}
                </div>
                <p class="text-muted small mt-2 mb-0">Escaneie o QR Code ou acesse:</p>
                <p class="fw-bold">{{ $entrarUrl }}</p>
                <div class="badge bg-secondary fs-5">PIN: {{ $sala->pin }}</div>
            </div>
            <div class="col-md-7">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h1 class="h4 mb-0 fw-bold">Sala ao vivo</h1>
                    <span>
                        <span id="rt-status" class="badge bg-warning text-dark me-1">Tempo real: conectando…</span>
                        <span class="badge bg-primary fs-6"><span id="total-jogadores">{{ $sala->jogadores->count() }}</span> aluno(s)</span>
                    </span>
                </div>
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div id="lista-jogadores" class="d-flex flex-wrap gap-2">
                            @forelse ($sala->jogadores as $j)
                                <span id="jog-{{ $j->id }}" class="badge rounded-pill bg-light text-dark border">{{ $j->nome }} {{ $j->sobrenome }}</span>
                            @empty
                                <span class="text-muted small">Aguardando alunos entrarem…</span>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button id="btn-proxima" class="btn btn-gold btn-lg fw-semibold">Iniciar primeira pergunta</button>
                    <a href="{{ route('professor.salas.show', $sala) }}" class="btn btn-link">Cancelar</a>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== PERGUNTA EM ANDAMENTO ===== --}}
    <div id="tela-pergunta" class="d-none">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="badge bg-secondary fs-6" id="pergunta-progresso"></span>
            <span class="badge bg-primary fs-5"><span id="contador">--</span>s</span>
        </div>
        <div class="progress mb-3" style="height: 8px;">
            <div id="barra-tempo" class="progress-bar bg-primary" style="width:100%"></div>
        </div>
        <div class="card shadow-sm mb-3">
            <div class="card-body fs-3 fw-semibold text-center" id="pergunta-texto"></div>
        </div>
        <div id="pergunta-alternativas" class="row g-2 mb-4"></div>
        <div id="aviso-aguardando" class="text-center text-muted">
            <span class="spinner-border spinner-border-sm me-1"></span>
            O resultado aparece automaticamente quando o tempo acabar.
        </div>
        <div id="aviso-recolhimento" class="text-center d-none">
            <span class="spinner-border spinner-border-sm me-1"></span>
            Recolhendo as últimas respostas...
        </div>
    </div>

    {{-- ===== RESULTADOS ===== --}}
    <div id="tela-resultados" class="d-none">
        <h1 class="h4 fw-bold mb-1">Resultados da pergunta</h1>
        <p class="text-muted mb-3"><span id="total-respostas">0</span> de <span id="total-jogadores-resp">0</span> alunos responderam</p>
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <canvas id="grafico" height="120"></canvas>
            </div>
        </div>
        <div id="resultado-enunciado" class="fs-4 fw-semibold text-center mb-3"></div>
        <div class="d-flex justify-content-center mb-4">
            <div id="resultado-correta"></div>
        </div>
        <div class="text-center d-flex gap-2 justify-content-center">
            <button id="btn-proxima-2" class="btn btn-gold btn-lg fw-semibold">Próxima pergunta</button>
            <button id="btn-finalizar-jogo" class="btn btn-danger btn-lg d-none">Finalizar jogo</button>
        </div>
    </div>

    {{-- ===== FIM ===== --}}
    <div id="tela-fim" class="d-none text-center">
        <h1 class="h3 fw-bold mb-2">Jogo encerrado!</h1>
        <a href="{{ parse_url(route('professor.salas.relatorio', $sala), PHP_URL_PATH) }}" class="btn btn-primary btn-lg">Ver relatório</a>
    </div>
</div>

<script>
(function () {
    const salaId = parseInt(document.getElementById('sala-id').value, 10);
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const FORMAS = { triangulo: '▲', losango: '◆', circulo: '●', quadrado: '■' };
    const CORES_HEX = { vermelho: '#e21b3c', azul: '#1368ce', amarelo: '#d89e00', verde: '#26890c' };

    let ehUltima = false;
    let timerId = null;
    let chart = null;
    let finalizando = false;

    function mostrar(id) {
        ['tela-lobby','tela-pergunta','tela-resultados','tela-fim']
            .forEach(t => document.getElementById(t).classList.add('d-none'));
        document.getElementById(id).classList.remove('d-none');
        const qr = document.getElementById('qr-painel');
        if (qr) { qr.classList.toggle('d-none', id === 'tela-fim'); }
    }

    async function post(url) {
        const r = await fetch(url, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
        });
        return r.json();
    }

    // ----- Lobby ao vivo: cada aluno que entra -----
    function adicionarJogador(j) {
        const cont = document.getElementById('lista-jogadores');
        const vazio = cont.querySelector('.text-muted');
        if (vazio) vazio.remove();
        if (document.getElementById('jog-' + j.id)) return;
        const span = document.createElement('span');
        span.id = 'jog-' + j.id;
        span.className = 'badge rounded-pill bg-light text-dark border';
        span.textContent = j.nome + ' ' + j.sobrenome;
        cont.appendChild(span);
    }
    function setTotal(n) { document.getElementById('total-jogadores').textContent = n; }

    // ----- Iniciar/próxima pergunta -----
    async function proxima() {
        finalizando = false;
        const data = await post(document.getElementById('proxima-url').value);
        if (data.fim) { return finalizarJogo(); }
        ehUltima = !!data.eh_ultima;
        document.getElementById('pergunta-texto').textContent = data.texto;
        document.getElementById('pergunta-progresso').textContent =
            'Pergunta ' + data.ordem + ' de ' + data.total_perguntas;
        renderizarAlternativas(data.alternativas || []);
        iniciarContagem(data.termina_em, data.tempo_segundos);
        document.getElementById('aviso-aguardando').classList.remove('d-none');
        document.getElementById('aviso-recolhimento').classList.add('d-none');
        document.getElementById('btn-proxima').textContent = 'Próxima pergunta';
        mostrar('tela-pergunta');
    }

    // Mostra as 4 alternativas (cor + símbolo + texto) na projeção do professor.
    // Não revela qual é a correta enquanto a pergunta está aberta.
    function renderizarAlternativas(alternativas) {
        const cont = document.getElementById('pergunta-alternativas');
        cont.innerHTML = '';
        alternativas.forEach(a => {
            const div = document.createElement('div');
            div.className = 'col-md-6';
            div.innerHTML =
                '<div class="alt-proj alt-proj-' + a.cor + '">' +
                '<span class="alt-proj-shape">' + (FORMAS[a.simbolo] || '■') + '</span>' +
                '<span>' + (a.texto || '') + '</span>' +
                '</div>';
            cont.appendChild(div);
        });
    }

    function iniciarContagem(terminaEmIso, total) {
        clearInterval(timerId);
        contagemTocada = false;
        // Conta o tempo decorrido a partir do RECEBIMENTO (Date.now() - início),
        // e não do termina_em absoluto — assim diferença de relógio entre
        // servidor/professor/aluno não afeta o cronômetro.
        const inicio = Date.now();
        timerId = setInterval(() => {
            const restante = total - (Date.now() - inicio) / 1000;
            const seg = Math.max(0, Math.ceil(restante));
            document.getElementById('contador').textContent = seg;
            document.getElementById('barra-tempo').style.width = (Math.max(0, restante) / total * 100) + '%';
            // Toca o countdown de 10s (1x) ao entrar nos últimos 10 segundos.
            if (restante <= 10 && restante > 0 && !contagemTocada) {
                contagemTocada = true;
                tocarContagem();
            }
            if (restante <= 0) { clearInterval(timerId); iniciarRecolhimento(); }
        }, 200);
    }

    // ----- Som: countdown de 10s (só na tela do professor) -----
    // Usa /storage/10-second-countdown.mp3; se não carregar, bip via Web Audio.
    let audioCtx = null;
    let contagemAudio = null;
    let contagemTocada = false;
    function tocarContagem() {
        try {
            contagemAudio = contagemAudio || new Audio('/storage/10-second-countdown.mp3');
            contagemAudio.currentTime = 0;
            const p = contagemAudio.play();
            if (p && p.catch) p.catch(() => bipContagem());
        } catch (e) { bipContagem(); }
    }
    // Fallback: um bip por segundo (10x) gerado no navegador.
    function bipContagem() {
        let n = 0;
        const id = setInterval(() => {
            beep(660, 0.13, 0.25);
            if (++n >= 10) clearInterval(id);
        }, 1000);
    }
    function beep(freq, dur, vol) {
        try {
            audioCtx = audioCtx || new (window.AudioContext || window.webkitAudioContext)();
            if (audioCtx.state === 'suspended') audioCtx.resume();
            const o = audioCtx.createOscillator(), g = audioCtx.createGain();
            o.type = 'sine'; o.frequency.value = freq;
            g.gain.value = vol; o.connect(g); g.connect(audioCtx.destination);
            const t = audioCtx.currentTime;
            o.start(t); o.stop(t + dur);
            g.gain.setValueAtTime(vol, t);
            g.gain.exponentialRampToValueAtTime(0.0001, t + dur);
        } catch (e) {}
    }

    // Após o tempo: aguarda 3s para recolher respostas de conexões lentas.
    function iniciarRecolhimento() {
        if (finalizando) return;
        document.getElementById('contador').textContent = '⏳';
        document.getElementById('aviso-aguardando').classList.add('d-none');
        document.getElementById('aviso-recolhimento').classList.remove('d-none');
        setTimeout(finalizarPergunta, 3000);
    }

    // ----- Finalizar pergunta -> gráfico (só quando o tempo acaba) -----
    async function finalizarPergunta() {
        if (finalizando) return;      // evita disparo duplo do timer / duplo clique
        finalizando = true;
        clearInterval(timerId);
        const data = await post(document.getElementById('finalizar-pergunta-url').value);
        if (data.erro) { finalizando = false; return; }

        const total = data.total_jogadores || 1;
        document.getElementById('total-respostas').textContent = data.total_respostas;
        document.getElementById('total-jogadores-resp').textContent = data.total_jogadores;

        const formas = ['▲', '◆', '●', '■'];
        const labels = data.resultado.map(r =>
            formas[r.ordem - 1] + '  ' + Math.round((r.qtd / total) * 100) + '%');
        const valores = data.resultado.map(r => r.qtd);
        const cores = data.resultado.map(r =>
            r.correta ? CORES_HEX[r.cor] : CORES_HEX[r.cor] + '99');

        if (chart) chart.destroy();
        chart = new Chart(document.getElementById('grafico'), {
            type: 'bar',
            data: { labels, datasets: [{ data: valores, backgroundColor: cores, borderRadius: 6 }] },
            options: {
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
            },
        });

        // Abaixo do gráfico: enunciado + resposta correta (cor e símbolo).
        document.getElementById('resultado-enunciado').textContent = data.texto || '';
        const corrEl = document.getElementById('resultado-correta');
        if (data.correta) {
            corrEl.innerHTML =
                '<div class="alt-proj alt-proj-' + data.correta.cor + '">' +
                '<span class="alt-proj-shape">' + (FORMAS[data.correta.simbolo] || '■') + '</span>' +
                '<span>' + (data.correta.texto || '') + '</span>' +
                '</div>';
        } else {
            corrEl.innerHTML = '';
        }

        document.getElementById('btn-proxima-2').classList.toggle('d-none', ehUltima);
        document.getElementById('btn-finalizar-jogo').classList.toggle('d-none', !ehUltima);
        mostrar('tela-resultados');
    }

    // ----- Finalizar jogo -----
    async function finalizarJogo() {
        clearInterval(timerId);
        await post(document.getElementById('finalizar-url').value);
        mostrar('tela-fim');
    }

    // ----- Eventos -----
    document.getElementById('btn-proxima').addEventListener('click', proxima);
    document.getElementById('btn-proxima-2').addEventListener('click', proxima);
    document.getElementById('btn-finalizar-jogo').addEventListener('click', finalizarJogo);

    // O app.js (module/deferred) define window.Echo DEPOIS deste script inline.
    // Aguardamos o Echo ficar pronto antes de assinar o canal.
    function ligarEcho() {
        if (!window.Echo) { return setTimeout(ligarEcho, 80); }

        window.Echo.channel('sala.' + salaId)
            .listen('.jogador.entrou', (e) => {
                adicionarJogador(e.jogador);
                setTotal(e.total);
            });

        // Indicador de status da conexão em tempo real.
        try {
            const badge = document.getElementById('rt-status');
            const conn = window.Echo.connector;
            const pusher = conn && conn.pusher; // PusherConnector expõe a instância pusher-js
            if (badge && pusher && pusher.connection) {
                const atualizar = (st) => {
                    const ok = st === 'connected';
                    badge.textContent = ok ? 'Tempo real: conectado' : 'Tempo real: ' + st;
                    badge.className = 'badge ' + (ok ? 'bg-success' : 'bg-warning text-dark');
                };
                atualizar(pusher.connection.state);
                pusher.connection.bind('state_change', (s) => atualizar(s.current));
            }
        } catch (_) { /* o status é só informativo; não pode quebrar a tela */ }
    }
    ligarEcho();
})();
</script>
@endsection
