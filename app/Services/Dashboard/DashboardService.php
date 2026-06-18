<?php

namespace App\Services\Dashboard;

use App\Models\Animal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardService
{
    public function obtenerPesoTotal(User $usuario, ?int $fincaId): array
    {
        $query = DB::table('animales')
            ->join('pesajes', function ($join) {
                $join->on('animales.id', '=', 'pesajes.animal_id')
                    ->whereIn('pesajes.id', function ($sub) {
                        $sub->select(DB::raw('MAX(id)'))
                            ->from('pesajes')
                            ->groupBy('animal_id');
                    });
            })
            ->where('animales.estado', 'activo');

        if ($fincaId !== null) {
            $query->where('animales.finca_id', $fincaId);
        } else {
            $fincaIds = $usuario->fincasAccesibles()->pluck('id');
            $query->whereIn('animales.finca_id', $fincaIds);
        }

        $resultado = $query->selectRaw(
            'COUNT(animales.id) as animales_con_pesaje,
             SUM(CASE WHEN pesajes.fue_corregido = 1
                      THEN pesajes.peso_corregido_kg
                      ELSE pesajes.peso_estimado_kg END) as peso_total_kg'
        )->first();

        return [
            'peso_total_kg'       => (float) round($resultado->peso_total_kg ?? 0, 2),
            'animales_con_pesaje' => (int) ($resultado->animales_con_pesaje ?? 0),
            'finca_id'            => $fincaId,
        ];
    }

    public function obtenerMetricasFinca(int $fincaId): array
    {
        // Cabezas totales (solo activos)
        $totalCabezas = Animal::where('finca_id', $fincaId)
            ->where('estado', 'activo')
            ->count();

        // Traemos el último pesaje de cada animal activo en la finca para saber su peso actual
        $pesajesActuales = DB::table('animales')
            ->join('pesajes', 'animales.id', '=', 'pesajes.animal_id')
            ->select('animales.id as animal_id', 'pesajes.fecha', 
                DB::raw('CASE WHEN pesajes.fue_corregido THEN pesajes.peso_corregido_kg ELSE pesajes.peso_estimado_kg END as peso_real')
            )
            ->where('animales.finca_id', $fincaId)
            ->where('animales.estado', 'activo')
            ->whereIn('pesajes.id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('pesajes')
                    ->groupBy('animal_id');
            })->get();

        $pesoTotalAcumulado = $pesajesActuales->sum('peso_real');

        /* 
        |--------------------------------------------------------------------------
        | CORRECCIÓN DEL BUG: Cálculo de GPD por Fechas Extremanas (Primer vs Último)
        |--------------------------------------------------------------------------
        | Obtenemos la primera y última fecha de pesaje de cada animal activo,
        | y mediante subconsultas extraemos el peso exacto de esas fechas específicas.
        */
        $gpdData = DB::table('pesajes as p')
            ->join('animales as a', 'p.animal_id', '=', 'a.id')
            ->select(
                'p.animal_id',
                DB::raw("MIN(p.fecha) as primera_fecha"),
                DB::raw("MAX(p.fecha) as ultima_fecha"),
                // Subconsulta para capturar el peso exacto en la primera fecha
                DB::raw("(SELECT CASE WHEN p1.fue_corregido THEN p1.peso_corregido_kg ELSE p1.peso_estimado_kg END 
                          FROM pesajes p1 WHERE p1.animal_id = p.animal_id ORDER BY p1.fecha ASC LIMIT 1) as primer_peso"),
                // Subconsulta para capturar el peso exacto en la última fecha
                DB::raw("(SELECT CASE WHEN p2.fue_corregido THEN p2.peso_corregido_kg ELSE p2.peso_estimado_kg END 
                          FROM pesajes p2 WHERE p2.animal_id = p.animal_id ORDER BY p2.fecha DESC LIMIT 1) as ultimo_peso")
            )
            ->where('a.finca_id', $fincaId)
            ->where('a.estado', 'activo')
            ->groupBy('p.animal_id')
            ->get();

        $gpdPromedioFinca = 0;
        $animalesConMultiplesPesajes = 0;

        foreach ($gpdData as $registro) {
            if ($registro->primera_fecha !== $registro->ultima_fecha) {
                $dias = Carbon::parse($registro->primera_fecha)->diffInDays(Carbon::parse($registro->ultima_fecha));
                if ($dias > 0) {
                    // Ahora resta los pesos cronológicos reales, no los máximos/mínimos numéricos
                    $gananciaPeso = $registro->ultimo_peso - $registro->primer_peso;
                    $gpdPromedioFinca += ($gananciaPeso / $dias);
                    $animalesConMultiplesPesajes++;
                }
            }
        }

        $gpdFinal = $animalesConMultiplesPesajes > 0 ? round($gpdPromedioFinca / $animalesConMultiplesPesajes, 2) : 0.00;

        // Alertas activas: Animales sin pesajes en los últimos 30 días
        $limiteDias = now()->subDays(30)->toDateTimeString();
        $animalesDesactualizados = Animal::where('finca_id', $fincaId)
            ->where('estado', 'activo')
            ->where(function($query) use ($limiteDias) {
                $query->whereDoesntHave('pesajes')
                    ->orWhereHas('pesajes', function($q) use ($limiteDias) {
                        $q->select('fecha')
                          ->whereIn('id', function($sub){
                              $sub->select(DB::raw('MAX(id)'))->from('pesajes')->groupBy('animal_id');
                          })
                          ->where('fecha', '<', $limiteDias);
                    });
            })
            ->select('id', 'nombre', 'arete_senasa')
            ->take(5) 
            ->get()
            ->map(function($animal) {
                return [
                    'tipo' => 'PESAJE_PENDIENTE',
                    'mensaje' => "El animal {$animal->nombre} (Arete: {$animal->arete_senasa}) requiere pesaje de control.",
                    'animal_id' => $animal->id
                ];
            });

        return [
            'finca_id' => $fincaId,
            'total_cabezas' => $totalCabezas,
            'peso_total_acumulado_kg' => (float) round($pesoTotalAcumulado, 2), 
            'gpd_promedio_finca_kg' => (float) $gpdFinal,                     
            'alertas' => $animalesDesactualizados
        ];
    }
}