<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResultadoMesa extends Model
{
    protected $table = 'resultados_mesas';

    protected $fillable = [
        'mesa_id',
        'eleccion_id',
        'votos_validos',
        'votos_blancos',
        'votos_nulos',
        'total_votos',
        'estado',
        'usuario_registro_id',
    ];

    protected $casts = [
        'votos_validos' => 'integer',
        'votos_blancos' => 'integer',
        'votos_nulos' => 'integer',
        'total_votos' => 'integer',
    ];

    public function mesa()
    {
        return $this->belongsTo(Mesa::class);
    }

    public function eleccion()
    {
        return $this->belongsTo(Eleccion::class);
    }

    public function detalles()
    {
        return $this->hasMany(ResultadoDetalle::class);
    }
}