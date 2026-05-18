<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\Finca;
use App\Models\Raza;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AnimalTest extends TestCase
{
    use RefreshDatabase;

    public function test_propietario_crea_animal_en_su_finca(): void
    {
        $user = User::factory()->create();
        $finca = Finca::factory()->for($user, 'propietario')->create();
        $raza = Raza::factory()->create(['nombre' => 'BRAHMAN']);

        Sanctum::actingAs($user);

        $this->postJson('/api/animales', [
            'finca_id' => $finca->id,
            'raza_id' => $raza->id,
            'arete_senasa' => 'CRI-TEST-001',
            'sexo' => 'macho',
        ])->assertCreated()->assertJsonPath('arete_senasa', 'CRI-TEST-001');
    }

    public function test_no_se_permite_crear_animal_en_finca_ajena(): void
    {
        $otro = User::factory()->create();
        $fincaAjena = Finca::factory()->for($otro, 'propietario')->create();
        $raza = Raza::factory()->create();

        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/animales', [
            'finca_id' => $fincaAjena->id,
            'raza_id' => $raza->id,
            'arete_senasa' => 'CRI-FORBIDDEN',
            'sexo' => 'hembra',
        ])->assertForbidden();
    }

    public function test_arete_duplicado_se_rechaza(): void
    {
        $user = User::factory()->create();
        $finca = Finca::factory()->for($user, 'propietario')->create();
        $raza = Raza::factory()->create();
        Animal::factory()->for($finca)->for($raza)->create(['arete_senasa' => 'CRI-DUP-1']);

        Sanctum::actingAs($user);

        $this->postJson('/api/animales', [
            'finca_id' => $finca->id,
            'raza_id' => $raza->id,
            'arete_senasa' => 'CRI-DUP-1',
            'sexo' => 'macho',
        ])->assertStatus(422)->assertJsonValidationErrors('arete_senasa');
    }
}
