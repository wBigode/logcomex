<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pokemon extends Model
{
    protected $table = 'pokemon';

    protected $fillable = [
        'nome',
        'tipo',
        'altura',
        'peso',
        'sprite',
        'id_externo',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'altura' => 'integer',
            'peso' => 'decimal:2',
            'id_externo' => 'integer',
            'ativo' => 'boolean',
        ];
    }
}
