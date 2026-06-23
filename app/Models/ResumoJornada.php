<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResumoJornada extends Model
{
    use HasFactory;

    protected $table = 'resumos_jornada';

    protected $fillable = [
        'user_id',
        'titulo',
        'periodo',
        'secoes',
        'incluir_perguntas',
        'conteudo',
        'origem',
    ];

    protected $casts = [
        'secoes' => 'array',
        'incluir_perguntas' => 'boolean',
        'conteudo' => 'array',
    ];
}
