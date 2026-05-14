<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    protected $table = 'notifications';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = env('DB_CONNECTION', 'mariadb');
    }

    protected $fillable = [
        'id',
        'type',
        'notifiable_type',
        'notifiable_id',
        'data',
        'read_at',
        'created_at',
        'updated_at',
    ];

    public function getDataAttribute()
    {
        return json_decode($this->attributes['data']);
    }
}
