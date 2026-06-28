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
use App\Http\Controllers\PerfilSaudeController;
use Illuminate\Http\Request;

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
        Route::get('exames/{exame}/visualizar', [ExameController::class, 'visualizar'])->name('exames.visualizar');
        Route::get('exames/{exame}/download', [ExameController::class, 'download'])->name('exames.download');

        Route::resource('exames', ExameController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
        Route::post('/exames/analisar-ia', [ExameController::class, 'analisarComIA'])->name('exames.analisar-ia');

        Route::get('/vacinacoes/{vacinacao}/visualizar', [VacinacaoController::class, 'visualizar'])->name('vacinacoes.visualizar');
        Route::get('/vacinacoes/{vacinacao}/download', [VacinacaoController::class, 'download'])->name('vacinacoes.download');
        Route::get('/{paciente}/vacinacoes', [VacinacaoController::class, 'index'])->name('vacinacoes.index');
        Route::post('/{paciente}/vacinacoes', [VacinacaoController::class, 'store'])->name('vacinacoes.store');
        Route::delete('/vacinacoes/{vacinacao}', [VacinacaoController::class, 'destroy'])->name('vacinacoes.destroy');
        Route::post('/vacinacoes/analisar-ia', [VacinacaoController::class, 'analisarComIA'])->name('vacinacoes.analisar-ia');

        Route::get('/receitas/{receita}/visualizar', [ReceitaController::class, 'visualizar'])->name('receitas.visualizar');
        Route::get('/receitas/{receita}/download', [ReceitaController::class, 'download'])->name('receitas.download');
        Route::get('/{paciente}/receitas', [ReceitaController::class, 'index'])->name('receitas.index');
        Route::delete('/receitas/{receita}', [ReceitaController::class, 'destroy'])->name('receitas.destroy');
        Route::get('/pacientes/{paciente}/receitas/inserir', [ReceitaController::class, 'create'])->name('receitas.create');
        Route::post('/receitas/analisar-ia', [ReceitaController::class, 'analisarComIA'])->name('receitas.analisar-ia');
        Route::post('/pacientes/{paciente}/receitas', [ReceitaController::class, 'store'])->name('receitas.store');
    });

    // Atalhos sem parâmetro usados pelo menu lateral.
    Route::get('/meus-documentos/vacinas', function (Request $request) {
        $paciente = $request->user()->paciente()->firstOrCreate([]);

        return redirect()->route('vacinacoes.index', $paciente->id);
    })->name('menu.vacinacoes');

    Route::get('/meus-documentos/receitas', function (Request $request) {
        $paciente = $request->user()->paciente()->firstOrCreate([]);

        return redirect()->route('receitas.index', $paciente->id);
    })->name('menu.receitas');

    Route::get('/perfil-saude', [PerfilSaudeController::class, 'index'])
        ->name('perfil-saude.index');
    Route::put('/perfil-saude', [PerfilSaudeController::class, 'update'])
        ->name('perfil-saude.update');

    // Route::prefix('guia-medico')->name('guia.')->group(function () {
    //     Route::get('/', [GuiaMedicoController::class, 'inicio'])->name('inicio');
    //     Route::get('/medicos', [GuiaMedicoController::class, 'medicos'])->name('medicos');
    //     Route::get('/clinicas', [GuiaMedicoController::class, 'clinicas'])->name('clinicas');
    // });
    // Route::get('/meu-plano', [PlanoController::class, 'index'])->name('meu.plano');

    Route::get('/historico-ps', [HistoricoClinicoController::class, 'index'])
        ->name('historico.ps');
    Route::post('/historico-ps/analisar-ia', [HistoricoClinicoController::class, 'analisarComIA'])
        ->name('historico-clinico.analisar-ia');
    Route::post('/historico-ps', [HistoricoClinicoController::class, 'store'])
        ->name('historico-clinico.store');
    Route::get('/historico-ps/{historico}/visualizar', [HistoricoClinicoController::class, 'visualizar'])
        ->name('historico-clinico.visualizar');
    Route::get('/historico-ps/{historico}/download', [HistoricoClinicoController::class, 'download'])
        ->name('historico-clinico.download');
    Route::get('/historico-ps/{historico}/relatorio', [HistoricoClinicoController::class, 'relatorio'])
        ->name('historico-clinico.relatorio');
    Route::delete('/historico-ps/{historico}', [HistoricoClinicoController::class, 'destroy'])
        ->name('historico-clinico.destroy');


    Route::get('/whatsapp-simulador', [WhatsappSimuladorController::class, 'index'])
        ->name('whatsapp-simulador.index');

    Route::post('/whatsapp-simulador/enviar', [WhatsappSimuladorController::class, 'enviar'])
        ->name('whatsapp-simulador.enviar');

    Route::delete('/whatsapp-simulador/mensagens/{mensagem}', [WhatsappSimuladorController::class, 'destruirMensagem'])
        ->name('whatsapp-simulador.mensagens.destroy');

    Route::delete('/whatsapp-simulador/conversa', [WhatsappSimuladorController::class, 'limparConversa'])
        ->name('whatsapp-simulador.conversa.destroy');

    Route::prefix('jornada-inteligente')->name('jornada-inteligente.')->group(function () {
        Route::get('/', [JornadaInteligenteController::class, 'index'])->name('index');
        Route::post('/relatos', [JornadaInteligenteController::class, 'store'])->name('relatos.store');
        Route::post('/resumo', [JornadaInteligenteController::class, 'gerarResumo'])->name('resumo');
        Route::get('/resumos/{resumo}/visualizar', [JornadaInteligenteController::class, 'visualizar'])
            ->name('resumos.visualizar');
        Route::get('/resumos/{resumo}/download', [JornadaInteligenteController::class, 'download'])
            ->name('resumos.download');
        Route::get('/resumos/{resumo}/imprimir', [JornadaInteligenteController::class, 'imprimir'])
            ->name('resumos.imprimir');
        Route::delete('/resumos/{resumo}', [JornadaInteligenteController::class, 'destruirResumo'])
            ->name('resumos.destroy');
    });

    // Rotas do módulo de Lembretes
    Route::get('/lembretes', [LembreteController::class, 'index'])->name('lembretes.index');
    Route::post('/lembretes', [LembreteController::class, 'store'])->name('lembretes.store');
    Route::patch('/lembretes/{lembrete}/concluir', [LembreteController::class, 'concluir'])->name('lembretes.concluir');
    Route::patch('/lembretes/{lembrete}/adiar', [LembreteController::class, 'adiar'])->name('lembretes.adiar');
    Route::delete('/lembretes/series/{serieId}', [LembreteController::class, 'destroySerie'])->name('lembretes.series.destroy');
    Route::delete('/lembretes/{lembrete}', [LembreteController::class, 'destroy'])->name('lembretes.destroy');

});
