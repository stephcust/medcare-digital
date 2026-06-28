<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Receita extends Model
{
    protected $table = 'receitas';

    protected $fillable = [
        'user_id',
        'medico',
        'especialidade',
        'medicamentos',
        'status',
        'arquivo_path',
        'arquivo_url',
        'data_emissao',
        'data_validade',
    ];

    protected $casts = [
        'data_emissao' => 'date',
        'data_validade' => 'date',
        'medicamentos' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
