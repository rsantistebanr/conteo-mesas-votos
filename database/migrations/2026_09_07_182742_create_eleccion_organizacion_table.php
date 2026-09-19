<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eleccion_organizacion', function (Blueprint $table) {
            $table->id();

            $table->foreignId('eleccion_id')
                ->constrained('elecciones')
                ->cascadeOnDelete();

            $table->foreignId('organizacion_politica_id')
                ->constrained('organizaciones_politicas')
                ->cascadeOnDelete();

            $table->unsignedSmallInteger('orden')->default(1);

            $table->timestamps();

            $table->unique(
                ['eleccion_id', 'organizacion_politica_id'],
                'eleccion_organizacion_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eleccion_organizacion');
    }
};