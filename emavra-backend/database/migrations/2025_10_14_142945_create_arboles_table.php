<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('arboles', function (Blueprint $table) {
            $table->id();
            $table->string('especie')->nullable();
            $table->string('nombre_comun')->nullable();
            $table->integer('edad')->nullable();
            $table->string('estado')->nullable();
            $table->string('fotoUrl')->nullable();
            $table->decimal('altura', 8, 2)->nullable();
            $table->decimal('diametroTronco', 8, 2)->nullable();
            $table->decimal('diametro_copa', 8, 2)->nullable();
            $table->string('codigo_arbol')->nullable();
            $table->decimal('latitud', 10, 8)->nullable();
            $table->decimal('longitud', 11, 8)->nullable();
            $table->string('propiedad')->nullable();
            $table->string('otb')->nullable();
            $table->string('nombre_area_verde')->nullable();
            $table->string('inspector')->nullable();
            $table->string('estado_fitosanitario')->nullable();
            $table->string('pdfUrl')->nullable();
            $table->string('qrUrl')->nullable();
            $table->date('fecha_registro')->nullable();
            $table->time('hora_registro')->nullable();
            
            // ✅ Cambio: JSON en lugar de geometry
            $table->json('coordenadas')->nullable()->comment('Coordenadas geográficas {lat, lng}');
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('arboles');
    }
};