<?php

namespace App\Jobs;

use App\Models\PokemonSyncJob as PokemonSyncJobModel;
use App\Services\PokemonSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class PokemonSyncJob implements ShouldQueue
{
    use Queueable;

    public $timeout = 1800;

    public function __construct(
        private readonly PokemonSyncJobModel $syncJob,
    ) {}

    public function handle(PokemonSyncService $service): void
    {
        $service->sync($this->syncJob);
    }
}
