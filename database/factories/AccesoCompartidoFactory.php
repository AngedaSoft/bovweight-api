<?php

namespace Database\Factories;

use App\Models\AccesoCompartido;
use App\Models\Finca;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AccesoCompartido>
 */
class AccesoCompartidoFactory extends Factory
{
    protected $model = AccesoCompartido::class;

    public function definition(): array
    {
        return [
            'finca_id' => Finca::factory(),
            'usuario_id' => User::factory()->veterinario(),
            'fecha_inicio' => now(),
            'fecha_fin' => null,
            'tipo_acceso' => AccesoCompartido::TIPO_LECTURA,
            'activo' => true,
        ];
    }

    public function edicion(): static
    {
        return $this->state(fn () => ['tipo_acceso' => AccesoCompartido::TIPO_EDICION]);
    }

    public function inactivo(): static
    {
        return $this->state(fn () => ['activo' => false]);
    }

    public function vencido(): static
    {
        return $this->state(fn () => ['fecha_fin' => now()->subDay()]);
    }
}
