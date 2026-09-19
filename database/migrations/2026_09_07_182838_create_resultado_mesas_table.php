<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resultados_mesas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('mesa_id')
                ->constrained('mesas')
                ->cascadeOnDelete();

            $table->foreignId('eleccion_id')
                ->constrained('elecciones')
                ->cascadeOnDelete();

            $table->unsignedInteger('votos_validos')->default(0);
            $table->unsignedInteger('votos_blancos')->default(0);
            $table->unsignedInteger('votos_nulos')->default(0);

            $table->unsignedInteger('total_votos')->default(0);

            $table->string('estado', 20)
                ->default('REGISTRADO');

            $table->foreignId('usuario_registro_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            /*
             * Una mesa solamente puede tener un resultado
             * por cada tipo de elección.
             */
            $table->unique(
                ['mesa_id', 'eleccion_id'],
                'mesa_eleccion_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resultados_mesas');
    }
};