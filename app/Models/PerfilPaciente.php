<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerfilPaciente extends Model
{
    protected $table = 'perfis_pacientes';

    protected $fillable = [
        'user_id',
        'data_nascimento',
        'tipo_sanguineo',
        'alergias_conhecidas',
        'contato_emergencia',
        'peso_kg',
        'altura_cm',
        'peso_atualizado_em',
        'condicoes_cronicas',
        'medicamentos_continuos',
        'cirurgias_anteriores',
        'dispositivos_implantes',
        'observacoes_importantes',
        'contato_emergencia_nome',
        'contato_emergencia_telefone',
        'contato_emergencia_parentesco',
    ];

    protected $casts = [
        'data_nascimento' => 'date',
        'peso_kg' => 'decimal:2',
        'altura_cm' => 'integer',
        'peso_atualizado_em' => 'datetime',
        'condicoes_cronicas' => 'array',
        'medicamentos_continuos' => 'array',
        'cirurgias_anteriores' => 'array',
        'dispositivos_implantes' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
