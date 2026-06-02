<?php

use App\Http\Controllers\ExameController;
use App\Http\Controllers\Exemplos\DashboardController;
use App\Http\Controllers\Exemplos\PrimeVueController;
use App\Http\Controllers\GuiaMedicoController;
use App\Http\Controllers\HistoricoClinicoController;
use App\Http\Controllers\InicioController;
use App\Http\Controllers\ReceitaController;
use App\Http\Controllers\VacinacaoController;
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
    // Route::get('teste_vue')->name('teste_vue')->uses([PrimeVueController::class, 'index']);
    Route::get('url_errada', fn() => abort(404))->name('url_errada');
    Route::get('relatorio-exemplo', fn() => abort(404))->name('relatorio.exemplo');
    // Route::prefix('exemplos')->name('exemplos.')->group(function () {
    //     Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    //     Route::get('primevue', [PrimeVueController::class, 'index'])->name('primevue');
    // });

    Route::prefix('paciente')->group(function () {
        // Rota específica para o download seguro de arquivos privados
        Route::get('exames/{exame}/download', [ExameController::class, 'download'])->name('exames.download');

        Route::resource('exames', ExameController::class)->only(['index', 'create', 'store', 'show', 'destroy']);

        Route::get('/{paciente}/vacinacoes', [VacinacaoController::class, 'index'])->name('vacinacoes.index');
        Route::post('/{paciente}/vacinacoes', [VacinacaoController::class, 'store'])->name('vacinacoes.store');
        Route::delete('/vacinacoes/{vacinacao}', [VacinacaoController::class, 'destroy'])->name('vacinacoes.destroy');

        Route::get('/{paciente}/receitas', [ReceitaController::class, 'index'])->name('receitas.index');
        Route::delete('/receitas/{receita}', [ReceitaController::class, 'destroy'])->name('receitas.destroy');
    });


    Route::prefix('guia-medico')->name('guia.')->group(function () {
        Route::get('/', [GuiaMedicoController::class, 'inicio'])->name('inicio');
        Route::get('/medicos', [GuiaMedicoController::class, 'medicos'])->name('medicos');
        Route::get('/clinicas', [GuiaMedicoController::class, 'clinicas'])->name('clinicas');
    });

    Route::get('/historico-ps', [HistoricoClinicoController::class, 'index'])->name('historico.ps');
});
