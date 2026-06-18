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

    /**
     * Visibilidad de pesajes: propietario directo o usuario con AccesoCompartido
     * vigente sobre la finca del animal.
     */
    private function consultaParaUsuario(User $usuario): Builder
    {
        if ($usuario->esAdministrador()) {
            return Pesaje::query();
        }
        return Pesaje::query()->whereHas('animal.finca', function ($q) use ($usuario) {
            $q->where('propietario_id', $usuario->id)
              ->orWhereHas('accesosVigentes', fn ($sub) => $sub->where('usuario_id', $usuario->id));
        });
    }
}
