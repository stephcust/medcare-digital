<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

Breadcrumbs::before(function (BreadcrumbTrail $trail) {
    $trail->push(config('app.name'), route('home'));
});

// --- MÓDULO DE EXAMES ---
Breadcrumbs::for('exames.index', function (BreadcrumbTrail $trail) {
    $trail->push('Meus Exames', route('exames.index'), [
        'actions' => [
            [
                'label' => 'Anexar exame',
                'icon' => 'pi pi-plus-circle',
                'url' => route('exames.create'),
                'variant' => 'primary',
            ],
            [
                'label' => 'Voltar',
                'icon' => 'pi pi-arrow-left',
                'url' => route('home'),
                'variant' => 'secondary',
            ],
        ],
    ]);
});

Breadcrumbs::for('exames.create', function (BreadcrumbTrail $trail) {
    $trail->parent('exames.index');
    $trail->push('Anexar Exame', route('exames.create'), [
        'actions' => [
            [
                'label' => 'Voltar',
                'icon' => 'pi pi-arrow-left',
                'url' => route('exames.index'),
                'variant' => 'secondary',
            ],
        ],
    ]);
});

Breadcrumbs::for('exames.show', function (BreadcrumbTrail $trail) {
    $exame = request()->route('exame');

    $trail->parent('exames.index');
    $trail->push($exame?->nome ?? 'Detalhes do Exame', route('exames.show', $exame), [
        'actions' => [
            [
                'label' => 'Voltar',
                'icon' => 'pi pi-arrow-left',
                'url' => route('exames.index'),
                'variant' => 'secondary',
            ],
        ],
    ]);
});

// --- MÓDULO DE VACINAÇÕES ---
Breadcrumbs::for('vacinacoes.index', function (BreadcrumbTrail $trail) {
    $paciente = request()->route('paciente');

    $trail->push('Minhas Vacinas', route('vacinacoes.index', $paciente), [
        'actions' => [
            [
                'label' => 'Voltar',
                'icon' => 'pi pi-arrow-left',
                'url' => route('home'),
                'variant' => 'secondary',
            ],
        ],
    ]);
});

// --- MÓDULO DE RECEITAS MÉDICAS (ATUALIZADO) ---
Breadcrumbs::for('receitas.index', function (BreadcrumbTrail $trail) {
    $paciente = request()->route('paciente');

    $trail->push('Minhas Receitas', route('receitas.index', $paciente), [
        'actions' => [
            [
                'label' => 'Anexar receita',
                'icon' => 'pi pi-plus-circle',
                'url' => route('receitas.create', $paciente),
                'variant' => 'primary',
            ],
            [
                'label' => 'Voltar',
                'icon' => 'pi pi-arrow-left',
                'url' => route('home'),
                'variant' => 'secondary',
            ],
        ],
    ]);
});

Breadcrumbs::for('receitas.create', function (BreadcrumbTrail $trail) {
    $paciente = request()->route('paciente');

    $trail->parent('receitas.index');
    $trail->push('Anexar Receita', route('receitas.create', $paciente), [
        'actions' => [
            [
                'label' => 'Voltar',
                'icon' => 'pi pi-arrow-left',
                'url' => route('receitas.index', $paciente),
                'variant' => 'secondary',
            ],
        ],
    ]);
});

// --- HISTÓRICO, LEMBRETES E JORNADA ---
Breadcrumbs::for('historico.ps', function (BreadcrumbTrail $trail) {
    $trail->push('Histórico Clínico', route('historico.ps'), [
        'actions' => [
            [
                'label' => 'Voltar',
                'icon' => 'pi pi-arrow-left',
                'url' => route('home'),
                'variant' => 'secondary',
            ],
        ],
    ]);
});

Breadcrumbs::for('lembretes.index', function (BreadcrumbTrail $trail) {
    $trail->push('Lembretes & Consultas', route('lembretes.index'), [
        'actions' => [
            [
                'label' => 'Voltar',
                'icon' => 'pi pi-arrow-left',
                'url' => route('home'),
                'variant' => 'secondary',
            ],
        ],
    ]);
});

Breadcrumbs::for('jornada-inteligente.index', function (BreadcrumbTrail $trail) {
    $trail->push('Jornada Inteligente', route('jornada-inteligente.index'), [
        'actions' => [
            [
                'label' => 'Voltar',
                'icon' => 'pi pi-arrow-left',
                'url' => route('home'),
                'variant' => 'secondary',
            ],
        ],
    ]);
});
