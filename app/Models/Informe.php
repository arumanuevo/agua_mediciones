<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Informe
{
    /**
     * Obtener consumos por período para todos los lotes.
     */
    public static function consumosPorPeriodo($desde, $hasta)
    {
        $mediciones = DB::table('mediciones')
            ->whereBetween('fecha_toma', [$desde, $hasta])
            ->join('lotes', 'mediciones.lote_id', '=', 'lotes.id')
            ->select('lotes.numero', 'mediciones.*')
            ->orderBy('lotes.numero')
            ->orderBy('mediciones.fecha_toma')
            ->get();

        $consumos = [];
        foreach ($mediciones as $medicion) {
            $consumo = $medicion->lectura_actual - ($medicion->lectura_anterior ?? 0);
            if (!isset($consumos[$medicion->numero])) {
                $consumos[$medicion->numero] = [
                    'lote' => $medicion->numero,
                    'consumo_total' => 0,
                    'mediciones' => [],
                ];
            }
            $consumos[$medicion->numero]['consumo_total'] += $consumo;
            $consumos[$medicion->numero]['mediciones'][] = $medicion;
        }
        return array_values($consumos);
    }

    /**
     * Obtener listado de usuarios con sus lotes y mediciones.
     */
    public static function listadoUsuarios()
    {
        $usuarios = DB::table('users')->get();
        foreach ($usuarios as $usuario) {
            $usuario->lotes = DB::table('lotes')->where('user_id', $usuario->id)->get();
            foreach ($usuario->lotes as $lote) {
                $lote->mediciones = DB::table('mediciones')->where('lote_id', $lote->id)->get();
            }
        }
        return $usuarios;
    }

    /**
     * Obtener listado de todas las mediciones.
     */
    public static function listadoMediciones()
    {
        return DB::table('mediciones')
            ->join('lotes', 'mediciones.lote_id', '=', 'lotes.id')
            ->join('users', 'mediciones.user_id', '=', 'users.id')
            ->select('mediciones.*', 'lotes.numero as lote_numero', 'users.name as user_name')
            ->orderBy('mediciones.fecha_toma', 'desc')
            ->get();
    }

    /**
     * Obtener datos para gráfico de consumos por mes.
     */
    public static function graficoConsumos($desde, $hasta)
    {
        $mediciones = DB::table('mediciones')
            ->whereBetween('fecha_toma', [$desde, $hasta])
            ->join('lotes', 'mediciones.lote_id', '=', 'lotes.id')
            ->select(
                DB::raw('DATE_FORMAT(mediciones.fecha_toma, "%Y-%m") as mes'),
                DB::raw('SUM(mediciones.lectura_actual - IFNULL(mediciones.lectura_anterior, 0)) as consumo')
            )
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        $labels = $mediciones->pluck('mes')->toArray();
        $data = $mediciones->pluck('consumo')->toArray();

        return [
            'labels' => $labels,
            'dataset' => ['Consumo (m³)', $data],
        ];
    }
}
