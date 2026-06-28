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
        'arquivo_path',
        'arquivo_url',
    ];

    protected $casts = [
        'data_aplicacao' => 'date',
        'data_proxima_dose' => 'date',
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }
}
