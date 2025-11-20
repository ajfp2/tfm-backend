<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HistorialCargoDirectiva;

class HistorialCargoDirectivoController extends BaseController
{
    /**
     * Listar todos los socios con cargos asignados
     */
    public function index(Request $request)
    {
        try{
        
            $query = HistorialCargoDirectiva::with(['temporada', 'persona', 'cargo']);
            
            // Si la temporada viene como parametro, se devuelven solo los de la temporada
            if ($request->has('temporada_id')) {
                $query->where('a_temporada', $request->temporada_id);
            }
            
            $cargos = $query->get();
            
            return $this->sendResponse($cargos, 'Cargos Junta Directiva obtenidos correctamente.', 200);

        } catch(\Exception $e) {
             \Log::error('Error al obtener los cargos de la junta directiva: ' . $e->getMessage());
            return $this->sendError(
                'Error al obtener los cargos de la junta directiva',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Obtener la junta directiva de una temporada en concreto
     */
    public function porTemporada($temporadaId)
    {
        try{

            $cargos = HistorialCargoDirectiva::with(['persona', 'cargo'])->where('a_temporada', $temporadaId)->get();
                    
            return $this->sendResponse($cargos, 'Cargos Junta Directiva Temporada '.$temporadaId, 200);

        } catch(\Exception $e) {
             \Log::error('Error al obtener los cargos de la junta de la temporada '. $temporadaId . $e->getMessage());
            return $this->sendError(
                'Error al obtener los cargos de la junta directiva de la temp:' . $temporadaId,
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }
        
    }

    /**
    * Asignar cargo a socio
    */
    public function store(Request $request)
    {
        try{
            $validated = $request->validate([
                'a_temporada' => 'required|exists:temporadas,id',
                'a_persona' => 'required|exists:socios_personas,Id_Persona',
                'a_cargo' => 'required|exists:junta_directiva,id'
            ]);

            // Verificar si ya existe la asignación
            $existe = HistorialCargoDirectiva::where('a_temporada', $validated['a_temporada'])
                ->where('a_persona', $validated['a_persona'])
                ->where('a_cargo', $validated['a_cargo'])
                ->exists();
            
            if ($existe) {

                return $this->sendError('Este cargo ya está asignado.', [], 409);
            }

            $asignacion = HistorialCargoDirectiva::create($validated);

            return $this->sendResponse($asignacion->load(['temporada', 'persona', 'cargo']), 'Cargo asignado correctamente ', 201);

        } catch(\Exception $e) {
             \Log::error('Error al asignar el cargo al socio' . $e->getMessage());
            return $this->sendError(
                'Error al asignar el cargo al socio',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Eliminar asignación de cargo
     */
    public function destroy(Request $request)
    {
        try{
        
            $validated = $request->validate([
                'a_temporada' => 'required|exists:temporadas,id',
                'a_persona' => 'required|exists:socios_personas,Id_Persona',
                'a_cargo' => 'required|exists:junta_directiva,id'
            ]);

            $asignacion = HistorialCargoDirectiva::where('a_temporada', $validated['a_temporada'])
                ->where('a_persona', $validated['a_persona'])
                ->where('a_cargo', $validated['a_cargo'])
                ->first();
            
            if (!$asignacion) {
                return $this->sendError('El cargo a asignar no encontrado.', [], 404);                
            }
            
            $asignacion->delete();
            
            return $this->sendResponse(NULL, 'Cargo quitado del socio correctamente', 201);

        } catch(\Exception $e) {
             \Log::error('Error al eliminar el cargo al socio' . $e->getMessage());
            return $this->sendError(
                'Error al eliminar el cargo al socio',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }
    }
}
