<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lembrete extends Model
{
    use HasFactory;

    protected $table = 'lembretes';

    protected $fillable = [
        'user_id',
        'tipo',
        'titulo',
        'descricao',
        'data_hora',
        'status',
        'concluido_em',
        'frequencia',
        'horarios',
        'serie_id',
        'recorrente',
        'intervalo_horas',
        'data_inicio',
        'data_fim',
        'dias_semana',
        'ativo',
        'enviado_em',
        'origem',
    ];

    protected $casts = [
        'data_hora' => 'datetime',
        'concluido_em' => 'datetime',
        'horarios' => 'array',
        'dias_semana' => 'array',
        'recorrente' => 'boolean',
        'intervalo_horas' => 'integer',
        'data_inicio' => 'datetime',
        'data_fim' => 'datetime',
        'ativo' => 'boolean',
        'enviado_em' => 'datetime',
    ];

    protected $attributes = [
        'tipo' => 'outro',
        'status' => 'pendente',
        'ativo' => true,
        'origem' => 'manual',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
