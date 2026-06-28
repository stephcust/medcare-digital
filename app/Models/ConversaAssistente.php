<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversaAssistente extends Model
{
    use HasFactory;

    protected $table = 'conversas_assistente';

    protected $fillable = [
        'user_id',
        'canal',
        'autor',
        'texto',
        'arquivo_nome',
        'exame_id',
        'receita_id',
        'vacinacao_id',
        'historico_clinico_id',
        'resumo_jornada_id',
    ];

    protected $casts = [
        'exame_id' => 'integer',
        'receita_id' => 'integer',
        'vacinacao_id' => 'integer',
        'historico_clinico_id' => 'integer',
        'resumo_jornada_id' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function exame(): BelongsTo
    {
        return $this->belongsTo(Exame::class);
    }

    public function receita(): BelongsTo
    {
        return $this->belongsTo(Receita::class);
    }

    public function vacinacao(): BelongsTo
    {
        return $this->belongsTo(Vacinacao::class);
    }

    public function historicoClinico(): BelongsTo
    {
        return $this->belongsTo(HistoricoClinico::class, 'historico_clinico_id');
    }

    public function resumoJornada(): BelongsTo
    {
        return $this->belongsTo(ResumoJornada::class, 'resumo_jornada_id');
    }
}
