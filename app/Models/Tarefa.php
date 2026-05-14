<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tarefa extends Model
{
    use HasFactory;

    /**
     * Os atributos que são atribuíveis em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'titulo',
        'descricao',
        'concluida_em',
        'prioridade',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
