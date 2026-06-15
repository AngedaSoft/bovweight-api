<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Notificacion\MarkAllReadRequest;
use App\Http\Requests\Notificacion\ReadNotificacionRequest;
use App\Http\Resources\NotificacionResource;
use App\Models\Notificacion;
use App\Services\Notificacion\NotificacionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificacionController extends Controller
{
    public function __construct(private readonly NotificacionService $service)
    {
    }

    /**
     * GET /api/notificaciones
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        return NotificacionResource::collection(
            $this->service->listarParaUsuario($request->user())
        );
    }

    /**
     * PATCH /api/notificaciones/{notificacion}/leer
     */
    public function marcarLeida(ReadNotificacionRequest $request, Notificacion $notificacion): NotificacionResource
    {
        $this->authorize('update', $notificacion);

        return new NotificacionResource($this->service->marcarComoLeida($notificacion));
    }

    /**
     * POST /api/notificaciones/leer-todas
     */
    public function marcarTodasLeidas(MarkAllReadRequest $request): JsonResponse
    {
        $afectadas = $this->service->marcarTodasComoLeidas($request->user());

        return response()->json([
            'leidas' => $afectadas,
            'message' => "Se marcaron {$afectadas} notificaciones como leidas.",
        ]);
    }
}
