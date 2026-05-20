<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vacinacao extends Model
{
    protected $table = 'vacinacoes';

    protected $fillable = [
        'paciente_id',
        'nome_vacina',
        'fabricante',
        'lote',
        'numero_dose',
        'data_aplicacao',
        'data_proxima_dose',
        'observacoes',
        'usuario_id'
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}