<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PokemonSyncJob extends Model
{
    protected $table = 'pokemon_sync_jobs';

    protected $fillable = [
        'status',
        'total_pokemon_imported',
        'started_at',
        'finished_at',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'total_pokemon_imported' => 'integer',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'ativo' => 'boolean',
        ];
    }
}
