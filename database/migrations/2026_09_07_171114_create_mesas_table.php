<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mesas', function (Blueprint $table) {
            $table->id();

            $table->char('numero_mesa', 6)->unique();

            $table->string('odpe', 100);
            $table->string('departamento', 100);
            $table->string('provincia', 100);
            $table->string('distrito', 150);

            $table->unsignedSmallInteger('pagina_fuente')->nullable();

            $table->string('estado', 20)->default('PENDIENTE');

            $table->timestamps();

            $table->index('provincia');
            $table->index('distrito');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mesas');
    }
};