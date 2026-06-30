<?php

use App\Http\Controllers\JogadorController;
use App\Http\Controllers\PainelController;
use App\Http\Controllers\PerguntaController;
use App\Http\Controllers\ProfessorController;
use App\Http\Controllers\SalaAoVivoController;
use App\Http\Controllers\SalaController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Raiz: redireciona conforme autenticação e papel do usuário.
Route::get('/', function () {
    $user = auth()->user();

    if (! $user) {
        return redirect('/login');
    }

    return redirect($user->isAdmin() ? '/painel' : '/professor');
});

// Autenticação sem cadastro público (admin é criado via seed).
Auth::routes(['register' => false]);

// Jogador (aluno) — público, entra via QR/PIN, sem conta.
Route::get('/j/{pin}', [JogadorController::class, 'entrar'])->name('jogador.entrar');
Route::post('/j/{pin}', [JogadorController::class, 'registrar'])->name('jogador.registrar');
Route::get('/j/{pin}/jogar', [JogadorController::class, 'jogar'])->name('jogador.jogar');
Route::post('/j/{pin}/responder', [JogadorController::class, 'responder'])->name('jogador.responder');

// Área do administrador (gerencia professores + usa as salas como professor).
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/painel', [PainelController::class, 'index'])->name('painel');

    // Gestão de professores (CRUD).
    Route::resource('professores', ProfessorController::class)
        ->parameter('professores', 'professor')
        ->except(['show']);
});

// Área do professor (admin também acessa): cria salas e perguntas.
Route::middleware(['auth', 'role:admin,professor'])
    ->prefix('professor')
    ->name('professor.')
    ->group(function () {
        // Landing do professor = suas salas.
        Route::get('/', [SalaController::class, 'index'])->name('home');

        Route::resource('salas', SalaController::class);

        // Sala ao vivo (lobby, perguntas, resultados, relatório).
        Route::post('salas/{sala}/iniciar', [SalaAoVivoController::class, 'iniciar'])->name('salas.iniciar');
        Route::get('salas/{sala}/ao-vivo', [SalaAoVivoController::class, 'aoVivo'])->name('salas.aovivo');
        Route::post('salas/{sala}/proxima-pergunta', [SalaAoVivoController::class, 'proximaPergunta'])->name('salas.proxima');
        Route::post('salas/{sala}/finalizar-pergunta', [SalaAoVivoController::class, 'finalizarPergunta'])->name('salas.finalizarPergunta');
        Route::post('salas/{sala}/finalizar', [SalaAoVivoController::class, 'finalizar'])->name('salas.finalizarJogo');
        Route::get('salas/{sala}/relatorio', [SalaAoVivoController::class, 'relatorio'])->name('salas.relatorio');

        // Arquivar / desarquivar salas + listagem de salas já utilizadas.
        Route::get('salas-arquivadas', [SalaController::class, 'arquivadas'])->name('salas.arquivadas');
        Route::post('salas/{sala}/arquivar', [SalaController::class, 'arquivar'])->name('salas.arquivar');
        Route::post('salas/{sala}/desarquivar', [SalaController::class, 'desarquivar'])->name('salas.desarquivar');

        // Perguntas aninhadas na sala (shallow: create/store com sala; edit/update/destroy direto).
        Route::resource('salas.perguntas', PerguntaController::class)
            ->shallow()
            ->except(['show']);
    });

