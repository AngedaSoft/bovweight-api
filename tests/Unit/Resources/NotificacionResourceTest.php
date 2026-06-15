<?php

namespace Tests\Unit\Resources;

use App\Http\Resources\NotificacionResource;
use App\Models\Notificacion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Tests\TestCase;

class NotificacionResourceTest extends TestCase
{
    public function test_serializa_los_campos_esperados(): void
    {
        $notif = new Notificacion([
            'tipo' => 'recordatorio_pesaje',
            'mensaje' => 'Hora de pesar a CRI-001',
            'payload' => ['arete' => 'CRI-001'],
            'fecha_envio' => Carbon::parse('2026-06-01 10:00:00'),
            'leida' => false,
        ]);
        $notif->id = 99;
        $notif->created_at = Carbon::parse('2026-06-01 09:00:00');

        $payload = (new NotificacionResource($notif))->toArray(new Request());

        $this->assertSame(99, $payload['id']);
        $this->assertSame('recordatorio_pesaje', $payload['tipo']);
        $this->assertSame('Hora de pesar a CRI-001', $payload['mensaje']);
        $this->assertSame(['arete' => 'CRI-001'], $payload['payload']);
        $this->assertFalse($payload['leida']);
        $this->assertNotNull($payload['fecha_envio']);
        $this->assertNotNull($payload['fecha_creacion']);
    }

    public function test_fecha_envio_null_se_serializa_como_null(): void
    {
        $notif = new Notificacion([
            'tipo' => 'X', 'mensaje' => 'X', 'leida' => false, 'fecha_envio' => null,
        ]);
        $notif->id = 1;
        $notif->created_at = Carbon::now();

        $payload = (new NotificacionResource($notif))->toArray(new Request());

        $this->assertNull($payload['fecha_envio']);
    }
}
