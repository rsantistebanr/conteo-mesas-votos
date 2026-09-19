<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('elecciones', function (Blueprint $table) {

            $table->string('tipo', 20)
                ->after('codigo');

            $table->string('departamento', 100)
                ->nullable()
                ->after('tipo');

            $table->string('provincia', 100)
                ->nullable()
                ->after('departamento');

            $table->string('distrito', 150)
                ->nullable()
                ->after('provincia');

            $table->index([
                'tipo',
                'departamento',
                'provincia',
                'distrito'
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('elecciones', function (Blueprint $table) {

            $table->dropIndex([
                'tipo',
                'departamento',
                'provincia',
                'distrito'
            ]);

            $table->dropColumn([
                'tipo',
                'departamento',
                'provincia',
                'distrito'
            ]);
        });
    }
};