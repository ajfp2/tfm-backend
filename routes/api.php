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
use App\Http\Controllers\SocioAuxiliarController;
use App\Http\Controllers\ContactoController;
use App\Http\Controllers\TareaPendienteController;
use App\Http\Controllers\HistorialAnualController;
use App\Http\Controllers\HistorialAnualBajaController;
use App\Http\Controllers\DashboardController;

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
    Route::get('/configuracion/activa', [ConfigController::class, 'activa']);
    Route::put('/configuracion/{id}', [ConfigController::class, 'update']);
    Route::put('/configuracion/{id}/visual', [ConfigController::class, 'updateVisual']);
    Route::post('/configuracion/upload-logo', [ConfigController::class, 'uploadLogo']);
    Route::delete('/configuracion/delete-logo', [ConfigController::class, 'deleteLogo']);

    // ==========================================
    // MENÚ
    // ==========================================
    Route::get('/menu', [MenuController::class, 'getMenu']);
    
    // ==========================================
    // USUARIOS
    // ==========================================
    Route::apiResource('/users', UserController::class);
    Route::put('/users/activa-user/{id}', [UserController::class, 'activar_user']);

    // ==========================================
    // PEÑA (Datos básicos)
    // ==========================================
    Route::get('/penya', [PenyaController::class, 'show']);
    Route::get('/penya/show-banco', [PenyaController::class, 'showDatosBanco']);
    Route::put('/penya/datos-generales', [PenyaController::class, 'updateDatos']);
    Route::put('/penya/datos-bancarios', [PenyaController::class, 'updateDatosBancarios']);
    
    // ==========================================
    // TEMPORADAS
    // ==========================================    
    Route::get('/temporadas/activa', [TemporadaController::class, 'getActiva']);
    Route::post('/temporadas/{id}/activar', [TemporadaController::class, 'activar']);
    Route::apiResource('/temporadas', TemporadaController::class);
    
    // ==========================================
    // JUNTA DIRECTIVA (Cargos)
    // ==========================================
    Route::apiResource('/junta-directiva', JuntaDirectivaController::class);
    Route::get('/junta-directiva/activos', [JuntaDirectivaController::class, 'activos']);
    // Route::get('/junta-directiva', [JuntaDirectivaController::class, 'index']);    
    // Route::get('/junta-directiva/{id}', [JuntaDirectivaController::class, 'show']);
    // Route::post('/junta-directiva', [JuntaDirectivaController::class, 'store']);
    // Route::put('/junta-directiva/{id}', [JuntaDirectivaController::class, 'update']);
    // Route::delete('/junta-directiva/{id}', [JuntaDirectivaController::class, 'destroy']);
    
    // ==========================================
    // HISTORIAL CARGOS DIRECTIVOS
    // ==========================================
    Route::get('/historial-cargos', [HistorialCargoDirectivoController::class, 'index']);
    Route::get('/historial-cargos/temporada/{temporadaId}', [HistorialCargoDirectivoController::class, 'porTemporada']);
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

    // ==========================================
    // AUX: PAISES; PROVINCIAS, MUNICIPIOS, PAGOS ...
    // ==========================================    

    Route::get('/nacionalidades', [SocioAuxiliarController::class, 'nacionalidades']);    
    Route::get('/provincias', [SocioAuxiliarController::class, 'provincias']);    
    Route::get('/municipios', [SocioAuxiliarController::class, 'municipios']);    
    Route::get('/formas-pago', [SocioAuxiliarController::class, 'formaspago']);

    // ==========================================
    // SOCIOS (Unificado: Personas + Alta + Baja)
    // ==========================================
    Route::get('/socios/exentos', [SocioController::class, 'getExentos']); // Obtener Socios exentos
    Route::get('/socios/deudores', [SocioController::class, 'getDeudores']); // Obtener Socios deudores
    Route::get('/socios/{id}/deudas', [SocioController::class, 'getDeudaSocio']); // Obtener Socios con deudas
    Route::post('/socios/{id}/baja', [SocioController::class, 'darBaja']); // Dar de baja a un socio    
    Route::post('/socios/{id}/reactivar', [SocioController::class, 'reactivar']); // Reactivar socio (de baja a alta)
    Route::apiResource('/socios', SocioController::class); // Ruta CRUD Socios

    // ==========================================
    // CONTACTOS (Empresas/Proveedores)
    // ==========================================
    Route::apiResource('/contactos', ContactoController::class);
    // Route::get('/contactos', [ContactoController::class, 'index']);
    // Route::get('/contactos/{id}', [ContactoController::class, 'show']);
    // Route::post('/contactos', [ContactoController::class, 'store']);
    // Route::put('/contactos/{id}', [ContactoController::class, 'update']);
    // Route::delete('/contactos/{id}', [ContactoController::class, 'destroy']);

    // ==========================================
    // TAREAS PENDIENTES
    // ==========================================
    Route::get('/tareas', [TareaPendienteController::class, 'index']);
    Route::get('/tareas/pendientes', [TareaPendienteController::class, 'pendientes']);
    Route::get('/tareas/{id}', [TareaPendienteController::class, 'show']);
    Route::post('/tareas', [TareaPendienteController::class, 'store']);
    Route::put('/tareas/{id}', [TareaPendienteController::class, 'update']);
    Route::post('/tareas/{id}/progreso', [TareaPendienteController::class, 'actualizarProgreso']);
    Route::post('/tareas/{id}/finalizar', [TareaPendienteController::class, 'finalizar']);
    Route::post('/tareas/{id}/reabrir', [TareaPendienteController::class, 'reabrir']);
    Route::delete('/tareas/{id}', [TareaPendienteController::class, 'destroy']);

    // ==========================================
    // HISTORIAL ANUAL (Cuotas de socios activos)
    // ==========================================    
    Route::get('/historial-anual', [HistorialAnualController::class, 'index']); // Listar historial        
    Route::get('/historial-anual/socio/{socioId}', [HistorialAnualController::class, 'porSocio']); // Historial por socio        
    Route::get('/historial-anual/temporada/{temporadaId}', [HistorialAnualController::class, 'porTemporada']); // Historial por temporada       
    Route::post('/historial-anual/generar', [HistorialAnualController::class, 'generarHistorial']);// Generar historial para todos los socios activos        
    Route::post('/historial-anual', [HistorialAnualController::class, 'store']);// Crear registro manual        
    Route::put('/historial-anual/{socioId}/{temporadaId}', [HistorialAnualController::class, 'update']);// Actualizar historial        
    Route::post('/historial-anual/{socioId}/{temporadaId}/pagar', [HistorialAnualController::class, 'marcarPagado']);// Marcar como pagado        
    Route::post('/historial-anual/{socioId}/{temporadaId}/no-pagar', [HistorialAnualController::class, 'marcarNoPagado']);// Marcar como no pagado        
    Route::get('/historial-anual/estadisticas/{temporadaId}', [HistorialAnualController::class, 'estadisticas']);// Estadísticas de una temporada

    // ==========================================
    // HISTORIAL ANUAL BAJAS (Cuotas de socios de baja)
    // ==========================================        
    Route::get('/historial-bajas', [HistorialAnualBajaController::class, 'index']); // Listar historial de bajas        
    Route::get('/historial-bajas/socio/{socioId}', [HistorialAnualBajaController::class, 'porSocio']);// Historial por socio de baja        
    Route::post('/historial-bajas/{socioId}/{temporadaId}/pagar', [HistorialAnualBajaController::class, 'marcarPagado']);// Marcar como pagado


    // ==========================================
    // DASHBOARD
    // ========================================== 
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/evolucion', [DashboardController::class, 'evolucion']);
    Route::get('/dashboard/tipos-socio', [DashboardController::class, 'tiposSocio']);
    Route::get('/dashboard/saldos-temporadas', [DashboardController::class, 'saldosTemporadas']);

});



