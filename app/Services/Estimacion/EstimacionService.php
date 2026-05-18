<?php

namespace App\Services\Estimacion;

use App\Models\Animal;
use App\Models\Fotografia;
use App\Models\Pesaje;
use App\Models\User;
use App\Services\Ml\MlEstimacionClient;
use App\Services\Ml\MlEstimacionException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Coordina el flujo de estimacion de peso desde la API.
 *
 * SRP: solo se ocupa de orquestar (autorizacion, llamada al ML, persistencia
 * del pesaje + fotografia). La logica de IA vive en el microservicio externo.
 * DIP: depende de `MlEstimacionClient` (interface), no de la implementacion HTTP.
 */
class EstimacionService
{
    public function __construct(private readonly MlEstimacionClient $cliente)
    {
    }

    public function estimar(User $usuario, Animal $animal, array $datos, ?UploadedFile $imagen = null): Pesaje
    {
        $this->autorizar($usuario, $animal);

        $codigoRaza = $this->resolverCodigoRaza($animal);
        $imagenPath = $imagen ? $this->guardarImagenTemporal($imagen) : null;

        $payload = array_filter([
            'raza' => $codigoRaza,
            'imagen' => $imagenPath,
            'perimetro_toracico_cm' => $datos['perimetro_toracico_cm'] ?? null,
            'largo_cuerpo_cm' => $datos['largo_cuerpo_cm'] ?? null,
        ], fn ($v) => $v !== null && $v !== '');

        try {
            $resultado = $this->cliente->estimar($payload);
        } finally {
            if ($imagenPath && file_exists($imagenPath)) {
                @unlink($imagenPath);
            }
        }

        return DB::transaction(function () use ($usuario, $animal, $resultado, $datos, $imagen) {
            $pesaje = Pesaje::create([
                'animal_id' => $animal->id,
                'usuario_id' => $usuario->id,
                'fecha' => now(),
                'peso_estimado_kg' => $resultado['peso_estimado_kg'],
                'rango_confianza_kg' => $resultado['rango_confianza_kg'] ?? null,
                'tipo' => $resultado['metodo'] === 'IA_VISUAL' ? 'ia' : 'manual',
                'modelo_ia_version' => $resultado['modelo_version'] ?? null,
                'tiempo_procesamiento_seg' => isset($resultado['tiempo_procesamiento_ms'])
                    ? max(1, (int) round($resultado['tiempo_procesamiento_ms'] / 1000))
                    : null,
                'estado_procesamiento' => $resultado['metodo'] === 'IA_VISUAL' ? 'procesada' : null,
                'formula_zootecnica' => $resultado['metodo'] === 'IA_VISUAL'
                    ? null
                    : ($resultado['formula_aplicada'] ?? 'schaeffer'),
                'es_offline' => (bool) ($datos['es_offline'] ?? false),
                'perimetro_toracico_cm' => $resultado['medida_inferida']['perimetro_toracico_cm']
                    ?? $datos['perimetro_toracico_cm']
                    ?? null,
                'largo_cuerpo_cm' => $resultado['medida_inferida']['largo_cuerpo_cm']
                    ?? $datos['largo_cuerpo_cm']
                    ?? null,
                'fecha_medicion' => $datos['fecha_medicion'] ?? now()->toDateString(),
            ]);

            if ($imagen) {
                $rutaPublica = $imagen->store('pesajes/' . $pesaje->id, 'public');
                Fotografia::create([
                    'pesaje_id' => $pesaje->id,
                    'ruta_archivo' => $rutaPublica,
                    'fecha_captura' => now(),
                    'resolucion' => $this->resolucionDeImagen($imagen),
                    'es_valida' => true,
                ]);
            }

            return $pesaje->fresh(['fotografias']);
        });
    }

    private function autorizar(User $usuario, Animal $animal): void
    {
        if ($usuario->esAdministrador()) {
            return;
        }
        if ($animal->finca?->propietario_id !== $usuario->id) {
            throw new AuthorizationException('No puede pesar animales de fincas ajenas.');
        }
    }

    private function resolverCodigoRaza(Animal $animal): string
    {
        $nombre = $animal->raza?->nombre;
        if (! $nombre) {
            throw new MlEstimacionException(
                'El animal no tiene raza asignada; asigne una raza antes de estimar.',
                'RAZA_REQUERIDA',
            );
        }
        return strtoupper(preg_replace('/\s+/', '_', $nombre));
    }

    private function guardarImagenTemporal(UploadedFile $imagen): string
    {
        $temporal = tempnam(sys_get_temp_dir(), 'bov-img-');
        copy($imagen->getRealPath(), $temporal);
        return $temporal;
    }

    private function resolucionDeImagen(UploadedFile $imagen): ?string
    {
        $info = @getimagesize($imagen->getRealPath());
        if (! $info) {
            return null;
        }
        return $info[0] . 'x' . $info[1];
    }
}
