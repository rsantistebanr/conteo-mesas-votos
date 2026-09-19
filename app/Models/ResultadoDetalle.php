<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResultadoDetalle extends Model
{
    protected $table = 'resultados_detalles';

    protected $fillable = [
        'resultado_mesa_id',
        'organizacion_politica_id',
        'votos',
    ];

    protected $casts = [
        'votos' => 'integer',
    ];

    public function resultadoMesa()
    {
        return $this->belongsTo(ResultadoMesa::class);
    }

    public function organizacion()
    {
        return $this->belongsTo(
            OrganizacionPolitica::class,
            'organizacion_politica_id'
        );
    }
}