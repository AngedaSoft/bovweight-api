<?php

namespace Database\Factories;

use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notificacion>
 */
class NotificacionFactory extends Factory
{
    protected $model = Notificacion::class;

    public function definition(): array
    {
        return [
            'usuario_id' => User::factory(),
            'tipo' => 'recordatorio_pesaje',
            'mensaje' => fake()->sentence(),
            'payload' => null,
            'leida' => false,
        ];
    }

    public function leida(): static
    {
        return $this->state(fn () => ['leida' => true]);
    }
}
