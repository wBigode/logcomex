<?php

namespace App\Http\Controllers;

use App\Jobs\PokemonSyncJob;
use App\Models\PokemonSyncJob as PokemonSyncJobModel;
use App\Services\PokemonSyncService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class PokemonSyncJobController extends Controller
{
    public function __construct(
        private readonly PokemonSyncService $syncService,
    ) {}

    public function index(): Response
    {
        $activeJob = $this->syncService->findActiveJob();

        return Inertia::render('PokemonSync/Index', [
            'activeJob' => $activeJob ? [
                'id' => $activeJob->id,
                'status' => $activeJob->status,
                'total_pokemon_imported' => $activeJob->total_pokemon_imported,
            ] : null,
        ]);
    }

    public function store(): JsonResponse
    {
        $activeJob = $this->syncService->findActiveJob();

        if ($activeJob) {
            return response()->json([
                'id' => $activeJob->id,
                'message' => 'Já existe uma sincronização em andamento.',
            ], 409);
        }

        $syncJob = $this->syncService->createSyncJob();

        PokemonSyncJob::dispatch($syncJob);

        return response()->json(['id' => $syncJob->id]);
    }

    public function show(PokemonSyncJobModel $pokemonSyncJob): JsonResponse
    {
        return response()->json([
            'status' => $pokemonSyncJob->status,
            'total_pokemon_imported' => $pokemonSyncJob->total_pokemon_imported,
            'started_at' => $pokemonSyncJob->started_at,
            'finished_at' => $pokemonSyncJob->finished_at,
        ]);
    }
}
