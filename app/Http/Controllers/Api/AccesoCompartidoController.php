<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccesoCompartido\OtorgarAccesoRequest;
use App\Http\Resources\AccesoCompartidoResource;
use App\Models\AccesoCompartido;
use App\Models\Finca;
use App\Services\AccesoCompartido\AccesoCompartidoService;
use App\Services\AccesoCompartido\NoSePuedeCompartirConAdminException;
use App\Services\AccesoCompartido\NoSePuedeCompartirConPropietarioException;
use App\Services\AccesoCompartido\UsuarioNoExisteParaCompartirException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class AccesoCompartidoController extends Controller
{
    public function __construct(private readonly AccesoCompartidoService $service)
    {
    }

    /**
     * POST /api/acceso-compartido
     * Propietario invita a un veterinario por correo a su finca.
     */
    public function store(OtorgarAccesoRequest $request): JsonResponse
    {
        $datos = $request->validated();
        $finca = Finca::findOrFail($datos['finca_id']);

        $this->authorize('create', [AccesoCompartido::class, $finca]);

        try {
            $acceso = $this->service->otorgar(
                $finca,
                $datos['correo_usuario'],
                $datos['tipo_acceso'],
                isset($datos['fecha_fin']) ? new \DateTimeImmutable($datos['fecha_fin']) : null,
            );
        } catch (UsuarioNoExisteParaCompartirException $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'codigo' => 'USUARIO_NO_EXISTE',
            ], 422);
        } catch (NoSePuedeCompartirConPropietarioException | NoSePuedeCompartirConAdminException $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'codigo' => 'COMPARTIR_INVALIDO',
            ], 422);
        }

        return response()->json(new AccesoCompartidoResource($acceso), 201);
    }

    /**
     * GET /api/fincas/{finca}/acceso-compartido
     * Lista los accesos otorgados sobre una finca (solo el propietario o admin).
     */
    public function porFinca(Request $request, Finca $finca): AnonymousResourceCollection
    {
        $this->authorize('update', $finca);

        return AccesoCompartidoResource::collection($this->service->listarParaFinca($finca));
    }

    /**
     * GET /api/acceso-compartido/mis-fincas
     * El veterinario lista las fincas a las que tiene acceso vigente.
     */
    public function misFincas(Request $request): AnonymousResourceCollection
    {
        return AccesoCompartidoResource::collection(
            $this->service->listarMisAccesos($request->user())
        );
    }

    /**
     * DELETE /api/acceso-compartido/{acceso}
     * El propietario revoca el acceso de un veterinario.
     */
    public function destroy(AccesoCompartido $acceso): Response
    {
        $this->authorize('delete', $acceso);

        $this->service->revocar($acceso);

        return response()->noContent();
    }
}
