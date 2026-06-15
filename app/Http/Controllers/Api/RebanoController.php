<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rebano\StoreRebanoRequest;
use App\Http\Requests\Rebano\UpdateRebanoRequest;
use App\Http\Resources\RebanoResource;
use App\Models\Finca;
use App\Models\Rebano;
use App\Services\Rebano\RebanoService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class RebanoController extends Controller
{
    public function __construct(protected RebanoService $service)
    {
    }

    /**
     * GET /api/rebanos
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Rebano::class);

        return RebanoResource::collection(
            $this->service->listarTodos($request->user())
        );
    }

    /**
     * GET /api/fincas/{fincaId}/rebanos
     */
    public function porFinca(int $fincaId): AnonymousResourceCollection
    {
        $finca = Finca::findOrFail($fincaId);
        $this->authorize('view', $finca);

        return RebanoResource::collection($this->service->listarPorFinca($fincaId));
    }

    /**
     * POST /api/rebanos
     */
    public function store(StoreRebanoRequest $request): RebanoResource
    {
        $datos = $request->validated();
        $finca = Finca::findOrFail($datos['finca_id']);

        // El policy recibe la Finca para evaluar pertenencia sin tocar el request.
        $this->authorize('create', [Rebano::class, $finca]);

        return new RebanoResource($this->service->crear($datos));
    }

    /**
     * GET /api/rebanos/{rebano}
     */
    public function show(Rebano $rebano): RebanoResource
    {
        $this->authorize('view', $rebano);

        return new RebanoResource($rebano->loadCount('animales'));
    }

    /**
     * PATCH/PUT /api/rebanos/{rebano}
     */
    public function update(UpdateRebanoRequest $request, Rebano $rebano): RebanoResource
    {
        $this->authorize('update', $rebano);

        return new RebanoResource($this->service->actualizar($rebano, $request->validated()));
    }

    /**
     * DELETE /api/rebanos/{rebano}
     */
    public function destroy(Rebano $rebano): Response
    {
        $this->authorize('delete', $rebano);

        $this->service->eliminar($rebano);

        return response()->noContent();
    }
}
