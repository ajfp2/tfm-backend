<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Configuracion;

class ConfigController extends BaseController
{

    public function index(): JsonResponse
    {
        try {
            $configuraciones = Configuracion::all();

            return $this->sendResponse(
                $configuraciones,
                'Configuraciones obtenidas exitosamente',
                200
            );
        } catch (\Exception $e) {
            return $this->sendError(
                'Error al obtener configuraciones',
                ['error' => $e->getMessage()],
                500
            );
        }
    }


    public function store(Request $request): JsonResponse
    {
        try {
            $configuracion = Configuracion::create($request->validated());

            return $this->sendResponse(
                $configuracion,
                'Configuración creada exitosamente',
                201
            );
        } catch (\Exception $e) {
            return $this->sendError(
                'Error al crear configuración',
                ['error' => $e->getMessage()],
                500
            );
        }
    }


    public function show(int $id): JsonResponse
    {
        try {
            $configuracion = Configuracion::find($id);

            if (!$configuracion) {
                return $this->sendError(
                    'Configuración no encontrada',
                    ['id' => 'No existe una configuración con este ID'],
                    404
                );
            }

            return $this->sendResponse(
                $configuracion,
                'Configuración obtenida exitosamente',
                200
            );
        } catch (\Exception $e) {
            return $this->sendError(
                'Error al obtener configuración',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $configuracion = Configuracion::find($id);

            if (!$configuracion) {
                return $this->sendError(
                    'Configuración no encontrada',
                    ['id' => 'No existe una configuración con este ID'],
                    404
                );
            }

            $configuracion->update($request->validated());

            return $this->sendResponse(
                $configuracion,
                'Configuración actualizada exitosamente',
                200
            );
        } catch (\Exception $e) {
            return $this->sendError(
                'Error al actualizar configuración',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $configuracion = Configuracion::find($id);

            if (!$configuracion) {
                return $this->sendError(
                    'Configuración no encontrada',
                    ['id' => 'No existe una configuración con este ID'],
                    404
                );
            }

            $configuracion->delete();

            return $this->sendResponse(
                null,
                'Configuración eliminada exitosamente',
                200
            );
        } catch (\Exception $e) {
            return $this->sendError(
                'Error al eliminar configuración',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    public function activa(): JsonResponse
    {
        try {
            $configuracion = Configuracion::first();

            if (!$configuracion) {
                return $this->sendError(
                    'No hay configuración disponible',
                    ['mensaje' => 'Debes crear una configuración primero'],
                    404
                );
            }

            return $this->sendResponse(
                $configuracion,
                'Configuración activa obtenida exitosamente',
                200
            );
        } catch (\Exception $e) {
            return $this->sendError(
                'Error al obtener configuración activa',
                ['error' => $e->getMessage()],
                500
            );
        }
    }
}
