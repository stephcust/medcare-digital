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
    ];

    protected $casts = [
        'data_hora' => 'datetime',
        'concluido_em' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}