<?php

namespace App\Services;

use App\Models\Pokemon;
use Illuminate\Pagination\LengthAwarePaginator;

class PokemonService
{
    public function list(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        return Pokemon::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nome', 'like', "%{$search}%")
                      ->orWhere('tipo', 'like', "%{$search}%");
                });
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): Pokemon
    {
        return Pokemon::create($data);
    }

    public function update(Pokemon $pokemon, array $data): Pokemon
    {
        $pokemon->update($data);

        return $pokemon;
    }

    public function deactivate(Pokemon $pokemon): Pokemon
    {
        $pokemon->update(['ativo' => false]);

        return $pokemon;
    }
}
