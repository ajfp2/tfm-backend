<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Configuracion;
use App\Models\Menu;
use DB;

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

            $validated = $request->validate([
                'tipo' => 'required|string|max:100',
                'ejercicio' => 'required|string|max:100',
                'modificado' => 'sometimes|boolean'
            ]);

            // Por defecto modificado = 0
            $validated['modificado'] = true;

            DB::beginTransaction();

            // Crear configuración
            $configuracion = Configuracion::create($validated);
            
            // Actualizar menús
            $this->actualizarMenus($validated['tipo'], $validated['ejercicio']);
            
            DB::commit();


            return $this->sendResponse(
                $configuracion,
                'Configuración creada exitosamente',
                201
            );
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error al crear configuración: ' . $e->getMessage());

            return $this->sendError(
                'Error al crear configuración',
                ['error' => $e->getMessage()],
                500
            );
        }
    }


    public function show(int $id): JsonResponse
    {
        \Log::info('Datos recibidos SHOW:');
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
            $configuracion = Configuracion::findOrFail($id);

            $validated = $request->validate([
                'tipo' => 'sometimes|required|string|max:100',
                'ejercicio' => 'sometimes|required|string|max:100',
                'modificado' => 'required|boolean'
            ]);

            // Marcar como modificado al actualizar
            $validated['modificado'] = true;

            DB::beginTransaction();

            // Actualizar configuración
            $configuracion->update($validated);
            
            // Actualizo menús tipo y ejercicio
            if (isset($validated['tipo']) || isset($validated['ejercicio'])) {
                $tipo = $validated['tipo'] ?? $configuracion->tipo;
                $ejercicio = $validated['ejercicio'] ?? $configuracion->ejercicio;
                
                $this->actualizarMenus($tipo, $ejercicio);
            }

            DB::commit();

            if (!$configuracion) {
                return $this->sendError(
                    'Configuración no encontrada',
                    ['id' => 'No existe una configuración con este ID'],
                    404
                );
            }

            return $this->sendResponse(
                $configuracion->fresh(),
                'Configuración actualizada correctamente',
                200
            );
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error al actualizar configuración: ' . $e->getMessage());

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
            // \Log::info('Datos recibidos ACTIVA');
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

    /**
     * Método privado para actualizar los labels de los menús
     */
    private function actualizarMenus($tipo, $ejercicio)
    {
        // Actualizar menú con ID 2 (Tipo)
        $menuTipo = Menu::find(2);
        if ($menuTipo) {
            $menuTipo->label = $tipo;
            $menuTipo->save();
            \Log::info('Menú ID 2 actualizado a: ' . $tipo);
        } else {
            \Log::warning('No se encontró el menú con ID 2');
        }

        // Actualizar menú con ID 5 (Ejercicio)
        $menuEjercicio = Menu::find(5);
        if ($menuEjercicio) {
            $menuEjercicio->label = $ejercicio;
            $menuEjercicio->save();
            \Log::info('Menú ID 5 actualizado a: ' . $ejercicio);
        } else {
            \Log::warning('No se encontró el menú con ID 5');
        }
    }
}
