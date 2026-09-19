<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mesa extends Model
{
    use HasFactory;

    protected $table = 'mesas';

    protected $fillable = [
        'numero_mesa',
        'odpe',
        'departamento',
        'provincia',
        'distrito',
        'pagina_fuente',
        'estado',
    ];

    protected $casts = [
        'pagina_fuente' => 'integer',
    ];

    public function resultados()
{
    return $this->hasMany(ResultadoMesa::class);
}
}