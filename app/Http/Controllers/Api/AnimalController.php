<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Animal\StoreAnimalRequest;
use App\Http\Requests\Animal\UpdateAnimalRequest;
use App\Http\Resources\AnimalResource;
use App\Models\Animal;
use App\Models\ImagenAnimal;
use App\Services\Animal\AnimalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;

class AnimalController extends Controller
{
    public function __construct(private readonly AnimalService $service)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $filtros = $request->only(['finca_id', 'estado', 'arete']);
        $animales = $this->service->listar($request->user(), $filtros, (int) $request->input('per_page', 25));
        return AnimalResource::collection($animales);
    }

    public function store(StoreAnimalRequest $request): JsonResponse
    {
        $animal = $this->service->crearParaUsuario($request->user(), $request->validated());
        return response()->json(new AnimalResource($animal->load(['raza', 'ultimoPesaje', 'imagenPrincipal'])), 201);
    }

    public function show(Request $request, Animal $animal): AnimalResource
    {
        $this->authorize('view', $animal);
        return new AnimalResource($animal->load(['raza', 'ultimoPesaje', 'finca:id,nombre', 'imagenPrincipal']));
    }

    public function subirImagen(Request $request, Animal $animal): JsonResponse
    {
        $this->authorize('update', $animal);

        $request->validate([
            'imagen' => ['required', 'image', 'max:10240'],
        ]);

        $ruta = $request->file('imagen')->store("animales/{$animal->id}", 'public');
        $url  = Storage::disk('public')->url($ruta);

        $imagen = ImagenAnimal::create([
            'animal_id' => $animal->id,
            'ruta'      => $ruta,
            'url'       => $url,
        ]);

        return response()->json(['url' => $imagen->url], 201);
    }

    public function update(UpdateAnimalRequest $request, Animal $animal): AnimalResource
    {
        $this->authorize('update', $animal);
        return new AnimalResource($this->service->actualizar($animal, $request->validated()));
    }

    public function destroy(Request $request, Animal $animal): JsonResponse
    {
        $this->authorize('delete', $animal);
        $this->service->eliminar($animal);
        return response()->json(null, 204);
    }
}
