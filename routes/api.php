<?php


use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\SocioController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PenyaController;
use App\Http\Controllers\TemporadaController;
use App\Http\Controllers\JuntaDirectivaController;
use App\Http\Controllers\HistorialCargoDirectivoController;
use App\Http\Controllers\SocioTipoSocioController;



// Ruta libre para login
Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {

    // ==========================================
    // AUTH
    // ==========================================
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    // ==========================================
    // CONFIGURACIÓN
    // ==========================================
    // Route::get('/configuracion', [ConfigController::class, 'index']);
    Route::get('configuracion/activa', [ConfigController::class, 'activa']);
    // Route::get('/configuracion/{id}', [ConfigController::class, 'show']);
    // Route::post('/configuracion', [ConfigController::class, 'store']);
    Route::put('/configuracion/{id}', [ConfigController::class, 'update']);
    // Route::delete('/configuracion/{id}', [ConfigController::class, 'destroy']);
    // Route::post('/configuracion/{id}/activar', [ConfigController::class, 'activar']);

    // ==========================================
    // MENÚ
    // ==========================================
    Route::get('/menu', [MenuController::class, 'getMenu']);
    // Route::get('/menus', [MenuController::class, 'index']);
    // Route::post('/menus', [MenuController::class, 'store']);
    // Route::put('/menus/{id}', [MenuController::class, 'update']);
    // Route::delete('/menus/{id}', [MenuController::class, 'destroy']);
    
    // ==========================================
    // USUARIOS
    // ==========================================
    Route::apiResource('/users', UserController::class);
    Route::put('/users/activa-user/{id}', [UserController::class, 'activar_user']);

    // ==========================================
    // PEÑA (Datos básicos)
    // ==========================================
    Route::get('penya', [PenyaController::class, 'index']);
    Route::get('penya/{id}', [PenyaController::class, 'show']);
    Route::put('/penya/{id}', [PenyaController::class, 'update']);
    
    // ==========================================
    // TEMPORADAS
    // ==========================================
    Route::apiResource('/temporadas', TemporadaController::class);
    // Route::get('/temporadas', [TemporadaController::class, 'index']);    
    // Route::get('/temporadas/{id}', [TemporadaController::class, 'show']);
    // Route::post('/temporadas', [TemporadaController::class, 'store']);
    // Route::put('/temporadas/{id}', [TemporadaController::class, 'update']);
    // Route::delete('/temporadas/{id}', [TemporadaController::class, 'destroy']);
    Route::get('/temporadas/activa', [TemporadaController::class, 'getActiva']);
    Route::post('/temporadas/{id}/activar', [TemporadaController::class, 'activar']);
    
    // ==========================================
    // JUNTA DIRECTIVA (Cargos)
    // ==========================================
    Route::apiResource('/junta-directiva', JuntaDirectivaController::class);
    Route::get('/junta-directiva', [JuntaDirectivaController::class, 'index']);
    Route::get('/junta-directiva/activos', [JuntaDirectivaController::class, 'activos']);
    // Route::get('/junta-directiva/{id}', [JuntaDirectivaController::class, 'show']);
    // Route::post('/junta-directiva', [JuntaDirectivaController::class, 'store']);
    // Route::put('/junta-directiva/{id}', [JuntaDirectivaController::class, 'update']);
    // Route::delete('/junta-directiva/{id}', [JuntaDirectivaController::class, 'destroy']);
    
    // ==========================================
    // HISTORIAL CARGOS DIRECTIVOS
    // ==========================================
    Route::get('/historial-cargos', [HistorialCargoDirectivoController::class, 'index']);
    // Route::get('/historial-cargos/temporada/{temporadaId}', [HistorialCargoDirectivoController::class, 'porTemporada']);
    // Route::post('/historial-cargos', [HistorialCargoDirectivoController::class, 'store']);
    // Route::delete('/historial-cargos', [HistorialCargoDirectivoController::class, 'destroy']);
    
    // ==========================================
    // TIPOS DE SOCIO
    // ==========================================
    Route::apiResource('/tipos-socio', SocioTipoSocioController::class);
    // Route::get('/tipos-socio', [SocioTipoSocioController::class, 'index']);
    // Route::get('/tipos-socio/{id}', [SocioTipoSocioController::class, 'show']);
    // Route::post('/tipos-socio', [SocioTipoSocioController::class, 'store']);
    // Route::put('/tipos-socio/{id}', [SocioTipoSocioController::class, 'update']);
    // Route::delete('/tipos-socio/{id}', [SocioTipoSocioController::class, 'destroy']);

    // Ruta CRUD Socios
    Route::apiResource('socios', SocioController::class);


});



