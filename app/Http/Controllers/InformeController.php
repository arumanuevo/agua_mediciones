<?php

namespace App\Http\Controllers;

use App\Models\Medicion;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Chartisan\PHP\Chartisan;

class InformeController extends Controller
{
    public function consumosPorPeriodo(Request $request)
    {
        $request->validate([
            'desde' => 'required|date',
            'hasta' => 'required|date|after_or_equal:desde',
        ]);

        $consumos = Medicion::whereBetween('fecha_toma', [$request->desde, $request->hasta])
            ->with('lote')
            ->get()
            ->groupBy('lote.numero')
            ->map(function ($mediciones) {
                return [
                    'consumo_total' => $mediciones->sum(function ($m) {
                        return $m->lectura_actual - ($m->lectura_anterior ?? 0);
                    }),
                    'mediciones' => $mediciones,
                ];
            });

        return response()->json($consumos);
    }

    public function listadoUsuarios()
    {
        return response()->json(User::with('lotes.mediciones')->get());
    }

    public function listadoMediciones()
    {
        return response()->json(Medicion::with('lote', 'user')->get());
    }

    public function graficoConsumos(Request $request)
    {
        $request->validate([
            'desde' => 'required|date',
            'hasta' => 'required|date|after_or_equal:desde',
        ]);

        $mediciones = Medicion::whereBetween('fecha_toma', [$request->desde, $request->hasta])
            ->with('lote')
            ->get()
            ->groupBy(function ($m) {
                return $m->fecha_toma->format('Y-m');
            })
            ->map(function ($group) {
                return $group->sum(function ($m) {
                    return $m->lectura_actual - ($m->lectura_anterior ?? 0);
                });
            });

        return response()->json(
            new Chartisan([
                'labels' => $mediciones->keys()->toArray(),
                'dataset' => ['Consumo (m³)', $mediciones->values()->toArray()],
            ])
        );
    }
}
