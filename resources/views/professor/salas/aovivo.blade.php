@extends('layouts.app')
@section('title', 'Ao vivo — ' . $sala->titulo)

@push('head')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
@endpush

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<input type="hidden" id="sala-id" value="{{ $sala->id }}">
<input type="hidden" id="proxima-url" value="{{ parse_url(route('professor.salas.proxima', $sala), PHP_URL_PATH) }}">
<input type="hidden" id="finalizar-pergunta-url" value="{{ parse_url(route('professor.salas.finalizarPergunta', $sala), PHP_URL_PATH) }}">
<input type="hidden" id="finalizar-url" value="{{ parse_url(route('professor.salas.finalizarJogo', $sala), PHP_URL_PATH) }}">

<div class="container py-4">

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
        <div class="card shadow-sm mb-4">
            <div class="card-body fs-4 fw-semibold text-center" id="pergunta-texto"></div>
        </div>
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
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <canvas id="grafico" height="120"></canvas>
            </div>
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
        iniciarContagem(data.termina_em, data.tempo_segundos);
        document.getElementById('aviso-aguardando').classList.remove('d-none');
        document.getElementById('aviso-recolhimento').classList.add('d-none');
        document.getElementById('btn-proxima').textContent = 'Próxima pergunta';
        mostrar('tela-pergunta');
    }

    function iniciarContagem(terminaEmIso, total) {
        clearInterval(timerId);
        const fim = new Date(terminaEmIso).getTime();
        timerId = setInterval(() => {
            const restante = Math.max(0, (fim - Date.now()) / 1000);
            document.getElementById('contador').textContent = Math.ceil(restante);
            document.getElementById('barra-tempo').style.width = ((restante / total) * 100) + '%';
            if (restante <= 0) { clearInterval(timerId); iniciarRecolhimento(); }
        }, 250);
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
