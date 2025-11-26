<?php

namespace App\Http\Controllers;

use App\Models\TareaPendiente;
use Illuminate\Http\Request;

class TareaPendienteController extends BaseController
{
    /**
     * Listar todas las tareas
     */
    public function index(Request $request)
    {
        try{

            $query = TareaPendiente::query();
            
            // Filtrar por estado
            if ($request->has('estado')) {
                switch ($request->estado) {
                    case 'pendientes':
                        $query->where('finalizado', false);
                    break;
                    case 'finalizadas':
                        $query->where('finalizado', true);
                    break;
                }
            }
            
            // Filtrar por menú
            if ($request->has('menu')) {
                $query->where('menu', 'like', "%{$request->menu}%");
            }
            
            $tareas = $query->orderBy('finalizado')->orderBy('progreso')->get();
            
            return $this->sendResponse($tareas, 'Tareas Obtenidas correctamente', 200);

        } catch(\Exception $e) {
            \Log::error('Error al listar las tareas' . $e->getMessage());
            return $this->sendError(
                'Error al listar las tareas',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Listar solo las tareas pendientes
     */
    public function pendientes()
    {
        try{
            $tareas = TareaPendiente::pendientes()->orderBy('progreso')->get();            
            
            return $this->sendResponse($tareas, 'Tareas pendientes obtenidas correctamente', 200);

        } catch(\Exception $e) {
            \Log::error('Error al listar las tareas pendientes' . $e->getMessage());
            return $this->sendError(
                'Error al listar las tareas pendientes',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }
    
    }

    /**
     * Mostrar una tarea
     */
    public function show($id)
    {
        try{
            $tarea = TareaPendiente::findOrFail($id);
            return $this->sendResponse($tarea, 'Tarea Obtenida correctamente', 200);
        } catch(\Exception $e) {
            \Log::error('Error al obtener la tarea '.$id . $e->getMessage());
            return $this->sendError(
                'Error al obtener la tarea '.$id,
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }

    }

    /**
     * Crear tarea
     */
    public function store(Request $request)
    {
        try{
        
            $validated = $request->validate([
                'menu' => 'required|string|max:50',
                'descripcion' => 'required|string|max:250',
                'estado' => 'nullable|string|max:50',
                'rutamenu' => 'nullable|string|max:50',
                'progreso' => 'sometimes|integer|min:0|max:100',
                'finalizado' => 'sometimes|boolean'
            ]);

            $tarea = TareaPendiente::create($validated);
            
            return $this->sendResponse($tarea, 'Tarea creada correctamente', 201);
        } catch(\Exception $e) {
            \Log::error('Error al crear la tarea ' . $e->getMessage());
            return $this->sendError(
                'Error al crear la tarea ',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Actualizar tarea
     */
    public function update(Request $request, $id)
    {
        try{
        
            $tarea = TareaPendiente::findOrFail($id);
            
            $validated = $request->validate([
                'menu' => 'sometimes|required|string|max:50',
                'descripcion' => 'sometimes|required|string|max:250',
                'estado' => 'nullable|string|max:50',
                'rutamenu' => 'nullable|string|max:50',
                'progreso' => 'sometimes|integer|min:0|max:100',
                'finalizado' => 'sometimes|boolean'
            ]);

            $tarea->update($validated);
            
            return $this->sendResponse($tarea->fresh(), 'Tarea actualizada correctamente', 200);

        } catch(\Exception $e) {
            \Log::error('Error al actualizar la tarea '.$id . $e->getMessage());
            return $this->sendError(
                'Error al actualizar la tarea '.$id,
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Actualizar progreso de una tarea
     */
    public function actualizarProgreso(Request $request, $id)
    {
        try{
        
            $validated = $request->validate([
                'progreso' => 'required|integer|min:0|max:100'
            ]);

            $tarea = TareaPendiente::findOrFail($id);
            $tarea->progreso = $validated['progreso'];
            
            // Si llega al 100%, marcar como finalizada
            if ($validated['progreso'] >= 100) {
                $tarea->finalizado = true;
            }
            
            $tarea->save();
            return $this->sendResponse($tarea, 'Progreso actualizado correctamente', 200);
        } catch(\Exception $e) {
            \Log::error('Error al actualizar el Progreso '.$id . $e->getMessage());
            return $this->sendError(
                'Error al actualizar el Progreso '.$id,
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Finalizar una tarea
     */
    public function finalizar($id)
    {
        try{
            $tarea = TareaPendiente::findOrFail($id);
            $tarea->finalizado = true;
            $tarea->progreso = 100;
            $tarea->save();
            
            return $this->sendResponse($tarea, 'Tarea marcada como finalizada', 200);
        } catch(\Exception $e) {
            \Log::error('Error al finalizar la tarea '.$id . $e->getMessage());
            return $this->sendError(
                'Error al finalizar la tarea '.$id,
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Marcar tarea como pendiente
     */
    public function reabrir($id)
    {
        try{
            $tarea = TareaPendiente::findOrFail($id);
            $tarea->finalizado = false;
            $tarea->save();

            return $this->sendResponse($tarea, 'Tarea reabierta de nuevo', 200);
        } catch(\Exception $e) {
            \Log::error('Error al reabrir la tarea '.$id . $e->getMessage());
            return $this->sendError(
                'Error al reabrir la tarea '.$id,
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Eliminar tarea
     */
    public function destroy($id)
    {
        try{
            $tarea = TareaPendiente::findOrFail($id);
            $tarea->delete();        

            return $this->sendResponse(NULL, 'Tarea eliminada correctamente', 200);
        } catch(\Exception $e) {
            \Log::error('Error al eliminar la tarea '.$id . $e->getMessage());
            return $this->sendError(
                'Error al eliminar la tarea '.$id,
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }
    }
}
