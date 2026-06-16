<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

Breadcrumbs::before(function (BreadcrumbTrail $trail) {
    $trail->push(config('app.name'), route('home'));
});

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

Breadcrumbs::for('receitas.index', function (BreadcrumbTrail $trail) {
    $paciente = request()->route('paciente');

    $trail->push('Minhas Receitas', route('receitas.index', $paciente), [
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

// Breadcrumbs::for('guia.inicio', function (BreadcrumbTrail $trail) {
//     $trail->push('Guia Médico', route('guia.inicio'), [
//         'actions' => [
//             [
//                 'label' => 'Consultar Médicos',
//                 'icon' => 'pi pi-user-md',
//                 'url' => route('guia.medicos'),
//                 'variant' => 'primary',
//             ],
//             [
//                 'label' => 'Consultar Clínicas',
//                 'icon' => 'pi pi-hospital',
//                 'url' => route('guia.clinicas'),
//                 'variant' => 'primary',
//             ],
//             [
//                 'label' => 'Voltar',
//                 'icon' => 'pi pi-arrow-left',
//                 'url' => route('home'),
//                 'variant' => 'secondary',
//             ],
//         ],
//     ]);
// });

// Breadcrumbs::for('guia.medicos', function (BreadcrumbTrail $trail) {
//     $trail->parent('guia.inicio');
//     $trail->push('Médicos', route('guia.medicos'), [
//         'actions' => [
//             [
//                 'label' => 'Voltar',
//                 'icon' => 'pi pi-arrow-left',
//                 'url' => route('guia.inicio'),
//                 'variant' => 'secondary',
//             ],
//         ],
//     ]);
// });

// Breadcrumbs::for('guia.clinicas', function (BreadcrumbTrail $trail) {
//     $trail->parent('guia.inicio');
//     $trail->push('Clínicas', route('guia.clinicas'), [
//         'actions' => [
//             [
//                 'label' => 'Voltar',
//                 'icon' => 'pi pi-arrow-left',
//                 'url' => route('guia.inicio'),
//                 'variant' => 'secondary',
//             ],
//         ],
//     ]);
// });

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