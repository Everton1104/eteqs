<?php

use App\Http\Controllers\PainelController;
use App\Http\Controllers\PerguntaController;
use App\Http\Controllers\ProfessorController;
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

        // Arquivar / desarquivar salas + listagem de salas já utilizadas.
        Route::get('salas-arquivadas', [SalaController::class, 'arquivadas'])->name('salas.arquivadas');
        Route::post('salas/{sala}/arquivar', [SalaController::class, 'arquivar'])->name('salas.arquivar');
        Route::post('salas/{sala}/desarquivar', [SalaController::class, 'desarquivar'])->name('salas.desarquivar');

        // Perguntas aninhadas na sala (shallow: create/store com sala; edit/update/destroy direto).
        Route::resource('salas.perguntas', PerguntaController::class)
            ->shallow()
            ->except(['show']);
    });

