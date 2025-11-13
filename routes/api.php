<?php


use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\SocioController;
use App\Http\Controllers\UserController;

// Ruta libre para login
Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {

    Route::get('/perfil', function (Request $request) {
        return $request->user();
    });
    
    Route::post('logout', [AuthController::class, 'logout']);
    
    // Ruta CRUD Usuarios
    Route::apiResource('users', UserController::class);
    Route::put('users/activa-user/{id}', [UserController::class, 'activar_user']);

    // Route::get('category/{id}/{page}', [ApiController::class, 'categories'])->name('api.category');

    // Ruta CRUD Config
    Route::apiResource('config', ConfigController::class);
    Route::get('/config-activa', [ConfigController::class, 'activa']); // Ruta solo para configuración activa

    // Ruta CRUD Socios
    Route::apiResource('socios', SocioController::class);


});



