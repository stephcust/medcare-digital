<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoricoClinico extends Model
{
    protected $table = 'historico_clinico';

    public const CREATED_AT = 'criado_em';
    public const UPDATED_AT = 'atualizado_em';

    protected $fillable = [
        'paciente_id',
        'motivo_atendimento',
        'gravidade',
        'data_atendimento',
        'local_atendimento',
        'medico_nome',
        'diagnostico',
        'tratamento',
        'exames_realizados',
        'medicamentos',
        'desfecho',
        'acompanhamento',
        'arquivo_path',
        'arquivo_url',
        'origem',
        'relato_original',
        'observacoes',
    ];

    protected $casts = [
        'data_atendimento' => 'datetime',
        'exames_realizados' => 'array',
        'medicamentos' => 'array',
    ];

    protected $attributes = [
        'origem' => 'manual',
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }
}
