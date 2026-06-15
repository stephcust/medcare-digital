<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RelatoSaude extends Model
{
    use HasFactory;

    protected $table = 'relatos_saude';

    protected $fillable = [
        'user_id',
        'categoria',
        'titulo',
        'relato',
        'data_ocorrencia',
        'incluir_no_resumo',
    ];

    protected $casts = [
        'data_ocorrencia' => 'date',
        'incluir_no_resumo' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}