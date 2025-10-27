<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\SocioController;

Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/perfil', function (Request $request) {
        return $request->user();
    });
    // Route::get('/logout', function (Request $request) {
    //     return $request->logout();
    // });
    Route::get('/logout', [AuthController::class, 'logout']);
    Route::apiResource('socios', SocioController::class);
});



