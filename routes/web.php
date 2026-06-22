<?php

use App\Http\Controllers\ExameController;
use App\Http\Controllers\Exemplos\DashboardController;
use App\Http\Controllers\Exemplos\PrimeVueController;
use App\Http\Controllers\GuiaMedicoController;
use App\Http\Controllers\HistoricoClinicoController;
use App\Http\Controllers\InicioController;
use App\Http\Controllers\PlanoController;
use App\Http\Controllers\ReceitaController;
use App\Http\Controllers\VacinacaoController;
use App\Http\Controllers\Visitante\VisitanteController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\WhatsappSimulador\WhatsappSimuladorController;
use App\Http\Controllers\JornadaInteligente\JornadaInteligenteController;
use App\Http\Controllers\LembreteController;
use App\Http\Controllers\PreparadorConsultaController;

//* Rotas de visitantes - Sem autenticação
Route::get('/', [VisitanteController::class, 'landingPage'])->name('landingPage');

//* Rotas autenticadas
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    // Página Inicial após autenticação
    Route::get('dashboard', [InicioController::class, 'inicioAutenticado'])->name('dashboard');
    Route::get('home')->name('home')->uses([InicioController::class, 'inicioAutenticado']);
    // Route::get('teste_vue')->name('teste_vue')->uses([PrimeVueController::class, 'index']);
    Route::get('url_errada', fn() => abort(404))->name('url_errada');
    // Route::get('relatorio-exemplo', fn() => abort(404))->name('relatorio.exemplo');
    // Route::prefix('exemplos')->name('exemplos.')->group(function () {
    //     Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    //     Route::get('primevue', [PrimeVueController::class, 'index'])->name('primevue');
    // });

    Route::prefix('paciente')->group(function () {
        // Rota específica para o download seguro de arquivos privados
        Route::get('exames/{exame}/download', [ExameController::class, 'download'])->name('exames.download');

        Route::resource('exames', ExameController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
        Route::post('/exames/analisar-ia', [ExameController::class, 'analisarComIA'])->name('exames.analisar-ia');

        Route::get('/{paciente}/vacinacoes', [VacinacaoController::class, 'index'])->name('vacinacoes.index');
        Route::post('/{paciente}/vacinacoes', [VacinacaoController::class, 'store'])->name('vacinacoes.store');
        Route::delete('/vacinacoes/{vacinacao}', [VacinacaoController::class, 'destroy'])->name('vacinacoes.destroy');
        Route::post('/vacinacoes/analisar-ia', [VacinacaoController::class, 'analisarComIA'])->name('vacinacoes.analisar-ia');

        Route::get('/{paciente}/receitas', [ReceitaController::class, 'index'])->name('receitas.index');
        Route::delete('/receitas/{receita}', [ReceitaController::class, 'destroy'])->name('receitas.destroy');
    });

    // Route::prefix('guia-medico')->name('guia.')->group(function () {
    //     Route::get('/', [GuiaMedicoController::class, 'inicio'])->name('inicio');
    //     Route::get('/medicos', [GuiaMedicoController::class, 'medicos'])->name('medicos');
    //     Route::get('/clinicas', [GuiaMedicoController::class, 'clinicas'])->name('clinicas');
    // });
    // Route::get('/meu-plano', [PlanoController::class, 'index'])->name('meu.plano');

    Route::get('/historico-ps', [HistoricoClinicoController::class, 'index'])->name('historico.ps');


    Route::get('/whatsapp-simulador', [WhatsappSimuladorController::class, 'index'])
        ->name('whatsapp-simulador.index');

    Route::post('/whatsapp-simulador/enviar', [WhatsappSimuladorController::class, 'enviar'])
        ->name('whatsapp-simulador.enviar');

    Route::prefix('jornada-inteligente')->name('jornada-inteligente.')->group(function () {
        Route::get('/', [JornadaInteligenteController::class, 'index'])->name('index');
        Route::post('/relatos', [JornadaInteligenteController::class, 'store'])->name('relatos.store');
        Route::post('/resumo', [JornadaInteligenteController::class, 'gerarResumo'])->name('resumo');
    });

    // Rotas do módulo de Lembretes
    Route::get('/lembretes', [LembreteController::class, 'index'])->name('lembretes.index');
    Route::post('/lembretes', [LembreteController::class, 'store'])->name('lembretes.store');
    Route::delete('/lembretes/{lembrete}', [LembreteController::class, 'destroy'])->name('lembretes.destroy');

    // Rotas do módulo Preparador de Consulta
    Route::get('/preparador-consulta', [PreparadorConsultaController::class, 'index'])->name('preparador.index');
    Route::post('/preparador-consulta', [PreparadorConsultaController::class, 'gerar'])->name('preparador.gerar');
});
