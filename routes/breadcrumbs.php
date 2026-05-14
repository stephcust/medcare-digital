<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

// APP
Breadcrumbs::before(function (BreadcrumbTrail $trail) {
    $appName = config('app.name');
    $trail->push($appName, route('landingPage'));
});

// APP > [Início]
Breadcrumbs::for('dashboard', function (BreadcrumbTrail $trail) {
    $trail->push('Início', route('dashboard'));
});

// APP > [Exemplos]
Breadcrumbs::for('exemplos.', function (BreadcrumbTrail $trail) {
    $trail->push('Exemplos');
});

// APP > Exemplos > [Dashboard]
Breadcrumbs::for('exemplos.dashboard', function (BreadcrumbTrail $trail) {
    $trail->parent('exemplos.');
    $trail->push('Dashboard', route('exemplos.dashboard'));
});

// APP > Exemplos > [Dashboard]
Breadcrumbs::for('exemplos.primevue', function (BreadcrumbTrail $trail) {
    $trail->parent('exemplos.');
    $trail->push('PrimeVue', route('exemplos.primevue'));
});

// APP > Exemplos > [Tarefas]
Breadcrumbs::for('tarefa.index', function (BreadcrumbTrail $trail) {
    $trail->parent('exemplos.');
    $trail->push('Listar Tarefas', route('tarefa.index'));
});
Breadcrumbs::for('tarefa.create', function (BreadcrumbTrail $trail) {
    $trail->parent('tarefa.index');
    $trail->push('Nova Tarefa', route('tarefa.create'));
});
Breadcrumbs::for('tarefa.edit', function (BreadcrumbTrail $trail) {
    $tarefa = request()->route()->parameter('tarefa');
    $trail->parent('tarefa.index');
    $trail->push('Editar Tarefa', route('tarefa.edit', $tarefa));
});
Breadcrumbs::for('tarefa.show', function (BreadcrumbTrail $trail) {
    $tarefa = request()->route()->parameter('tarefa');
    $trail->parent('tarefa.index');
    $trail->push('Ver Tarefa', route('tarefa.show', $tarefa));
});
