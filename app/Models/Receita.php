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
        'medicamentos',
        'caminho_arquivo',
        'data_emissao',
    ];

    protected $casts = [
        'data_emissao' => 'date',
    ];

    /**
     * Relacionamento: A receita pertence a um usuário.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
