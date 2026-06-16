<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clinica extends Model
{
    protected $table = 'clinicas';

    const CREATED_AT = 'criado_em';
    const UPDATED_AT = 'atualizado_em';

    protected $fillable = [
        'nome', 'tipo', 'avaliacao', 'distancia', 'telefone', 'servicos'
    ];

    // Converte automaticamente o array do PostgreSQL para o PHP
    protected $casts = [
        'servicos' => 'array'
    ];
}
