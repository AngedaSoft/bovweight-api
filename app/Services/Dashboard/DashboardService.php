<?php

namespace App\Services\Dashboard;

use App\Models\Animal;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardService
{
    public function obtenerMetricasFinca(int $fincaId): array
    {
        // Cabezas totales (solo activos)
        $totalCabezas = Animal::where('finca_id', $fincaId)
            ->where('estado', 'activo')
            ->count();

        // Peso total acumulado e histórico de GPD mediante subconsultas optimizadas
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

        // Cálculo de GPD Promedio de la Finca (Comparación entre el primer y último pesaje de cada animal)
        $gpdData = DB::table('pesajes')
            ->join('animales', 'pesajes.animal_id', '=', 'animales.id')
            ->select(
                'pesajes.animal_id',
                DB::raw("MIN(pesajes.fecha) as primera_fecha"),
                DB::raw("MAX(pesajes.fecha) as ultima_fecha"),
                DB::raw("MIN(CASE WHEN pesajes.fue_corregido THEN pesajes.peso_corregido_kg ELSE pesajes.peso_estimado_kg END) as primer_peso"),
                DB::raw("MAX(CASE WHEN pesajes.fue_corregido THEN pesajes.peso_corregido_kg ELSE pesajes.peso_estimado_kg END) as ultimo_peso")
            )
            ->where('animales.finca_id', $fincaId)
            ->where('animales.estado', 'activo')
            ->groupBy('pesajes.animal_id')
            ->get();

        $gpdPromedioFinca = 0;
        $animalesConMultiplesPesajes = 0;

        foreach ($gpdData as $registro) {
            if ($registro->primera_fecha !== $registro->ultima_fecha) {
                $dias = Carbon::parse($registro->primera_fecha)->diffInDays(Carbon::parse($registro->ultima_fecha));
                if ($dias > 0) {
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
            ->take(5) /* -----OJO AQUI MUCHACHOS----, retorna las top 5 alertas críticas 
            para no saturar el dashboard, si se ocupan mas solo se cambia el numero*/ 
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
    'peso_total_acumulado_kg' => (float) round($pesoTotalAcumulado, 2), // <- Casteo estricto a float
    'gpd_promedio_finca_kg' => (float) $gpdFinal,                       // <- Casteo estricto a float
    'alertas' => $animalesDesactualizados
        ];
    }
}