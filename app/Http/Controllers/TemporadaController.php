<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Temporada;

class TemporadaController extends BaseController
{
    /**
     * Lista de las temporadas
     */
    public function index()
    {
        try{
            $temporadas = Temporada::orderBy('id', 'desc')->get();

            return $this->sendResponse($temporadas, 'Listado de Temporadas obetindo correctamente.', 200);
        
        } catch(\Exception $e) {
             \Log::error('Error al obtener listado de Temporadas: ' . $e->getMessage());
            return $this->sendError(
                'Error al obtener listado Temporadas peña',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Obtenemos temporada activa
     */
    public function getActiva()
    {
        try{
            $temporada = Temporada::where('activa', true)->first();
        
            if (!$temporada) {
                return $this->sendError(
                    'No hay temporada activa',
                    [],
                    404
                );
            }

            return $this->sendResponse($temporada, 'Temporada activa obtenida correctamente.', 200);

        } catch(\Exception $e) {
             \Log::error('Error al obtener la temporada activa: ' . $e->getMessage());
            return $this->sendError(
                'Error al obtener temporada activa',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }
        
    }

    /**
     * Mostrar datos de una temporada
     */
    public function show($id)
    {
        try {
            $temporada = Temporada::findOrFail($id);
            
            return $this->sendResponse($temporada, 'Temporada activa obtenida correctamente.', 200);

        } catch(\Exception $e) {
             \Log::error('Error al mostrar la temporada ' . $e->getMessage());
            return $this->sendError(
                'Error al obtener temporada',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Crear una nueva temporada
     */
    public function store(Request $request)
    {
        try{
        
            $validated = $request->validate([
                'temporada' => 'required|string|max:100',
                'abrev' => 'required|string|max:50',
                'fechaIni' => 'required|date',
                'fechaFin' => 'nullable|date',
                'saldoIni' => 'required|string|max:10',
                'saldoFin' => 'required|string|max:10',
                'activa' => 'sometimes|boolean',
                'cuotaPasada' => 'sometimes|boolean'
            ]);

            // Si se marca como activa, desactivar las demás
            if (isset($validated['activa']) && $validated['activa']) {
                Temporada::query()->update(['activa' => false]);
            }

            $temporada = Temporada::create($validated);

            return $this->sendResponse($temporada, 'Temporada creada correctamente.', 201);
        } catch(\Exception $e) {
             \Log::error('Error al crear la temporada ' . $e->getMessage());
            return $this->sendError(
                'Error al crear temporada',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Actualizar temporada
     */
    public function update(Request $request, $id)
    {
        try{ 
            $temporada = Temporada::findOrFail($id);
            
            $validated = $request->validate([
                'temporada' => 'sometimes|required|string|max:100',
                'abrev' => 'sometimes|required|string|max:50',
                'fechaIni' => 'sometimes|required|date',
                'fechaFin' => 'nullable|date',
                'saldoIni' => 'sometimes|required|string|max:10',
                'saldoFin' => 'sometimes|required|string|max:10',
                'activa' => 'sometimes|boolean',
                'cuotaPasada' => 'sometimes|boolean'
            ]);

            // Si se marca como activa, desactivar las demás
            if (isset($validated['activa']) && $validated['activa']) {
                Temporada::where('id', '!=', $id)->update(['activa' => false]);
            }

            $temporada->update($validated);
        
            return $this->sendResponse($temporada->fresh(), 'Temporada actualizada correctamente', 200);

        } catch(\Exception $e) {
             \Log::error('Error al actualizar la temporada ' . $e->getMessage());
            return $this->sendError(
                'Error al actualizar temporada',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Activar una temporada
     */
    public function activar($id)
    {
        try{
            // Desactivamos todas las temporadas
            Temporada::query()->update(['activa' => false]);
            
            // Activar la temporada con el id
            $temporada = Temporada::findOrFail($id);
            $temporada->activa = true;
            $temporada->save();

            return $this->sendResponse($temporada, 'Temporada activada correctamente', 200);
        } catch(\Exception $e) {
             \Log::error('Error al activar la temporada ' . $e->getMessage());
            return $this->sendError(
                'Error al activar temporada',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }
        
    }

    /**
     * Eliminar temporada
     */
    public function destroy($id)
    {
        try{
            $temporada = Temporada::findOrFail($id);
        
            // LA temporada activa no la podemos borrar, primero debemos crear otra y activarla
            if ($temporada->activa) {
                return $this->sendError(
                    'No se puede eliminar la temporada activa, debes activar primero otra temporada.',
                    [],
                    403
                );
            }
            
            $temporada->delete();

            return $this->sendResponse(NULL, 'Temporada eliminada correctamente', 200);

        } catch(\Exception $e) {
             \Log::error('Error al eliminar la temporada ' . $e->getMessage());
            return $this->sendError(
                'Error al eliminar temporada',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }
        
    }
}
