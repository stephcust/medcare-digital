<?php

use App\Http\Controllers\Exemplos\DashboardController;
use App\Http\Controllers\Exemplos\PrimeVueController;
use App\Http\Controllers\Exemplos\TarefaController;
use App\Http\Controllers\InicioController;
use App\Http\Controllers\Visitante\VisitanteController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

//* Rotas de visitantes - Sem autenticação
Route::get('/', [VisitanteController::class, 'landingPage'])->name('landingPage');

//* Rotas autenticadas
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    // Página Inicial após autenticação
    Route::get('dashboard', [InicioController::class, 'inicioAutenticado'])->name('dashboard');
    Route::get('home')->name('home')->uses([InicioController::class, 'inicioAutenticado']);
    Route::get('teste_vue')->name('teste_vue')->uses([PrimeVueController::class, 'index']);
    Route::get('url_errada', fn() => abort(404))->name('url_errada');
    Route::get('relatorio-exemplo', fn() => abort(404))->name('relatorio.exemplo');

    // CRUD Multi Page (Listagem, Criação, Edição)
    Route::post('tarefa/{tarefa}/complete', [TarefaController::class, 'complete'])->name('tarefa.complete');
    Route::resource('tarefa', TarefaController::class);

    // Exemplos de Funcionalidades Comuns
    Route::prefix('exemplos')->name('exemplos.')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('primevue', [PrimeVueController::class, 'index'])->name('primevue');
    });
});
