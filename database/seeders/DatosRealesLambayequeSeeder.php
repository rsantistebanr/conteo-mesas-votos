<?php

namespace Database\Seeders;

use App\Models\Eleccion;
use App\Models\OrganizacionPolitica;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatosRealesLambayequeSeeder extends Seeder
{
    public function run(): void
    {
        $distritos = [

            'LAMBAYEQUE' => [
                'nombre' => 'Lambayeque',
                'organizaciones' => [
                    'Avanza País - Partido de Integración Social',
                    'Fuerza Popular',
                    'Acción Popular',
                    'Alianza para el Progreso',
                    'Partido Político Perú Primero',
                    'Partido Aprista Peruano',
                    'Juntos por el Perú',
                    'Renovación Popular',
                    'Partido Morado',
                    'Partido Democrático Somos Perú',
                    'Ahora Nación - AN',
                ],
            ],

            'CHOCHOPE' => [
                'nombre' => 'Chóchope',
                'organizaciones' => [
                    'Partido Democrático Somos Perú',
                    'Fuerza Popular',
                    'Alianza para el Progreso',
                ],
            ],

            'ILLIMO' => [
                'nombre' => 'Íllimo',
                'organizaciones' => [
                    'Renovación Popular',
                    'Partido Democrático Somos Perú',
                    'Fuerza Popular',
                    'Partido Popular Cristiano - PPC',
                    'Juntos por el Perú',
                    'Podemos Perú',
                    'Partido Aprista Peruano',
                ],
            ],

            'JAYANCA' => [
                'nombre' => 'Jayanca',
                'organizaciones' => [
                    'Partido Político PRIN',
                    'Partido Democrático Somos Perú',
                    'Partido Aprista Peruano',
                    'Fuerza Popular',
                    'Alianza para el Progreso',
                    'Juntos por el Perú',
                ],
            ],

            'MOCHUMI' => [
                'nombre' => 'Mochumí',
                'organizaciones' => [
                    'Avanza País - Partido de Integración Social',
                    'Partido Político Perú Primero',
                    'Partido Democrático Somos Perú',
                    'Renovación Popular',
                    'Juntos por el Perú',
                    'Fuerza Popular',
                    'Alianza para el Progreso',
                    'Partido Aprista Peruano',
                    'Fe en el Perú',
                ],
            ],

            'MORROPE' => [
                'nombre' => 'Mórrope',
                'organizaciones' => [
                    'Ahora Nación - AN',
                    'Partido Democrático Somos Perú',
                    'Partido Aprista Peruano',
                    'Alianza para el Progreso',
                    'Fuerza Popular',
                    'Juntos por el Perú',
                    'Renovación Popular',
                    'Acción Popular',
                    'Partido Político Perú Primero',
                    'Avanza País - Partido de Integración Social',
                ],
            ],

            'MOTUPE' => [
                'nombre' => 'Motupe',
                'organizaciones' => [
                    'Partido Democrático Somos Perú',
                    'Partido Aprista Peruano',
                    'Partido Político Perú Primero',
                    'Avanza País - Partido de Integración Social',
                    'Juntos por el Perú',
                    'Alianza para el Progreso',
                    'Fuerza Popular',
                    'Fe en el Perú',
                ],
            ],

            'OLMOS' => [
                'nombre' => 'Olmos',
                'organizaciones' => [
                    'Acción Popular',
                    'Fe en el Perú',
                    'Partido Democrático Somos Perú',
                    'Alianza para el Progreso',
                    'Renovación Popular',
                    'Fuerza Popular',
                    'Juntos por el Perú',
                    'Partido Aprista Peruano',
                    'Partido Morado',
                    'Avanza País - Partido de Integración Social',
                ],
            ],

            'PACORA' => [
                'nombre' => 'Pacora',
                'organizaciones' => [
                    'Fe en el Perú',
                    'Alianza para el Progreso',
                    'Partido Democrático Somos Perú',
                    'Acción Popular',
                    'Partido Aprista Peruano',
                    'Renovación Popular',
                    'Fuerza Popular',
                    'Avanza País - Partido de Integración Social',
                    'Juntos por el Perú',
                    'Partido País Para Todos',
                ],
            ],

            'SALAS' => [
                'nombre' => 'Salas',
                'organizaciones' => [
                    'Fuerza Popular',
                    'Visión Perú',
                    'Acción Popular',
                ],
            ],

            'SAN JOSE' => [
                'nombre' => 'San José',
                'organizaciones' => [
                    'Alianza para el Progreso',
                    'Visión Perú',
                    'Fuerza Popular',
                ],
            ],

            'TUCUME' => [
                'nombre' => 'Túcume',
                'organizaciones' => [
                    'Alianza para el Progreso',
                    'Fuerza Popular',
                    'Partido Aprista Peruano',
                    'Juntos por el Perú',
                    'Renovación Popular',
                    'Partido Democrático Somos Perú',
                    'Partido Político Perú Primero',
                    'Avanza País - Partido de Integración Social',
                    'Podemos Perú',
                    'Partido Popular Cristiano - PPC',
                ],
            ],
        ];


        DB::transaction(function () use ($distritos) {

            foreach ($distritos as $distritoBD => $datos) {

                $codigo =
                    'DISTRITAL_LAMBAYEQUE_' .
                    str_replace(' ', '_', $distritoBD);


                $eleccion = Eleccion::updateOrCreate(
                    [
                        'codigo' => $codigo,
                    ],
                    [
                        'nombre' =>
                            'Elección Municipal Distrital - ' .
                            $datos['nombre'],

                        'tipo' => 'DISTRITAL',

                        'departamento' =>
                            'LAMBAYEQUE',

                        'provincia' =>
                            'LAMBAYEQUE',

                        'distrito' =>
                            $distritoBD,

                        'orden' => 1,

                        'activo' => true,
                    ]
                );


                $relaciones = [];


                foreach (
                    $datos['organizaciones']
                    as $indice => $nombreOrganizacion
                ) {

                    $organizacion =
                        OrganizacionPolitica::firstOrCreate(
                            [
                                'nombre' =>
                                    $nombreOrganizacion,
                            ],
                            [
                                'activo' => true,
                            ]
                        );


                    $relaciones[
                        $organizacion->id
                    ] = [
                        'orden' =>
                            $indice + 1,
                    ];
                }


                $eleccion
                    ->organizaciones()
                    ->sync($relaciones);
            }
        });


        $this->command?->info(
            'Data real de la provincia de Lambayeque cargada correctamente.'
        );
    }
}