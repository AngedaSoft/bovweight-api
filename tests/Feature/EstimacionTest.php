<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\Finca;
use App\Models\Raza;
use App\Models\User;
use App\Services\Ml\MlEstimacionClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EstimacionTest extends TestCase
{
    use RefreshDatabase;

    private function bindFakeClient(array $respuesta): void
    {
        $this->app->instance(MlEstimacionClient::class, new class($respuesta) implements MlEstimacionClient {
            public function __construct(private readonly array $respuesta)
            {
            }

            public function estimar(array $payload): array
            {
                return $this->respuesta;
            }
        });
    }

    public function test_estimacion_con_imagen_persiste_pesaje_tipo_ia(): void
    {
        $this->bindFakeClient([
            'peso_estimado_kg' => 432.5,
            'rango_confianza_kg' => 18.2,
            'metodo' => 'IA_VISUAL',
            'formula_aplicada' => 'YOLOv8 bbox -> Schaeffer ajustado',
            'medida_inferida' => ['perimetro_toracico_cm' => 178, 'largo_cuerpo_cm' => 145],
            'confianza_deteccion' => 0.87,
            'tiempo_procesamiento_ms' => 250,
            'modelo_version' => 'yolov8n-1.0',
            'advertencias' => [],
            'generado_en' => now()->toIso8601String(),
        ]);

        $user = User::factory()->create();
        $finca = Finca::factory()->for($user, 'propietario')->create();
        $raza = Raza::factory()->create(['nombre' => 'BRAHMAN']);
        $animal = Animal::factory()->for($finca)->for($raza)->create();

        Sanctum::actingAs($user);

        $imagen = UploadedFile::fake()->image('vaca.jpg', 640, 480);

        $this->postJson('/api/estimaciones', [
            'animal_id' => $animal->id,
            'imagen' => $imagen,
        ])->assertOk()
            ->assertJsonPath('data.peso_estimado_kg', 432.5)
            ->assertJsonPath('data.tipo', 'ia');

        $this->assertDatabaseHas('pesajes', [
            'animal_id' => $animal->id,
            'tipo' => 'ia',
            'modelo_ia_version' => 'yolov8n-1.0',
        ]);
        $this->assertDatabaseHas('fotografias', [
            'es_valida' => 1,
        ]);
    }

    public function test_estimacion_con_medidas_manuales_persiste_pesaje_tipo_manual(): void
    {
        $this->bindFakeClient([
            'peso_estimado_kg' => 410.0,
            'rango_confianza_kg' => 20.5,
            'metodo' => 'FORMULA_MANUAL',
            'formula_aplicada' => 'Schaeffer K=10800',
            'medida_inferida' => ['perimetro_toracico_cm' => 175, 'largo_cuerpo_cm' => 145],
            'confianza_deteccion' => null,
            'tiempo_procesamiento_ms' => 1,
            'modelo_version' => 'schaeffer-1.0',
            'advertencias' => [],
            'generado_en' => now()->toIso8601String(),
        ]);

        $user = User::factory()->create();
        $finca = Finca::factory()->for($user, 'propietario')->create();
        $raza = Raza::factory()->create(['nombre' => 'BRAHMAN']);
        $animal = Animal::factory()->for($finca)->for($raza)->create();

        Sanctum::actingAs($user);

        $this->postJson('/api/estimaciones', [
            'animal_id' => $animal->id,
            'perimetro_toracico_cm' => 175,
            'largo_cuerpo_cm' => 145,
        ])->assertOk()->assertJsonPath('data.tipo', 'manual');
    }

    public function test_estimacion_rechaza_animal_de_finca_ajena(): void
    {
        $this->bindFakeClient(['peso_estimado_kg' => 1]);

        $otro = User::factory()->create();
        $fincaAjena = Finca::factory()->for($otro, 'propietario')->create();
        $raza = Raza::factory()->create();
        $animalAjeno = Animal::factory()->for($fincaAjena)->for($raza)->create();

        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/estimaciones', [
            'animal_id' => $animalAjeno->id,
            'perimetro_toracico_cm' => 170,
            'largo_cuerpo_cm' => 140,
        ])->assertForbidden();
    }

    public function test_estimacion_requiere_imagen_o_medidas(): void
    {
        $this->bindFakeClient(['peso_estimado_kg' => 1]);
        $user = User::factory()->create();
        $finca = Finca::factory()->for($user, 'propietario')->create();
        $raza = Raza::factory()->create();
        $animal = Animal::factory()->for($finca)->for($raza)->create();

        Sanctum::actingAs($user);

        $this->postJson('/api/estimaciones', [
            'animal_id' => $animal->id,
        ])->assertStatus(422)->assertJsonValidationErrors('imagen');
    }
}
