<?php


use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\Api\SocioController;

// Ruta libre para login
Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/perfil', function (Request $request) {
        return $request->user();
    });
    
    Route::post('/logout', [AuthController::class, 'logout']);

    // Ruta CRUD Config
    Route::apiResource('config', ConfigController::class);

    // Ruta solo para configuración activa
    Route::get('/config-activa', [ConfigController::class, 'activa']);


    Route::apiResource('socios', SocioController::class);
});



