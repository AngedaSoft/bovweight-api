<?php

namespace Tests\Unit\Services;

use App\Models\Notificacion;
use App\Models\User;
use App\Services\Notificacion\NotificacionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificacionServiceTest extends TestCase
{
    use RefreshDatabase;

    private NotificacionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new NotificacionService();
    }

    public function test_listar_para_usuario_solo_devuelve_sus_notificaciones_descendentes(): void
    {
        $user = User::factory()->create();
        $otro = User::factory()->create();

        // Insertamos manualmente created_at separados por horas para evitar
        // empates a la hora de ordenar por created_at desc en SQLite.
        Notificacion::factory()->for($user, 'usuario')->create([
            'mensaje' => 'mas vieja',
            'created_at' => now()->subHours(3),
        ]);
        Notificacion::factory()->for($user, 'usuario')->create([
            'mensaje' => 'mas reciente',
            'created_at' => now(),
        ]);
        Notificacion::factory()->for($otro, 'usuario')->create([
            'mensaje' => 'ajena',
            'created_at' => now()->subHour(),
        ]);

        $list = $this->service->listarParaUsuario($user);

        $this->assertCount(2, $list);
        $this->assertSame('mas reciente', $list->first()->mensaje);
    }

    public function test_marcar_como_leida_persiste_cambio(): void
    {
        $notif = Notificacion::factory()->create(['leida' => false]);

        $actualizada = $this->service->marcarComoLeida($notif);

        $this->assertTrue($actualizada->leida);
        $this->assertTrue($notif->fresh()->leida);
    }

    public function test_marcar_todas_solo_afecta_no_leidas_del_usuario(): void
    {
        $user = User::factory()->create();
        $otro = User::factory()->create();
        Notificacion::factory()->count(2)->for($user, 'usuario')->create(['leida' => false]);
        Notificacion::factory()->for($user, 'usuario')->create(['leida' => true]);
        Notificacion::factory()->for($otro, 'usuario')->create(['leida' => false]);

        $afectadas = $this->service->marcarTodasComoLeidas($user);

        $this->assertSame(2, $afectadas);
        $this->assertSame(0, Notificacion::where('usuario_id', $user->id)->where('leida', false)->count());
        $this->assertSame(1, Notificacion::where('usuario_id', $otro->id)->where('leida', false)->count());
    }
}
