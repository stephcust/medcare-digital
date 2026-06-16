<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medico extends Model
{
    protected $table = 'medicos';

    // Desativa os timestamps padrão caso as colunas usem snake_case em português
    const CREATED_AT = 'criado_em';
    const UPDATED_AT = 'atualizado_em';

    protected $fillable = [
        'nome',
        'especialidade',
        'status',
        'avaliacao',
        'distancia',
        'telefone'
    ];
}
