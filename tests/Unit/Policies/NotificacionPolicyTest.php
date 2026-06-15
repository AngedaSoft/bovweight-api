<?php

namespace Tests\Unit\Policies;

use App\Models\Notificacion;
use App\Models\User;
use App\Policies\NotificacionPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificacionPolicyTest extends TestCase
{
    use RefreshDatabase;

    private NotificacionPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new NotificacionPolicy();
    }

    public function test_dueno_puede_actualizar_su_propia_notificacion(): void
    {
        $user = User::factory()->create();
        $notif = Notificacion::create([
            'usuario_id' => $user->id, 'tipo' => 'A', 'mensaje' => 'm', 'leida' => false,
        ]);

        $this->assertTrue($this->policy->update($user, $notif));
    }

    public function test_otro_usuario_no_puede_actualizar_notificacion_ajena(): void
    {
        $dueno = User::factory()->create();
        $notif = Notificacion::create([
            'usuario_id' => $dueno->id, 'tipo' => 'A', 'mensaje' => 'm', 'leida' => false,
        ]);
        $otro = User::factory()->create();

        $this->assertFalse($this->policy->update($otro, $notif));
    }

    public function test_admin_concede_acceso_via_before(): void
    {
        $this->assertTrue($this->policy->before(User::factory()->administrador()->create()));
    }
}
