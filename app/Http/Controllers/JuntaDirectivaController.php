<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JuntaDirectiva;

class JuntaDirectivaController extends BaseController
{
    /**
     * Listar todos los tipos cargos  de la peña (presidente, secretario ...)
     */
    public function index()
    {
        try{
            $cargos = JuntaDirectiva::activos()->get();

            // Añadir información de firma
            $cargos->each(function ($cargo) {
                $cargo->tiene_firma = $cargo->tieneFirma();
            });
            
            return $this->sendResponse($cargos, 'Tipos Cargos obtenidos correctamente.', 200);

        } catch(\Exception $e) {
             \Log::error('Error al obtener los tipos de cargos: ' . $e->getMessage());
            return $this->sendError(
                'Error al obtener tipos de cargos',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Listar solo cargos activos
     */
    public function activos()
    {
        try {
            $cargos = JuntaDirectiva::activos()->orderBy('id')->get();
        
            return $this->sendResponse($cargos, 'Tipos Cargos obtenidos correctamente.', 200);
        } catch(\Exception $e) {
             \Log::error('Error al obtener los tipos de cargos: ' . $e->getMessage());
            return $this->sendError(
                'Error al obtener tipos de cargos',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }
        
    }

    /**
     * Mostrar un cargo
     */
    public function show($id)
    {
        try{
            $cargo = JuntaDirectiva::findOrFail($id);
            $cargo->tiene_firma = $cargo->tieneFirma();
            return $this->sendResponse($cargo, 'Tipo Cargo obtenido correctamente.', 200);
        } catch(\Exception $e) {
             \Log::error('Error al obtener el cargo: ' . $e->getMessage());
            return $this->sendError(
                'Error al obtener el cargo',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }
        
    }

    /**
     * Crear cargo (presidente, secretario ...)
     */
    public function store(Request $request)
    {
        try{
            $validated = $request->validate([
                'cargo' => 'required|string|max:50|unique:junta_directiva,cargo',
                'borrar' => 'sometimes|boolean'
            ]);

            $cargo = JuntaDirectiva::create($validated);

            return $this->sendResponse($cargo, 'Tipo Cargo creado correctamente.', 201);
        } catch(\Exception $e) {
             \Log::error('Error al crear el cargo: ' . $e->getMessage());
            return $this->sendError(
                'Error al crear el cargo',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Actualizar cargo
     */
    public function update(Request $request, $id)
    {
        try{
            $cargo = JuntaDirectiva::findOrFail($id);
            
            $validated = $request->validate([
                'cargo' => 'sometimes|required|string|max:50|unique:junta_directiva,cargo,' . $id,
                'borrar' => 'sometimes|boolean'
            ]);

            $cargo->update($validated);

            return $this->sendResponse($cargo->fresh(), 'Tipo Cargo actualizado correctamente.', 200);

        } catch(\Exception $e) {
             \Log::error('Error al actualizar el cargo: ' . $e->getMessage());
            return $this->sendError(
                'Error al actualizar el cargo',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Eliminar cargo
     */
    public function destroy($id)
    {
        try{
            $cargo = JuntaDirectiva::findOrFail($id);
        
            // Verificar si tiene asignaciones
            if ($cargo->historialCargos()->exists()) {
                return $this->sendError(
                    'No se puede eliminar el cargo porque tiene asignaciones en el historial',
                    [],
                    409
                );
            }

            // Hay cargos que no se pueden borrar segun estatus
            if ($cargo->borrar == 0) {
                return $this->sendError(
                    'No se puede eliminar este tipo de cargo.',
                    [],
                    403
                );
            }
        
            $cargo->delete();

            return $this->sendResponse(NULL, 'Tipo Cargo eliminado correctamente.', 200);
            
        } catch(\Exception $e) {
             \Log::error('Error al eliminar el cargo: ' . $e->getMessage());
            return $this->sendError(
                'Error al eliminar el cargo',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }
        
    }

    /**
     * Verificar si un cargo tiene firma
     * GET /api/junta-directiva/{id}/tiene-firma
    */
    public function tieneFirma($id)
    {
        try {
            $cargo = JuntaDirectiva::findOrFail($id);
            
            return $this->sendResponse([
                'cargo_id' => $cargo->id,
                'nombre_cargo' => $cargo->nombre_cargo,
                'tiene_firma' => $cargo->tieneFirma(),
                'ruta_firma' => $cargo->tieneFirma() ? 'firma_cargo_' . $cargo->id . '.png' : null
            ], 'Información de firma obtenida');
        } catch (\Exception $e) {
            return $this->sendError('Cargo no encontrado', ['error' => $e->getMessage()], 404);
        }
    }
}
