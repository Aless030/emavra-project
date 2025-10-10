<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArbolController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Rutas públicas (sin autenticación)
Route::prefix('v1')->group(function () {
    // Obtener todos los árboles
    Route::get('/arboles', [ArbolController::class, 'index']);
    
    // Obtener un árbol específico
    Route::get('/arboles/{id}', [ArbolController::class, 'show']);
    
    // Crear nuevo árbol
    Route::post('/arboles', [ArbolController::class, 'store']);
    
    // Actualizar árbol
    Route::put('/arboles/{id}', [ArbolController::class, 'update']);
    Route::post('/arboles/{id}', [ArbolController::class, 'update']); // Para FormData
    
    // Eliminar árbol
    Route::delete('/arboles/{id}', [ArbolController::class, 'destroy']);
});

// Ruta de salud de la API
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
        'service' => 'SIF API'
    ]);
});