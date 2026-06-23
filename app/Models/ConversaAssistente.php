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
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
