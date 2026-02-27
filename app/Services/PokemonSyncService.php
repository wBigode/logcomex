<?php

namespace App\Services;

use App\Models\Pokemon;
use App\Models\PokemonSyncJob;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PokemonSyncService
{
    private const POKEAPI_BASE_URL = 'https://pokeapi.co/api/v2/pokemon';
    private const PER_PAGE = 100;
    private const REQUEST_DELAY_MS = 100;

    public function findActiveJob(): ?PokemonSyncJob
    {
        return PokemonSyncJob::whereIn('status', ['pending', 'processing'])
            ->latest()
            ->first();
    }

    public function createSyncJob(): PokemonSyncJob
    {
        return PokemonSyncJob::create([
            'status' => 'pending',
            'total_pokemon_imported' => 0,
            'ativo' => true,
        ]);
    }

    public function sync(PokemonSyncJob $syncJob): void
    {
        $syncJob->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);

        try {
            $imported = 0;
            $nextUrl = self::POKEAPI_BASE_URL . '?limit=' . self::PER_PAGE . '&offset=0';

            while ($nextUrl) {
                $listResponse = Http::get($nextUrl);

                if ($listResponse->failed()) {
                    throw new \RuntimeException('Falha ao buscar lista de Pokémon da PokeAPI');
                }

                $body = $listResponse->json();
                $results = $body['results'] ?? [];
                $nextUrl = $body['next'] ?? null;

                foreach ($results as $item) {
                    usleep(self::REQUEST_DELAY_MS * 1000);

                    $detailResponse = Http::get($item['url']);

                    if ($detailResponse->failed()) {
                        Log::warning("Falha ao buscar detalhes do Pokémon: {$item['name']}");
                        continue;
                    }

                    $data = $detailResponse->json();

                    Pokemon::updateOrCreate(
                        ['id_externo' => $data['id']],
                        [
                            'nome' => $data['name'],
                            'tipo' => $data['types'][0]['type']['name'] ?? 'unknown',
                            'altura' => $data['height'] * 10,
                            'peso' => $data['weight'] / 10,
                            'sprite' => $data['sprites']['front_default'] ?? '',
                            'ativo' => true,
                        ]
                    );

                    $imported++;

                    $syncJob->update(['total_pokemon_imported' => $imported]);
                }
            }

            $syncJob->update([
                'status' => 'completed',
                'finished_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error("Sync falhou: {$e->getMessage()}");

            $syncJob->update([
                'status' => 'failed',
                'finished_at' => now(),
            ]);

            throw $e;
        }
    }
}
