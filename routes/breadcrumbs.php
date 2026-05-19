<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

Breadcrumbs::before(function (BreadcrumbTrail $trail) {
    $trail->push(config('app.name'), route('home'));
});

Breadcrumbs::for('exames.index', function (BreadcrumbTrail $trail) {
    $trail->push('Meus Exames', route('exames.index'));
});

Breadcrumbs::for('exames.create', function (BreadcrumbTrail $trail) {
    $trail->parent('exames.index');
    $trail->push('Anexar Exame', route('exames.create'));
});

Breadcrumbs::for('exames.show', function (BreadcrumbTrail $trail) {
    $exame = request()->route('exame');

    $trail->parent('exames.index');
    $trail->push($exame?->nome ?? 'Detalhes do Exame', route('exames.show', $exame));
});
