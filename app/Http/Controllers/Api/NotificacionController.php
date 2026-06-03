<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificacionResource;
use App\Models\Notificacion;
use App\Services\Notificacion\NotificacionService;
use App\Http\Requests\Notificacion\ReadNotificacionRequest;
use App\Http\Requests\Notificacion\MarkAllReadRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class NotificacionController extends Controller
{
    public function __construct(private readonly NotificacionService $service)
    {
    }

    /**
     * GET /notificaciones
     */
    public function index(Request $request): JsonResponse
    {
        $notificaciones = $this->service->listarParaUsuario($request->user());
        
        return response()->json([
            'success' => true,
            'data' => NotificacionResource::collection($notificaciones)
        ]);
    }

    /**
     * PATCH /notificaciones/{id}/leer
     */
    public function marcarLeida(string $id, ReadNotificacionRequest $request): JsonResponse
    {
        // Al no usar Route Model Binding en la ruta por usar {id}, buscamos el modelo manualmente
        $notificacion = Notificacion::findOrFail($id);

        Gate::authorize('update', $notificacion);

        $actualizada = $this->service->marcarComoLeida($notificacion);

        return response()->json([
            'success' => true,
            'message' => 'Notificación marcada como leída.',
            'data' => new NotificacionResource($actualizada)
        ]);
    }

    /**
     * POST /notificaciones/leer-todas
     */
    public function marcarTodasLeidas(MarkAllReadRequest $request): JsonResponse
    {
        $afectadas = $this->service->marcarTodasComoLeidas($request->user());

        return response()->json([
            'success' => true,
            'message' => "Se marcaron {$afectadas} notificaciones como leídas."
        ]);
    }
}