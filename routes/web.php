<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-pdf/{id}', function($id) {
    $convocatoria = \App\Models\CorrespondenciaJunta::with(['temporada', 'cargoFirmante'])->findOrFail($id);
    
    // Cargar configuración con logo
    $config = \App\Models\Configuracion::first();
    
    return view('convocatorias.pdf', compact('convocatoria', 'config'));
});
