<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mesa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DigitacionController extends Controller
{
    /**
     * Resumen general y últimas mesas registradas.
     */
    public function resumen(): JsonResponse
    {
        $total = Mesa::count();

        $registradas = Mesa::where('estado', 'REGISTRADA')
            ->count();

        $enRegistro = Mesa::where('estado', 'EN_REGISTRO')
            ->count();

        $pendientes = Mesa::where('estado', 'PENDIENTE')
            ->count();

        $avance = $total > 0
            ? round(($registradas / $total) * 100, 1)
            : 0;

        $ultimas = Mesa::where('estado', 'REGISTRADA')
            ->orderByDesc('updated_at')
            ->limit(15)
            ->get([
                'id',
                'numero_mesa',
                'provincia',
                'distrito',
                'estado',
                'updated_at',
            ]);

        $ultimaRegistrada = $ultimas->first();

        return response()->json([
            'success' => true,

            'data' => [
                'resumen' => [
                    'total' => $total,
                    'registradas' => $registradas,
                    'en_registro' => $enRegistro,
                    'pendientes' => $pendientes,
                    'avance' => $avance,
                ],

                'ultima_registrada' => $ultimaRegistrada,

                'ultimas' => $ultimas,
            ],
        ]);
    }


    private function mesasConfiguradas()
{
    return Mesa::query()
        ->whereExists(function ($query) {

            $query
                ->selectRaw('1')
                ->from('elecciones as e')
                ->where('e.activo', true)
                ->whereColumn(
                    'e.departamento',
                    'mesas.departamento'
                )
                ->where(function ($q) {

                    /*
                     * Elección distrital
                     */
                    $q->where(function ($distrital) {

                        $distrital
                            ->where(
                                'e.tipo',
                                'DISTRITAL'
                            )
                            ->whereColumn(
                                'e.provincia',
                                'mesas.provincia'
                            )
                            ->whereColumn(
                                'e.distrito',
                                'mesas.distrito'
                            );
                    });

                    /*
                     * Elección provincial
                     */
                    $q->orWhere(function ($provincial) {

                        $provincial
                            ->where(
                                'e.tipo',
                                'PROVINCIAL'
                            )
                            ->whereColumn(
                                'e.provincia',
                                'mesas.provincia'
                            )
                            ->whereNull(
                                'e.distrito'
                            );
                    });

                    /*
                     * Elección regional
                     */
                    $q->orWhere(function ($regional) {

                        $regional
                            ->where(
                                'e.tipo',
                                'REGIONAL'
                            )
                            ->whereNull(
                                'e.provincia'
                            )
                            ->whereNull(
                                'e.distrito'
                            );
                    });
                });
        });
}


public function siguiente(Request $request): JsonResponse
{
    $desde = preg_replace(
        '/\D/',
        '',
        (string) $request->query('desde', '')
    );

    if ($desde !== '') {
        $desde = str_pad(
            $desde,
            6,
            '0',
            STR_PAD_LEFT
        );
    }


    $query = $this
        ->mesasConfiguradas()
        ->whereIn(
            'estado',
            [
                'PENDIENTE',
                'EN_REGISTRO'
            ]
        );


    if ($desde !== '') {

        $mesa = (clone $query)
            ->where(
                'numero_mesa',
                '>',
                $desde
            )
            ->orderBy(
                'numero_mesa'
            )
            ->first();

    } else {

        $mesa = (clone $query)
            ->orderBy(
                'numero_mesa'
            )
            ->first();
    }


    /*
     * Si llegó al final, buscar desde
     * el principio.
     */
    if (!$mesa && $desde !== '') {

        $mesa = $query
            ->orderBy(
                'numero_mesa'
            )
            ->first();
    }


    return response()->json([
        'success' => true,

        'data' => $mesa
            ? [
                'id' =>
                    $mesa->id,

                'numero_mesa' =>
                    $mesa->numero_mesa,

                'provincia' =>
                    $mesa->provincia,

                'distrito' =>
                    $mesa->distrito,

                'estado' =>
                    $mesa->estado,
            ]
            : null,
    ]);
}


public function anterior(Request $request): JsonResponse
{
    $desde = preg_replace(
        '/\D/',
        '',
        (string) $request->query('desde', '')
    );


    if ($desde === '') {

        return response()->json([
            'success' => false,
            'message' =>
                'Debe indicar la mesa actual.',
        ], 422);
    }


    $desde = str_pad(
        $desde,
        6,
        '0',
        STR_PAD_LEFT
    );


    $mesa = $this
        ->mesasConfiguradas()

        ->where(
            'numero_mesa',
            '<',
            $desde
        )

        ->orderByDesc(
            'numero_mesa'
        )

        ->first();


    return response()->json([

        'success' => true,

        'data' => $mesa
            ? [
                'numero_mesa' =>
                    $mesa->numero_mesa,

                'provincia' =>
                    $mesa->provincia,

                'distrito' =>
                    $mesa->distrito,

                'estado' =>
                    $mesa->estado,
            ]
            : null,
    ]);
}
}