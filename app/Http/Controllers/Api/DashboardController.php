<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function partidos(Request $request): JsonResponse
    {
        $distrito = strtoupper(
            trim((string) $request->query('distrito', ''))
        );

        /*
        |--------------------------------------------------------------------------
        | Elecciones distritales aplicables
        |--------------------------------------------------------------------------
        */

        $eleccionesQuery = DB::table('elecciones')
            ->where('activo', true)
            ->where('tipo', 'DISTRITAL')
            ->where('departamento', 'LAMBAYEQUE')
            ->where('provincia', 'LAMBAYEQUE');

        if ($distrito !== '') {
            $eleccionesQuery->where(
                'distrito',
                $distrito
            );
        }

        $eleccionIds = (clone $eleccionesQuery)
            ->pluck('id');


        /*
        |--------------------------------------------------------------------------
        | Total de mesas
        |--------------------------------------------------------------------------
        */

        $mesasQuery = DB::table('mesas')
            ->where(
                'departamento',
                'LAMBAYEQUE'
            )
            ->where(
                'provincia',
                'LAMBAYEQUE'
            );

        if ($distrito !== '') {
            $mesasQuery->where(
                'distrito',
                $distrito
            );
        }

        $totalMesas = $mesasQuery->count();


        /*
        |--------------------------------------------------------------------------
        | Mesas registradas
        |--------------------------------------------------------------------------
        */

        $mesasRegistradas = DB::table(
            'resultados_mesas'
        )
            ->whereIn(
                'eleccion_id',
                $eleccionIds
            )
            ->distinct()
            ->count('mesa_id');


        /*
        |--------------------------------------------------------------------------
        | Totales
        |--------------------------------------------------------------------------
        */

        $totalValidos = (int) DB::table(
            'resultados_mesas'
        )
            ->whereIn(
                'eleccion_id',
                $eleccionIds
            )
            ->sum('votos_validos');


        $totalBlancos = (int) DB::table(
            'resultados_mesas'
        )
            ->whereIn(
                'eleccion_id',
                $eleccionIds
            )
            ->sum('votos_blancos');


        $totalNulos = (int) DB::table(
            'resultados_mesas'
        )
            ->whereIn(
                'eleccion_id',
                $eleccionIds
            )
            ->sum('votos_nulos');


        /*
        |--------------------------------------------------------------------------
        | Votos por organización
        |--------------------------------------------------------------------------
        |
        | Partimos desde eleccion_organizacion para que aparezcan
        | también los partidos que todavía tienen 0 votos.
        |
        */

        $partidosQuery = DB::table(
            'eleccion_organizacion as eo'
        )
            ->join(
                'elecciones as e',
                'e.id',
                '=',
                'eo.eleccion_id'
            )
            ->join(
                'organizaciones_politicas as op',
                'op.id',
                '=',
                'eo.organizacion_politica_id'
            )
            ->leftJoin(
                'resultados_mesas as rm',
                'rm.eleccion_id',
                '=',
                'e.id'
            )
            ->leftJoin(
                'resultados_detalles as rd',
                function ($join) {
                    $join
                        ->on(
                            'rd.resultado_mesa_id',
                            '=',
                            'rm.id'
                        )
                        ->on(
                            'rd.organizacion_politica_id',
                            '=',
                            'op.id'
                        );
                }
            )
            ->where(
                'e.activo',
                true
            )
            ->where(
                'op.activo',
                true
            )
            ->where(
                'e.tipo',
                'DISTRITAL'
            )
            ->where(
                'e.departamento',
                'LAMBAYEQUE'
            )
            ->where(
                'e.provincia',
                'LAMBAYEQUE'
            );

        if ($distrito !== '') {
            $partidosQuery->where(
                'e.distrito',
                $distrito
            );
        }


        $partidos = $partidosQuery
            ->select(
                'op.id',
                'op.nombre',
                'op.siglas',
                DB::raw(
                    'COALESCE(SUM(rd.votos), 0) AS votos'
                ),
                DB::raw(
                    'COUNT(DISTINCT rd.resultado_mesa_id) AS mesas_con_datos'
                )
            )
            ->groupBy(
                'op.id',
                'op.nombre',
                'op.siglas'
            )
            ->orderByDesc('votos')
            ->get()
            ->map(function ($partido) use ($totalValidos) {

                $votos = (int) $partido->votos;

                return [
                    'id' => $partido->id,

                    'nombre' =>
                        $partido->nombre,

                    'siglas' =>
                        $partido->siglas,

                    'votos' =>
                        $votos,

                    'mesas_con_datos' =>
                        (int) $partido
                            ->mesas_con_datos,

                    'porcentaje' =>
                        $totalValidos > 0
                            ? round(
                                ($votos / $totalValidos) * 100,
                                2
                            )
                            : 0,
                ];
            });


        /*
        |--------------------------------------------------------------------------
        | Distritos disponibles
        |--------------------------------------------------------------------------
        */

        $distritos = DB::table('elecciones')
            ->where('activo', true)
            ->where('tipo', 'DISTRITAL')
            ->where(
                'departamento',
                'LAMBAYEQUE'
            )
            ->where(
                'provincia',
                'LAMBAYEQUE'
            )
            ->orderBy('distrito')
            ->pluck('distrito');


        /*
        |--------------------------------------------------------------------------
        | Respuesta
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,

            'data' => [

                'filtro' => [
                    'distrito' =>
                        $distrito ?: null,
                ],

                'resumen' => [

                    'total_mesas' =>
                        $totalMesas,

                    'mesas_registradas' =>
                        $mesasRegistradas,

                    'mesas_pendientes' =>
                        max(
                            0,
                            $totalMesas -
                            $mesasRegistradas
                        ),

                    'avance' =>
                        $totalMesas > 0
                            ? round(
                                (
                                    $mesasRegistradas /
                                    $totalMesas
                                ) * 100,
                                2
                            )
                            : 0,

                    'votos_validos' =>
                        $totalValidos,

                    'votos_blancos' =>
                        $totalBlancos,

                    'votos_nulos' =>
                        $totalNulos,

                    'total_votos' =>
                        $totalValidos +
                        $totalBlancos +
                        $totalNulos,
                ],

                'partidos' =>
                    $partidos,

                'distritos' =>
                    $distritos,
            ],
        ]);
    }
}