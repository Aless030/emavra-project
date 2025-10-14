<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
    ];

    public $timestamps = false;

    protected static function booted()
    {
        static::saved(function ($arbol) {
            if ($arbol->latitud && $arbol->longitud && $arbol->id) {
                // ✅ CAMBIADO: PostgreSQL/PostGIS syntax
                DB::statement(
                    "UPDATE arboles SET coordenadas = ST_SetSRID(ST_MakePoint(?, ?), 4326) WHERE id = ?",
                    [$arbol->longitud, $arbol->latitud, $arbol->id]
                );
            }
        });
    }
}