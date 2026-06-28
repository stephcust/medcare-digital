<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paciente extends Model
{
    protected $table = 'pacientes';

    protected $fillable = [
        'user_id',
        'rg',
        'genero',
        'telefone',
        'cep',
        'endereco',
        'cidade',
        'estado',
        'tipo_sanguineo',
        'alergias_conhecidas',
    ];

    public function vacinacoes(): HasMany
    {
        return $this->hasMany(Vacinacao::class, 'paciente_id');
    }

    public function historicosClinicos(): HasMany
    {
        return $this->hasMany(HistoricoClinico::class, 'paciente_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
