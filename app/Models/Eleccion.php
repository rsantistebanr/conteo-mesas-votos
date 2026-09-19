<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Eleccion extends Model
{
    protected $table = 'elecciones';

   protected $fillable = [
    'nombre',
    'codigo',
    'tipo',
    'departamento',
    'provincia',
    'distrito',
    'orden',
    'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
    ];

    public function organizaciones()
    {
        return $this->belongsToMany(
            OrganizacionPolitica::class,
            'eleccion_organizacion'
        )
        ->withPivot('orden')
        ->withTimestamps();
    }

    public function resultados()
    {
        return $this->hasMany(ResultadoMesa::class);
    }
}
