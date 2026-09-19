<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Eleccion;
use App\Models\Mesa;
use App\Models\ResultadoMesa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RegistroVotosController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Elecciones que corresponden realmente a una mesa
    |--------------------------------------------------------------------------
    |
    | Permite manejar:
    |
    | DISTRITAL   -> mismo departamento/provincia/distrito
    | PROVINCIAL  -> mismo departamento/provincia
    | REGIONAL    -> mismo departamento
    |
    */
    private function eleccionesAplicables(Mesa $mesa)
    {
        return Eleccion::query()
            ->where('activo', true)
            ->where(
                'departamento',
                $mesa->departamento
            )
            ->where(function ($query) use ($mesa) {

                /*
                |--------------------------------------------------------------------------
                | Elección distrital
                |--------------------------------------------------------------------------
                */
                $query->where(function ($q) use ($mesa) {

                    $q->where(
                        'tipo',
                        'DISTRITAL'
                    )
                    ->where(
                        'provincia',
                        $mesa->provincia
                    )
                    ->where(
                        'distrito',
                        $mesa->distrito
                    );
                });

                /*
                |--------------------------------------------------------------------------
                | Elección provincial
                |--------------------------------------------------------------------------
                */
                $query->orWhere(function ($q) use ($mesa) {

                    $q->where(
                        'tipo',
                        'PROVINCIAL'
                    )
                    ->where(
                        'provincia',
                        $mesa->provincia
                    )
                    ->whereNull(
                        'distrito'
                    );
                });

                /*
                |--------------------------------------------------------------------------
                | Elección regional
                |--------------------------------------------------------------------------
                */
                $query->orWhere(function ($q) {

                    $q->where(
                        'tipo',
                        'REGIONAL'
                    )
                    ->whereNull(
                        'provincia'
                    )
                    ->whereNull(
                        'distrito'
                    );
                });
            });
    }


    /*
    |--------------------------------------------------------------------------
    | Obtener formulario para una mesa
    |--------------------------------------------------------------------------
    */
    public function formulario(
        string $numeroMesa
    ): JsonResponse {

        $numeroMesa =
            preg_replace(
                '/\D/',
                '',
                $numeroMesa
            );

        $numeroMesa =
            str_pad(
                $numeroMesa,
                6,
                '0',
                STR_PAD_LEFT
            );


        $mesa =
            Mesa::where(
                'numero_mesa',
                $numeroMesa
            )->first();


        if (!$mesa) {

            return response()->json([
                'success' => false,
                'message' =>
                    'La mesa ingresada no existe.',
            ], 404);
        }


        /*
        |--------------------------------------------------------------------------
        | Resultados registrados
        |--------------------------------------------------------------------------
        */
        $resultadosRegistrados =
            ResultadoMesa::where(
                'mesa_id',
                $mesa->id
            )
            ->with('detalles')
            ->get()
            ->keyBy('eleccion_id');


        /*
        |--------------------------------------------------------------------------
        | SOLO elecciones aplicables a esta mesa
        |--------------------------------------------------------------------------
        */
        $elecciones =
            $this
                ->eleccionesAplicables($mesa)

                ->with([
                    'organizaciones' =>
                        function ($query) {

                            $query
                                ->where(
                                    'organizaciones_politicas.activo',
                                    true
                                )
                                ->orderBy(
                                    'eleccion_organizacion.orden'
                                );
                        }
                ])

                ->orderBy('orden')

                ->get()

                ->map(
                    function ($eleccion)
                    use ($resultadosRegistrados) {

                        /*
                        |--------------------------------------------------------------------------
                        | Resultado de esta elección
                        |--------------------------------------------------------------------------
                        */
                        $resultado =
                            $resultadosRegistrados
                                ->get(
                                    $eleccion->id
                                );


                        /*
                        |--------------------------------------------------------------------------
                        | Detalles indexados por organización
                        |--------------------------------------------------------------------------
                        */
                        $detalles =
                            $resultado
                                ? $resultado
                                    ->detalles
                                    ->keyBy(
                                        'organizacion_politica_id'
                                    )
                                : collect();


                        return [

                            'id' =>
                                $eleccion->id,

                            'nombre' =>
                                $eleccion->nombre,

                            'codigo' =>
                                $eleccion->codigo,

                            'tipo' =>
                                $eleccion->tipo,

                            'departamento' =>
                                $eleccion->departamento,

                            'provincia' =>
                                $eleccion->provincia,

                            'distrito' =>
                                $eleccion->distrito,


                            /*
                            |--------------------------------------------------------------------------
                            | Saber si ya se registró
                            |--------------------------------------------------------------------------
                            */
                            'registrada' =>
                                (bool) $resultado,


                            /*
                            |--------------------------------------------------------------------------
                            | Resultado guardado
                            |--------------------------------------------------------------------------
                            */
                            'resultado' =>
                                $resultado
                                    ? [

                                        'id' =>
                                            $resultado->id,

                                        'votos_validos' =>
                                            $resultado
                                                ->votos_validos,

                                        'votos_blancos' =>
                                            $resultado
                                                ->votos_blancos,

                                        'votos_nulos' =>
                                            $resultado
                                                ->votos_nulos,

                                        'total_votos' =>
                                            $resultado
                                                ->total_votos,

                                        'estado' =>
                                            $resultado
                                                ->estado,

                                    ]
                                    : null,


                            /*
                            |--------------------------------------------------------------------------
                            | Organizaciones
                            |--------------------------------------------------------------------------
                            */
                            'organizaciones' =>
                                $eleccion
                                    ->organizaciones

                                    ->map(
                                        function ($organizacion)
                                        use ($detalles) {

                                            $detalle =
                                                $detalles
                                                    ->get(
                                                        $organizacion
                                                            ->id
                                                    );


                                            return [

                                                'id' =>
                                                    $organizacion
                                                        ->id,

                                                'nombre' =>
                                                    $organizacion
                                                        ->nombre,

                                                'siglas' =>
                                                    $organizacion
                                                        ->siglas,

                                                'logo' =>
                                                    $organizacion
                                                        ->logo,

                                                'orden' =>
                                                    $organizacion
                                                        ->pivot
                                                        ->orden,

                                                /*
                                                |--------------------------------------------------------------------------
                                                | Votos guardados
                                                |--------------------------------------------------------------------------
                                                */
                                                'votos' =>
                                                    $detalle
                                                        ? $detalle
                                                            ->votos
                                                        : null,
                                            ];
                                        }
                                    )

                                    ->values(),
                        ];
                    }
                );


        return response()->json([

            'success' => true,

            'data' => [

                'mesa' => [

                    'id' =>
                        $mesa->id,

                    'numero_mesa' =>
                        $mesa->numero_mesa,

                    'departamento' =>
                        $mesa->departamento,

                    'provincia' =>
                        $mesa->provincia,

                    'distrito' =>
                        $mesa->distrito,

                    'estado' =>
                        $mesa->estado,
                ],

                'elecciones' =>
                    $elecciones,
            ],
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Registrar votos
    |--------------------------------------------------------------------------
    */
    public function guardar(
        Request $request,
        string $numeroMesa
    ): JsonResponse {

        $numeroMesa =
            preg_replace(
                '/\D/',
                '',
                $numeroMesa
            );

        $numeroMesa =
            str_pad(
                $numeroMesa,
                6,
                '0',
                STR_PAD_LEFT
            );


        $mesa =
            Mesa::where(
                'numero_mesa',
                $numeroMesa
            )->first();


        if (!$mesa) {

            return response()->json([
                'success' => false,
                'message' =>
                    'La mesa ingresada no existe.',
            ], 404);
        }


        /*
        |--------------------------------------------------------------------------
        | Validaciones
        |--------------------------------------------------------------------------
        */
        $validator =
            Validator::make(
                $request->all(),
                [

                    'eleccion_id' =>
                        'required|integer|exists:elecciones,id',

                    'votos_blancos' =>
                        'required|integer|min:0',

                    'votos_nulos' =>
                        'required|integer|min:0',

                    'votos' =>
                        'required|array|min:1',

                    'votos.*.organizacion_id' =>
                        'required|integer|distinct',

                    'votos.*.cantidad' =>
                        'required|integer|min:0',
                ]
            );


        if ($validator->fails()) {

            return response()->json([

                'success' => false,

                'message' =>
                    'Existen datos inválidos.',

                'errors' =>
                    $validator->errors(),

            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | IMPORTANTE:
        | La elección debe corresponder a la mesa.
        |--------------------------------------------------------------------------
        */
        $eleccion =
            $this
                ->eleccionesAplicables($mesa)

                ->where(
                    'id',
                    $request->eleccion_id
                )

                ->first();


        if (!$eleccion) {

            return response()->json([

                'success' => false,

                'message' =>
                    'La elección seleccionada no corresponde a esta mesa.',

            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Organizaciones configuradas
        |--------------------------------------------------------------------------
        */
        $organizacionesEsperadas =
            $eleccion
                ->organizaciones()

                ->where(
                    'organizaciones_politicas.activo',
                    true
                )

                ->pluck(
                    'organizaciones_politicas.id'
                )

                ->map(
                    fn ($id) => (int) $id
                )

                ->sort()

                ->values()

                ->toArray();


        $organizacionesRecibidas =
            collect($request->votos)

                ->pluck(
                    'organizacion_id'
                )

                ->map(
                    fn ($id) => (int) $id
                )

                ->sort()

                ->values()

                ->toArray();


        if (
            $organizacionesEsperadas
            !==
            $organizacionesRecibidas
        ) {

            return response()->json([

                'success' => false,

                'message' =>
                    'Las organizaciones enviadas no corresponden a la elección.',

            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Evitar doble digitación
        |--------------------------------------------------------------------------
        */
        $existe =
            ResultadoMesa::where(
                'mesa_id',
                $mesa->id
            )
            ->where(
                'eleccion_id',
                $eleccion->id
            )
            ->exists();


        if ($existe) {

            return response()->json([

                'success' => false,

                'message' =>
                    'Esta elección ya fue registrada para la mesa.',

            ], 409);
        }


        /*
        |--------------------------------------------------------------------------
        | Totales
        |--------------------------------------------------------------------------
        */
        $votosValidos =
            collect(
                $request->votos
            )->sum(
                'cantidad'
            );


        $votosBlancos =
            (int)
            $request->votos_blancos;


        $votosNulos =
            (int)
            $request->votos_nulos;


        $totalVotos =
            $votosValidos +
            $votosBlancos +
            $votosNulos;


        try {

            /*
            |--------------------------------------------------------------------------
            | Guardar resultado
            |--------------------------------------------------------------------------
            */
            $resultado =
                DB::transaction(
                    function () use (
                        $mesa,
                        $eleccion,
                        $request,
                        $votosValidos,
                        $votosBlancos,
                        $votosNulos,
                        $totalVotos
                    ) {

                        $resultado =
                            ResultadoMesa::create([

                                'mesa_id' =>
                                    $mesa->id,

                                'eleccion_id' =>
                                    $eleccion->id,

                                'votos_validos' =>
                                    $votosValidos,

                                'votos_blancos' =>
                                    $votosBlancos,

                                'votos_nulos' =>
                                    $votosNulos,

                                'total_votos' =>
                                    $totalVotos,

                                'estado' =>
                                    'REGISTRADO',

                                'usuario_registro_id' =>
                                    auth()->id(),
                            ]);


                        foreach (
                            $request->votos
                            as $voto
                        ) {

                            $resultado
                                ->detalles()
                                ->create([

                                    'organizacion_politica_id' =>
                                        $voto[
                                            'organizacion_id'
                                        ],

                                    'votos' =>
                                        $voto[
                                            'cantidad'
                                        ],
                                ]);
                        }


                        return $resultado;
                    }
                );


            /*
            |--------------------------------------------------------------------------
            | Elecciones que ESTA mesa debe completar
            |--------------------------------------------------------------------------
            */
            $eleccionesAplicables =
                $this
                    ->eleccionesAplicables($mesa)

                    ->pluck('id');


            $totalElecciones =
                $eleccionesAplicables
                    ->count();


            /*
            |--------------------------------------------------------------------------
            | Cuántas de esas elecciones ya se registraron
            |--------------------------------------------------------------------------
            */
            $totalRegistradas =
                ResultadoMesa::where(
                    'mesa_id',
                    $mesa->id
                )

                ->whereIn(
                    'eleccion_id',
                    $eleccionesAplicables
                )

                ->count();


            /*
            |--------------------------------------------------------------------------
            | Estado real de la mesa
            |--------------------------------------------------------------------------
            */
            if (
                $totalElecciones > 0 &&
                $totalRegistradas >=
                    $totalElecciones
            ) {

                $mesa->update([
                    'estado' =>
                        'REGISTRADA',
                ]);

            } else {

                $mesa->update([
                    'estado' =>
                        'EN_REGISTRO',
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Garantizar estado actualizado
            |--------------------------------------------------------------------------
            */
            $mesa->refresh();


            return response()->json([

                'success' =>
                    true,

                'message' =>
                    'Resultados registrados correctamente.',

                'data' => [

                    'resultado_id' =>
                        $resultado->id,

                    'mesa' =>
                        $mesa->numero_mesa,

                    'mesa_estado' =>
                        $mesa->estado,

                    'eleccion' =>
                        $eleccion->nombre,

                    'votos_validos' =>
                        $votosValidos,

                    'votos_blancos' =>
                        $votosBlancos,

                    'votos_nulos' =>
                        $votosNulos,

                    'total_votos' =>
                        $totalVotos,

                    /*
                    |--------------------------------------------------------------------------
                    | Útil para depuración
                    |--------------------------------------------------------------------------
                    */
                    'total_elecciones_mesa' =>
                        $totalElecciones,

                    'total_registradas_mesa' =>
                        $totalRegistradas,
                ],

            ], 201);


        } catch (\Throwable $e) {

            return response()->json([

                'success' =>
                    false,

                'message' =>
                    'No se pudieron registrar los resultados.',

                'error' =>
                    config('app.debug')
                        ? $e->getMessage()
                        : null,

            ], 500);
        }
    }
}