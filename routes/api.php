<?php

use App\Http\Controllers\Api\MesaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DigitacionController;
use App\Http\Controllers\Api\RegistroVotosController;
use App\Http\Controllers\Api\DashboardController;

Route::get('/mesas/{numeroMesa}', [MesaController::class, 'buscar'])
    ->where('numeroMesa', '[0-9]{1,6}');

Route::get(
    '/mesas/{numeroMesa}/formulario',
    [RegistroVotosController::class, 'formulario']
)->where('numeroMesa', '[0-9]{1,6}');


Route::post(
    '/mesas/{numeroMesa}/resultados',
    [RegistroVotosController::class, 'guardar']
)->where('numeroMesa', '[0-9]{1,6}');

Route::get(
    '/digitacion/resumen',
    [DigitacionController::class, 'resumen']
);

Route::get(
    '/dashboard/partidos',
    [DashboardController::class, 'partidos']
);
Route::get(
    '/digitacion/siguiente',
    [DigitacionController::class, 'siguiente']
);
Route::get(
    '/digitacion/anterior',
    [DigitacionController::class, 'anterior']
);