<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Arbol extends Model
{
    protected $table = 'arboles';
    
    protected $fillable = [
        'especie',
        'nombre_comun',
        'edad',
        'estado',
        'fotoUrl',
        'altura',
        'diametroTronco',
        'diametro_copa',
        'codigo_arbol',
        'latitud',
        'longitud',
        'propiedad',
        'otb',
        'nombre_area_verde',
        'inspector',
        'estado_fitosanitario',
        'pdfUrl',
        'qrUrl',
        'fecha_registro',
        'hora_registro',
        'coordenadas',
    ];

    protected $casts = [
        'coordenadas' => 'array', // ✅ Convierte JSON automáticamente
        'fecha_registro' => 'date',
        'hora_registro' => 'datetime',
    ];
}