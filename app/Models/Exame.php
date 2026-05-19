<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Exame extends Model
{
    use HasFactory;

    /**
     * Os atributos que podem ser preenchidos em massa (Mass Assignment).
     */
    protected $fillable = [
        'user_id',
        'nome',
        'tipo',
        'laboratorio',
        'data_realizacao',
        'arquivo_path',
        'visualizado',
        'origem'
    ];

    /**
     * Define que o campo visualizado deve ser tratado como booleano.
     */
    protected $casts = [
        'visualizado' => 'boolean',
        'data_realizacao' => 'date'
    ];

    /**
     * Relacionamento: Um exame pertence a um único Usuário/Paciente.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
