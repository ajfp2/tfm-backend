<?php


use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\SocioController;
use App\Http\Controllers\UserController;

// Ruta libre para login
Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    // Configuración
    // Route::apiResource('configuracion', ConfigController::class);
    // Route::get('/configuracion', [ConfigController::class, 'index']);
    Route::get('/configuracion/activa', [ConfigController::class, 'activa']);
    // Route::get('/configuracion/{id}', [ConfigController::class, 'show']);
    // Route::post('/configuracion', [ConfigController::class, 'store']);
    Route::put('/configuracion/{id}', [ConfigController::class, 'update']);
    // Route::delete('/configuracion/{id}', [ConfigController::class, 'destroy']);
    // Route::post('/configuracion/{id}/activar', [ConfigController::class, 'activar']);

    // Route::apiResource('config', ConfigController::class);
    // Route::get('/config-activa', [ConfigController::class, 'activa']); // Ruta solo para configuración activa


    // Ruta CRUD menú 
    // Route::get('/menus', [MenuController::class, 'index']);
    // Route::post('/menus', [MenuController::class, 'store']);
    // Route::put('/menus/{id}', [MenuController::class, 'update']);
    // Route::delete('/menus/{id}', [MenuController::class, 'destroy']);
    
    // Obtener menú del usuario autenticado
    Route::get('/menu', [MenuController::class, 'getMenu']);
    
    // Ruta CRUD Usuarios
    Route::apiResource('users', UserController::class);
    Route::put('users/activa-user/{id}', [UserController::class, 'activar_user']);

    // Route::get('category/{id}/{page}', [ApiController::class, 'categories'])->name('api.category');



    // Ruta CRUD Socios
    Route::apiResource('socios', SocioController::class);


});



