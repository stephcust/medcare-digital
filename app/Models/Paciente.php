<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paciente extends Model
{
    protected $table = 'pacientes';

    protected $fillable = [
        'nome_completo',
        'data_nascimento',
        'cpf',
        'rg',
        'genero',
        'telefone',
        'email',
        'cep',
        'endereco',
        'cidade',
        'estado',
        'tipo_sanguineo',
        'alergias_conhecidas'
    ];

    // Mapeia os campos de data para serem tratados como objetos Carbon pelo Laravel
    protected $casts = [
        'data_nascimento' => 'date',
    ];

    /**
     * Relacionamento: Um paciente pode ter muitas vacinações registradas.
     */
    public function vacinacoes(): HasMany
    {
        return $this->hasMany(Vacinacao::class, 'paciente_id');
    }
}