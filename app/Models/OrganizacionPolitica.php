<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizacionPolitica extends Model
{
    protected $table = 'organizaciones_politicas';

    protected $fillable = [
        'nombre',
        'siglas',
        'logo',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

        public function elecciones()
    {
        return $this->belongsToMany(
            Eleccion::class,
            'eleccion_organizacion'
        )
        ->withPivot('orden')
        ->withTimestamps();
    }

    public function resultadosDetalles()
    {
        return $this->hasMany(ResultadoDetalle::class);
    }
}
