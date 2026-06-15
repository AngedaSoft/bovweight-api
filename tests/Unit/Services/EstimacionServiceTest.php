<?php

namespace Tests\Unit\Services;

use App\Models\Animal;
use App\Models\Finca;
use App\Models\Raza;
use App\Models\User;
use App\Services\Estimacion\EstimacionService;
use App\Services\Ml\MlEstimacionClient;
use App\Services\Ml\MlEstimacionException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EstimacionServiceTest extends TestCase
{
    use RefreshDatabase;

    private function clienteFake(array $respuesta): MlEstimacionClient
    {
        return new class($respuesta) implements MlEstimacionClient {
            public function __construct(private readonly array $respuesta)
            {
            }

            public function estimar(array $payload): array
            {
                return $this->respuesta;
            }
        };
    }

    private function clienteQueExplota(MlEstimacionException $excepcion): MlEstimacionClient
    {
        return new class($excepcion) implements MlEstimacionClient {
            public function __construct(private readonly MlEstimacionException $excepcion)
            {
            }

            public function estimar(array $payload): array
            {
                throw $this->excepcion;
            }
        };
    }

    public function test_estimar_con_imagen_persiste_pesaje_tipo_ia_y_fotografia(): void
    {
        Storage::fake('public');
        $cliente = $this->clienteFake([
            'peso_estimado_kg' => 432.5,
            'rango_confianza_kg' => 18.2,
            'metodo' => 'IA_VISUAL',
            'formula_aplicada' => 'YOLO',
            'medida_inferida' => ['perimetro_toracico_cm' => 178, 'largo_cuerpo_cm' => 145],
            'confianza_deteccion' => 0.87,
            'tiempo_procesamiento_ms' => 250,
            'modelo_version' => 'yolov8n-1.0',
            'advertencias' => [],
            'generado_en' => now()->toIso8601String(),
        ]);
        $service = new EstimacionService($cliente);

        $user = User::factory()->create();
        $finca = Finca::factory()->for($user, 'propietario')->create();
        $raza = Raza::factory()->create(['nombre' => 'BRAHMAN']);
        $animal = Animal::factory()->for($finca)->for($raza)->create();
        $animal->load(['finca', 'raza']);

        $pesaje = $service->estimar(
            $user,
            $animal,
            ['animal_id' => $animal->id],
            UploadedFile::fake()->image('vaca.jpg', 640, 480),
        );

        $this->assertSame('ia', $pesaje->tipo);
        $this->assertEqualsWithDelta(432.5, (float) $pesaje->peso_estimado_kg, 0.01);
        $this->assertSame('yolov8n-1.0', $pesaje->modelo_ia_version);
        $this->assertSame('procesada', $pesaje->estado_procesamiento);
        $this->assertCount(1, $pesaje->fotografias);
    }

    public function test_estimar_con_medidas_manuales_persiste_pesaje_tipo_manual_sin_fotografia(): void
    {
        $cliente = $this->clienteFake([
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
        $service = new EstimacionService($cliente);

        $user = User::factory()->create();
        $finca = Finca::factory()->for($user, 'propietario')->create();
        $raza = Raza::factory()->create();
        $animal = Animal::factory()->for($finca)->for($raza)->create();
        $animal->load(['finca', 'raza']);

        $pesaje = $service->estimar($user, $animal, [
            'animal_id' => $animal->id,
            'perimetro_toracico_cm' => 175,
            'largo_cuerpo_cm' => 145,
        ]);

        $this->assertSame('manual', $pesaje->tipo);
        $this->assertCount(0, $pesaje->fotografias);
        $this->assertNull($pesaje->estado_procesamiento);
    }

    public function test_estimar_rechaza_animal_de_finca_ajena(): void
    {
        $cliente = $this->clienteFake(['peso_estimado_kg' => 1]);
        $service = new EstimacionService($cliente);

        $duenoOriginal = User::factory()->create();
        $fincaAjena = Finca::factory()->for($duenoOriginal, 'propietario')->create();
        $raza = Raza::factory()->create();
        $animalAjeno = Animal::factory()->for($fincaAjena)->for($raza)->create();
        $animalAjeno->load(['finca', 'raza']);
        $intruso = User::factory()->create();

        $this->expectException(AuthorizationException::class);
        $service->estimar($intruso, $animalAjeno, [
            'animal_id' => $animalAjeno->id,
            'perimetro_toracico_cm' => 170,
            'largo_cuerpo_cm' => 140,
        ]);
    }

    public function test_estimar_lanza_si_animal_no_tiene_raza(): void
    {
        $cliente = $this->clienteFake(['peso_estimado_kg' => 1]);
        $service = new EstimacionService($cliente);

        $user = User::factory()->create();
        $finca = Finca::factory()->for($user, 'propietario')->create();
        $animal = Animal::factory()->for($finca)->create(['raza_id' => null]);
        $animal->load(['finca', 'raza']);

        $this->expectException(MlEstimacionException::class);
        $service->estimar($user, $animal, [
            'animal_id' => $animal->id,
            'perimetro_toracico_cm' => 175,
            'largo_cuerpo_cm' => 145,
        ]);
    }

    public function test_estimar_propaga_excepcion_si_ml_devuelve_error(): void
    {
        $service = new EstimacionService($this->clienteQueExplota(
            new MlEstimacionException('ML caido', 'ML_UNREACHABLE')
        ));

        $user = User::factory()->create();
        $finca = Finca::factory()->for($user, 'propietario')->create();
        $raza = Raza::factory()->create();
        $animal = Animal::factory()->for($finca)->for($raza)->create();
        $animal->load(['finca', 'raza']);

        $this->expectException(MlEstimacionException::class);
        $service->estimar($user, $animal, [
            'animal_id' => $animal->id,
            'perimetro_toracico_cm' => 175,
            'largo_cuerpo_cm' => 145,
        ]);
    }
}
