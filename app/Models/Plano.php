<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plano extends Model
{
    protected $table = 'planos';
    const CREATED_AT = 'criado_em';
    const UPDATED_AT = 'atualizado_em';

    protected $fillable = ['nome', 'operadora', 'registro_ans', 'acomodacao', 'coberturas'];

    protected $casts = [
        'coberturas' => 'array'
    ];
}
