<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CorrespondenciaJuntaController;
use App\Http\Controllers\CorrespondenciaController;
use App\Http\Controllers\JuntaDirectivaController;

/*
|--------------------------------------------------------------------------
| API Routes - Módulo de Correspondencia
|--------------------------------------------------------------------------
|
| Rutas protegidas con middleware auth:sanctum
|
*/

Route::middleware('auth:sanctum')->group(function () {

    // ============================================
    // CONVOCATORIAS DE JUNTAS
    // ============================================
    
    // CRUD básico
    Route::apiResource('convocatorias', CorrespondenciaJuntaController::class);
    
    // Rutas especiales
    Route::prefix('convocatorias')->group(function () {
        // Obtener convocatorias con PDF generado
        Route::get('con-pdf', [CorrespondenciaJuntaController::class, 'conPdf']);
        
        // Generar PDF
        Route::post('{id}/generar-pdf', [CorrespondenciaJuntaController::class, 'generarPdf']);
        
        // Descargar PDF
        Route::get('{id}/descargar-pdf', [CorrespondenciaJuntaController::class, 'descargarPdf']);
        
        // Toggle VºBº Presidente
        Route::post('{id}/vobo', [CorrespondenciaJuntaController::class, 'toggleVoBo']);
        
        // Marcar como enviada
        Route::post('{id}/marcar-enviada', [CorrespondenciaJuntaController::class, 'marcarEnviada']);
    });

    // ============================================
    // CORRESPONDENCIA (ENVÍOS)
    // ============================================
    
    // CRUD básico
    Route::apiResource('correspondencia', CorrespondenciaController::class);
    
    // Rutas especiales
    Route::prefix('correspondencia')->group(function () {
        // Añadir destinatarios
        Route::post('{id}/destinatarios', [CorrespondenciaController::class, 'agregarDestinatarios']);
        
        // Enviar emails
        Route::post('{id}/enviar-emails', [CorrespondenciaController::class, 'enviarEmails']);
        
        // Generar PDF de cartas para papel
        Route::get('{id}/generar-cartas-pdf', [CorrespondenciaController::class, 'generarCartasPdf']);
        
        // Marcar cartas como impresas
        Route::post('{id}/marcar-impresas', [CorrespondenciaController::class, 'marcarImpresas']);
        
        // Histórico de envíos
        Route::get('historico', [CorrespondenciaController::class, 'historico']);
    });

    // ============================================
    // TEMPORADAS (si no están ya definidas)
    // ============================================
    
    // Route::get('temporadas', [TemporadaController::class, 'index']);
    // Route::get('temporadas/activa', [TemporadaController::class, 'activa']);

    // ============================================
    // JUNTA DIRECTIVA (CARGOS)
    // ============================================
    
    Route::get('junta-directiva', [JuntaDirectivaController::class, 'index']);
    Route::get('junta-directiva/{id}', [JuntaDirectivaController::class, 'show']);
    Route::get('junta-directiva/{id}/tiene-firma', [JuntaDirectivaController::class, 'tieneFirma']);

});

/*
|--------------------------------------------------------------------------
| RESUMEN DE ENDPOINTS
|--------------------------------------------------------------------------
|
| CONVOCATORIAS:
| GET    /api/convocatorias                    - Listar todas
| GET    /api/convocatorias/{id}               - Ver una
| POST   /api/convocatorias                    - Crear
| PUT    /api/convocatorias/{id}               - Actualizar
| DELETE /api/convocatorias/{id}               - Eliminar
| GET    /api/convocatorias/con-pdf            - Listar con PDF generado
| POST   /api/convocatorias/{id}/generar-pdf   - Generar PDF
| GET    /api/convocatorias/{id}/descargar-pdf - Descargar PDF
| POST   /api/convocatorias/{id}/vobo          - Toggle VºBº
| POST   /api/convocatorias/{id}/marcar-enviada - Marcar enviada
|
| CORRESPONDENCIA:
| GET    /api/correspondencia                  - Listar todas
| GET    /api/correspondencia/{id}             - Ver una con destinatarios
| POST   /api/correspondencia                  - Crear
| PUT    /api/correspondencia/{id}             - Actualizar
| DELETE /api/correspondencia/{id}             - Eliminar
| POST   /api/correspondencia/{id}/destinatarios - Añadir destinatarios
| POST   /api/correspondencia/{id}/enviar-emails - Enviar emails
| GET    /api/correspondencia/{id}/generar-cartas-pdf - PDF cartas papel
| POST   /api/correspondencia/{id}/marcar-impresas - Marcar impresas
| GET    /api/correspondencia/historico        - Histórico de envíos
|
*/