<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resultados_detalles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('resultado_mesa_id')
                ->constrained('resultados_mesas')
                ->cascadeOnDelete();

            $table->foreignId('organizacion_politica_id')
                ->constrained('organizaciones_politicas')
                ->cascadeOnDelete();

            $table->unsignedInteger('votos')->default(0);

            $table->timestamps();

            $table->unique(
                ['resultado_mesa_id', 'organizacion_politica_id'],
                'resultado_organizacion_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resultados_detalles');
    }
};