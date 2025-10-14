<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArbolController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'timestamp' => now(),
        'database' => DB::connection()->getPdo() ? 'Connected' : 'Disconnected'
    ]);
});

// Rutas de árboles
Route::get('/arboles', [ArbolController::class, 'index']);
Route::get('/arboles/{id}', [ArbolController::class, 'show']);
Route::post('/arboles', [ArbolController::class, 'store']);
Route::put('/arboles/{id}', [ArbolController::class, 'update']);
Route::post('/arboles/{id}', [ArbolController::class, 'update']); // Para FormData con _method=PUT
Route::delete('/arboles/{id}', [ArbolController::class, 'destroy']);