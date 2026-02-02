<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


// Rutas para los TEST de las cartas
Route::get('/test-pdf/{id}', function($id) {
    $convocatoria = \App\Models\CorrespondenciaJunta::with(['temporada', 'cargoFirmante'])->findOrFail($id);
    
    // Cargar configuración con logo
    $config = \App\Models\Configuracion::first();
    
    return view('convocatorias.pdf', compact('convocatoria', 'config'));
});

Route::get('/carta-pdf/{id}', function($id) {
    $correspondencia = \App\Models\Correspondencia::with([
        'cargoFirmante',
        'destinatarios' => function($query) {
            $query->where('papel', true);
        }
    ])->findOrFail($id);
        
    $config = \App\Models\Configuracion::first();// Cargar configuración con logo
    $penya = \App\Models\Penya::first();// Cargar datos de direccion. cp...
    return view('correspondencia.cartas-pdf', compact('correspondencia', 'config', 'penya'));
});
