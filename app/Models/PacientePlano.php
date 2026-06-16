<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PacientePlano extends Model
{
    protected $table = 'paciente_plano';
    public $timestamps = false;

    protected $fillable = ['paciente_id', 'plano_id', 'numero_carteirinha', 'vigencia', 'inicio_plano', 'utilizacao_atual'];

    protected $casts = [
        'inicio_plano' => 'date',
        'utilizacao_atual' => 'array'
    ];

    public function plano(): BelongsTo
    {
        return $this->belongsTo(Plano::class, 'plano_id');
    }
}
