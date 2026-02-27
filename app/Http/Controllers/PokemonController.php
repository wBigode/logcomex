<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePokemonRequest;
use App\Http\Requests\UpdatePokemonRequest;
use App\Models\Pokemon;
use App\Services\PokemonService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PokemonController extends Controller
{
    public function __construct(
        private readonly PokemonService $pokemonService,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Pokemon/Index', [
            'pokemon' => $this->pokemonService->list($request->query('search')),
            'filters' => [
                'search' => $request->query('search', ''),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Pokemon/Create');
    }

    public function store(StorePokemonRequest $request): RedirectResponse
    {
        $this->pokemonService->create($request->validated());

        return redirect()->route('pokemon.index');
    }

    public function edit(Pokemon $pokemon): Response
    {
        return Inertia::render('Pokemon/Edit', [
            'pokemon' => $pokemon,
        ]);
    }

    public function update(UpdatePokemonRequest $request, Pokemon $pokemon): RedirectResponse
    {
        $this->pokemonService->update($pokemon, $request->validated());

        return redirect()->route('pokemon.index');
    }

    public function destroy(Pokemon $pokemon): RedirectResponse
    {
        $this->pokemonService->deactivate($pokemon);

        return redirect()->route('pokemon.index');
    }
}
