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
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class NotificacionController extends Controller
{
    // Mantenemos el constructor con promoción de propiedades (limpio y moderno)
    public function __construct(private readonly NotificacionService $service)
    {
    }

    /**
     * GET /notificaciones
     * 
     * CORRECCIÓN: Retornamos la colección directamente. 
     * Laravel se encarga de envolverlo en 'data' y mantiene soporte para paginación limpia.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $notificaciones = $this->service->listarParaUsuario($request->user());
        
        return NotificacionResource::collection($notificaciones);
    }

    
    public function marcarLeida(Notificacion $notificacion, ReadNotificacionRequest $request): JsonResponse
    {
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