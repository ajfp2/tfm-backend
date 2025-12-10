<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Temporada;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

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
        \Log::info('Obteniendo la temp ' . $id);
        try {
            $temporada = Temporada::findOrFail($id);
            
            return $this->sendResponse($temporada, 'Temporada obtenida correctamente '.$id, 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->sendError(
                'Error: Temporada no encontrada',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                404
            );
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

            $validator = Validator::make($request->all(), [
                'temporada' => 'required|string|max:100|unique:temporadas,temporada',
                'abreviatura' => 'required|string|max:20',
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'nullable|date|after:fecha_inicio',
                'saldo_inicial' => 'nullable|numeric',
                'activa' => 'sometimes|boolean',
                'cuotaPasada' => 'sometimes|boolean'
            ], [
                'temporada.required' => 'La temporada es obligatoria',
                'temporada.unique' => 'Esta temporada ya existe',
                'abreviatura.required' => 'La abreviatura es obligatoria',
                'fecha_inicio.required' => 'La fecha de inicio es obligatoria',
                'fecha_fin.required' => 'La fecha de fin es obligatoria',
                'fecha_fin.after' => 'La fecha de fin debe ser posterior a la fecha de inicio',
                'saldo_inicial.numeric' => 'El saldo inicial debe ser un número'
            ]);

            if ($validator->fails()) {                
                return $this->sendError(
                    'Errores de validación',
                    [$validator->errors()],
                    422
                );
            }
    
            DB::beginTransaction();

            // Si se marca como activa, desactivar las demás
            if ($request->activa) {
                Temporada::where('activa', true)->update(['activa' => false]);
            }

            // $temporada = Temporada::create($validated);
            $temporada = Temporada::create([
                'temporada' => $request->temporada,
                'abreviatura' => $request->abreviatura,
                'fechaIni' => $request->fecha_inicio,
                'fechaFin' => $request->fecha_fin,
                'saldoIni' => $request->saldo_inicial ?? 0,
                'activa' => $request->activa ?? false
            ]);

            DB::commit();

            return $this->sendResponse($temporada, 'Temporada creada correctamente.', 201);
        } catch(\Exception $e) {
            DB::rollBack();
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

            $validator = Validator::make($request->all(), [
                'temporada' => 'required|string|max:50|unique:temporadas,temporada,' . $id,
                'abreviatura' => 'required|string|max:5',
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after:fecha_inicio',
                'saldo_inicial' => 'nullable|numeric|min:0',
                'activa' => 'boolean'
            ], [
                'temporada.required' => 'La temporada es obligatoria',
                'temporada.unique' => 'Esta temporada ya existe',
                'abreviatura.required' => 'La abreviatura es obligatoria',
                'fecha_inicio.required' => 'La fecha de inicio es obligatoria',
                'fecha_fin.required' => 'La fecha de fin es obligatoria',
                'fecha_fin.after' => 'La fecha de fin debe ser posterior a la fecha de inicio',
                'saldo_inicial.numeric' => 'El saldo inicial debe ser un número',
                'saldo_inicial.min' => 'El saldo inicial no puede ser negativo'
            ]);

            if ($validator->fails()) {                
                return $this->sendError(
                    'Errores de validación',
                    [$validator->errors()],
                    422
                );
            }

            $temporada = Temporada::findOrFail($id);
            DB::beginTransaction();

            // Si se marca como activa, desactivar las demás
            if ($request->activa && !$temporada->activa) {
                Temporada::where('activa', true)->update(['activa' => false]);
            }

            $temporada->update([
                'temporada' => $request->temporada,
                'abreviatura' => $request->abreviatura,
                'fechaIni' => $request->fecha_inicio,
                'fechaFin' => $request->fecha_fin,
                'saldoIni' => $request->saldo_inicial ?? 0,
                'activa' => $request->activa ?? false
            ]);
    
            DB::commit();
            
            return $this->sendResponse($temporada->fresh(), 'Temporada actualizada correctamente', 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return $this->sendError(
                'Temporada no encontrada',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                404
            );
            
        } catch(\Exception $e) {
            \Log::error('Error al actualizar la temporada ' . $e->getMessage());
            DB::rollBack();
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
            $temporada = Temporada::findOrFail($id);
            DB::beginTransaction();

            // Desactivamos todas las temporadas
            Temporada::where('activa', true)->update(['activa' => false]);
            
            // Activar la temporada con el id
            $temporada->activa = true;
            $temporada->save();
            DB::commit();
            
            return $this->sendResponse($temporada, 'Temporada activada correctamente', 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Temporada no encontrada'
            ], 404);
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

            // Verificar si tiene registros en historial_anual
            $tieneHistorial = DB::table('historial_anual')->where('a_temporada', $id)->exists();
            if ($tieneHistorial) {                
                return $this->sendError(
                    'No se puede eliminar la temporada porque tiene historiales de socios asociadas',
                    [],
                    400
                );
            }

            // Verificar si tiene cargos directivos asignados
            $tieneCargos = DB::table('historial_cargos_directivos')->where('a_temporada', $id)->exists();
            if ($tieneCargos) {
                return $this->sendError(
                    'No se puede eliminar la temporada porque tiene cargos directivos asignados',
                    [],
                    400
                );
            }

            
            $temporada->delete();

            return $this->sendResponse(NULL, 'Temporada eliminada correctamente', 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {            
            return $this->sendError(
                'Error Temporada no encontrada',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                404
            );
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
