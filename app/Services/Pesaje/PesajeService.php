<?php

namespace App\Services\Pesaje;

use App\Models\Animal;
use App\Models\CorreccionPeso;
use App\Models\Pesaje;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PesajeService
{
    public function crear(User $usuario, Animal $animal, array $datos): Pesaje
    {
        $this->autorizar($usuario, $animal);

        return Pesaje::create([
            'animal_id'               => $animal->id,
            'usuario_id'              => $usuario->id,
            'fecha'                   => now(),
            'peso_estimado_kg'        => $datos['peso_estimado_kg'],
            'rango_confianza_kg'      => $datos['rango_confianza_kg'] ?? null,
            'tipo'                    => $datos['tipo'] ?? 'ia',
            'modelo_ia_version'       => $datos['modelo_ia_version'] ?? null,
            'tiempo_procesamiento_seg'=> $datos['tiempo_procesamiento_seg'] ?? null,
            'estado_procesamiento'    => ($datos['tipo'] ?? 'ia') === 'ia' ? 'procesada' : null,
            'formula_zootecnica'      => $datos['formula_zootecnica'] ?? null,
            'es_offline'              => (bool) ($datos['es_offline'] ?? false),
            'perimetro_toracico_cm'   => $datos['perimetro_toracico_cm'] ?? null,
            'largo_cuerpo_cm'         => $datos['largo_cuerpo_cm'] ?? null,
            'fecha_medicion'          => $datos['fecha_medicion'] ?? now()->toDateString(),
        ]);
    }

    private function autorizar(User $usuario, Animal $animal): void
    {
        if ($usuario->esAdministrador()) return;
        if ($animal->finca?->propietario_id !== $usuario->id) {
            throw new \Illuminate\Auth\Access\AuthorizationException('No puede pesar animales de fincas ajenas.');
        }
    }

    public function listarPorUsuario(User $usuario, int $porPagina = 100): LengthAwarePaginator
    {
        return $this->consultaParaUsuario($usuario)
            ->with(['animal.raza', 'animal.finca'])
            ->orderByDesc('fecha')
            ->paginate($porPagina);
    }

    public function listarPorAnimal(User $usuario, Animal $animal, int $porPagina = 20): LengthAwarePaginator
    {
        return $this->consultaParaUsuario($usuario)
            ->where('animal_id', $animal->id)
            ->with('fotografias')
            ->orderByDesc('fecha')
            ->paginate($porPagina);
    }

    public function corregir(User $usuario, Pesaje $pesaje, float $pesoCorregidoKg, string $motivo): Pesaje
    {
        return DB::transaction(function () use ($usuario, $pesaje, $pesoCorregidoKg, $motivo) {
            CorreccionPeso::create([
                'pesaje_id' => $pesaje->id,
                'usuario_id' => $usuario->id,
                'peso_original_kg' => $pesaje->fue_corregido ? $pesaje->peso_corregido_kg : $pesaje->peso_estimado_kg,
                'peso_corregido_kg' => $pesoCorregidoKg,
                'motivo' => $motivo,
                'fecha' => now(),
            ]);

            $pesaje->update([
                'fue_corregido' => true,
                'peso_corregido_kg' => $pesoCorregidoKg,
            ]);

            return $pesaje->fresh(['fotografias']);
        });
    }

    private function consultaParaUsuario(User $usuario): Builder
    {
        if ($usuario->esAdministrador()) {
            return Pesaje::query();
        }

        if ($usuario->esVeterinario()) {
            $fincaIds = $usuario->fincasAsignadas()->pluck('fincas.id');
            return Pesaje::query()->whereHas('animal', fn ($q) => $q->whereIn('finca_id', $fincaIds));
        }

        return Pesaje::query()->whereHas('animal.finca', fn ($q) => $q->where('propietario_id', $usuario->id));
    }
}
