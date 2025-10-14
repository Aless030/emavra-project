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
            $table->string('especie', 100);
            $table->string('nombre_comun', 100);
            $table->integer('edad')->unsigned();
            $table->enum('estado', ['exótico', 'nativo']);
            $table->string('fotoUrl', 500)->nullable();
            $table->decimal('altura', 8, 2)->unsigned();
            $table->decimal('diametroTronco', 8, 2)->unsigned();
            $table->decimal('diametro_copa', 8, 2)->unsigned();
            $table->string('codigo_arbol', 50)->unique();
            $table->decimal('latitud', 10, 8);
            $table->decimal('longitud', 11, 8);
            $table->string('propiedad', 100);
            $table->string('otb', 100);
            $table->string('nombre_area_verde', 150);
            $table->string('inspector', 100);
            $table->text('estado_fitosanitario')->nullable();
            $table->string('pdfUrl', 500)->nullable();
            $table->string('qrUrl', 500)->nullable();
            
            // Tus campos personalizados de fecha/hora
            $table->date('fecha_registro');
            $table->time('hora_registro');
            
            // ❌ ELIMINADO: $table->timestamps(); 
            
            // Índices para optimizar búsquedas
            $table->index('codigo_arbol');
            $table->index('fecha_registro');
            $table->index('estado');
        });

        // Agregar columna espacial NULLABLE para evitar errores en inserciones iniciales
        DB::statement('ALTER TABLE arboles ADD coordenadas POINT NULL AFTER longitud');
        DB::statement('CREATE SPATIAL INDEX coordenadas_spatial ON arboles(coordenadas)');
    }

    public function down()
    {
        Schema::dropIfExists('arboles');
    }
};