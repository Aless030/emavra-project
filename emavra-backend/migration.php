<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('arboles', function (Blueprint $table) {
            $table->id();
            $table->string('especie');
            $table->string('nombre_comun');
            $table->integer('edad');
            $table->enum('estado', ['exótico', 'nativo']);
            $table->string('fotoUrl');
            $table->decimal('altura', 8, 2);
            $table->decimal('diametroTronco', 8, 2);
            $table->decimal('diametro_copa', 8, 2);
            $table->string('codigo_arbol')->unique();
            $table->decimal('latitud', 10, 8);
            $table->decimal('longitud', 11, 8);
            $table->string('propiedad');
            $table->string('otb');
            $table->string('nombre_area_verde');
            $table->string('inspector');
            $table->text('estado_fitosanitario')->nullable();
            $table->string('pdfUrl')->nullable();
            $table->string('qrUrl')->nullable();
            $table->date('fecha_registro');
            $table->time('hora_registro');
            $table->timestamps();
        });

        // Agregar columna espacial para coordenadas
        DB::statement('ALTER TABLE arboles ADD coordenadas POINT NOT NULL');
        DB::statement('CREATE SPATIAL INDEX coordenadas_spatial ON arboles(coordenadas)');
    }

    public function down()
    {
        Schema::dropIfExists('arboles');
    }
};