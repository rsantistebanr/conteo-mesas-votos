<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mesa;
use Illuminate\Http\JsonResponse;

class MesaController extends Controller
{
    public function buscar(string $numeroMesa): JsonResponse
    {
        // Solo números
        $numeroMesa = preg_replace('/\D/', '', $numeroMesa);

        // Completar con cero a la izquierda
        $numeroMesa = str_pad($numeroMesa, 6, '0', STR_PAD_LEFT);

        $mesa = Mesa::where('numero_mesa', $numeroMesa)->first();

        if (!$mesa) {
            return response()->json([
                'success' => false,
                'message' => 'La mesa ingresada no existe.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Mesa encontrada correctamente.',
            'data' => [
                'id' => $mesa->id,
                'numero_mesa' => $mesa->numero_mesa,
                'odpe' => $mesa->odpe,
                'departamento' => $mesa->departamento,
                'provincia' => $mesa->provincia,
                'distrito' => $mesa->distrito,
                'estado' => $mesa->estado,
            ],
        ]);
    }
}