<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HistorialAnual;
use App\Models\Temporada;
use App\Models\SocioAlta;
use Illuminate\Support\Facades\DB;

class HistorialAnualController extends BaseController
{
    /**
     * Listar historial anual
     */
    public function index(Request $request)
    {
        try{
            $query = HistorialAnual::with(['socio', 'temporada']);        
            // Filtrar por temporada
            if ($request->has('temporada_id')) {
                $query->where('a_temporada', $request->temporada_id);
            }
        
            // Filtrar por estado de pago
            if ($request->has('estado')) {
                switch ($request->estado) {
                    case 'pagados':
                        $query->where('cuota_pagada', true);
                        break;
                    case 'pendientes':
                        $query->where('cuota_pagada', false)->where('exento', false);
                        break;
                    case 'exentos':
                        $query->where('exento', true);
                        break;
                }
            }
    
            $historial = $query->get();
            
            return $this->sendResponse($historial, 'Historiales listados correctamente', 200);

        } catch(\Exception $e) {
            \Log::error('Error al listar el historial anual de los socios' . $e->getMessage());
            return $this->sendError(
                'Error al listar el historial anual de los socios',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }
        
    }

    /**
     * Obtener historial de un socio
     */
    public function porSocio($socioId)
    {
        try{
            $historial = HistorialAnual::with('temporada')
                ->where('a_socio', $socioId)
                ->orderBy('a_temporada', 'desc')
                ->get();
            return $this->sendResponse($historial, 'Historial del socio obtenido correctamente', 200);
        } catch(\Exception $e) {
            \Log::error('Error al obtener el historial del socio' . $e->getMessage());
            return $this->sendError(
                'Error al obtener el historial del socio',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Obtener historial de una temporada
     */
    public function porTemporada($temporadaId)
    {
        try{
            $historial = HistorialAnual::with('socio')
                ->where('a_temporada', $temporadaId)
                ->get();

            return $this->sendResponse($historial, 'Historial de la temporada', 200);
        } catch(\Exception $e) {
            \Log::error('Error al obtener el historial de la temporada' . $e->getMessage());
            return $this->sendError(
                'Error al obtener el historial de la temporada',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Generar historial para todos los socios activos en una temporada
     */
    public function generarHistorial(Request $request)
    {
        $validated = $request->validate([
            'temporada_id' => 'required|exists:temporadas,id'
        ]);

        DB::beginTransaction();
        
        try {
            $temporadaId = $validated['temporada_id'];
            
            // Obtener todos los socios activos
            $sociosActivos = SocioAlta::with('tipoSocio')->get();
            
            $creados = 0;
            $existentes = 0;
            
            foreach ($sociosActivos as $socio) {
                // Verificar si ya existe el registro en la BD
                $existe = HistorialAnual::where('a_socio', $socio->a_Persona)
                    ->where('a_temporada', $temporadaId)
                    ->exists();
                
                if (!$existe) {
                    HistorialAnual::create([
                        'a_socio' => $socio->a_Persona,
                        'a_temporada' => $temporadaId,
                        'cuota_pagada' => false,
                        'exento' => $socio->tipoSocio->exentos_pago ? 1 : 0
                    ]);
                    $creados++;
                } else {
                    $existentes++;
                }
            }
            
            DB::commit();                        
            return $this->sendResponse(['creados' => $creados, 'existentes' => $existentes, 'total_socios' => $sociosActivos->count()], 'Historial generado correctamente', 200);
            
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error al generar historial: ' . $e->getMessage());
            return $this->sendError(
                'Error al generar el historial',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Crear entrada en historial manual
     */
    public function store(Request $request)
    {
        try{
            $validated = $request->validate([
                'a_socio' => 'required|exists:socios_personas,Id_Persona',
                'a_temporada' => 'required|exists:temporadas,id',
                'cuota_pagada' => 'sometimes|boolean',
                'exento' => 'sometimes|boolean'
            ]);

            // Verificar que no exista ya
            $existe = HistorialAnual::where('a_socio', $validated['a_socio'])
                ->where('a_temporada', $validated['a_temporada'])
                ->exists();
            
            if ($existe) {
                return $this->sendError('Ya existe un registro para este socio en esta temporada',[],409);
            }

            $historial = HistorialAnual::create($validated);

            return $this->sendResponse($historial->load(['socio', 'temporada']), 'Registro creado correctamente', 201);
        } catch (\Exception $e) {            
            \Log::error('Error al generar el registro en el historial: ' . $e->getMessage());
            return $this->sendError(
                'Error al generar el registro en el historial',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Actualizar historial
     */
    public function update(Request $request, $socioId, $temporadaId)
    {
        try{
            $historial = HistorialAnual::where('a_socio', $socioId)
                ->where('a_temporada', $temporadaId)
                ->firstOrFail();
            
            $validated = $request->validate([
                'cuota_pagada' => 'sometimes|boolean',
                'exento' => 'sometimes|boolean'
            ]);

            $historial->update($validated);
            
            return $this->sendResponse($historial->fresh(['socio', 'temporada']), 'Historial actualizado correctamente', 200);
        } catch (\Exception $e) {            
            \Log::error('Error al actualizar el historial: ' . $e->getMessage());
            return $this->sendError(
                'Error al actualizar el historial',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Marcar cuota como pagada
     */
    public function marcarPagado($socioId, $temporadaId)
    {
        try{        
            $historial = HistorialAnual::where('a_socio', $socioId)
                ->where('a_temporada', $temporadaId)
                ->firstOrFail();
            
            $historial->cuota_pagada = true;
            $historial->save();
            
            return $this->sendResponse($historial, 'Cuota marcada como pagada', 200);
        } catch (\Exception $e) {            
            \Log::error('Error al marcar la cuota pagada: ' . $e->getMessage());
            return $this->sendError(
                'Error al marcar la cuota pagada',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Marcar cuota como no pagada
     */
    public function marcarNoPagado($socioId, $temporadaId)
    {
        try{
            $historial = HistorialAnual::where('a_socio', $socioId)
                ->where('a_temporada', $temporadaId)
                ->firstOrFail();
            
            $historial->cuota_pagada = false;
            $historial->save();
            
            return $this->sendResponse($historial, 'Cuota marcada como NO pagada', 200);
        } catch (\Exception $e) {            
            \Log::error('Error al marcar la cuota NO pagada: ' . $e->getMessage());
            return $this->sendError(
                'Error al marcar la cuota NO pagada',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Obtener estadísticas de una temporada
     */
    public function estadisticas($temporadaId)
    {
        try{
            $total = HistorialAnual::where('a_temporada', $temporadaId)->count();
            $pagados = HistorialAnual::where('a_temporada', $temporadaId)->where('cuota_pagada', true)->count();
            $pendientes = HistorialAnual::where('a_temporada', $temporadaId)->where('cuota_pagada', false)->where('exento', false)->count();
            $exentos = HistorialAnual::where('a_temporada', $temporadaId)->where('exento', true)->count();
            
            return $this->sendResponse([
                'total' => $total,
                'pagados' => $pagados,
                'pendientes' => $pendientes,
                'exentos' => $exentos,
                'porcentaje_pagado' => $total > 0 ? round(($pagados / $total) * 100, 2) : 0
            ], 'Estadisticas obtenidas correctamente', 200);
        } catch (\Exception $e) {
            \Log::error('Error al obtener las estadisticas: ' . $e->getMessage());
            return $this->sendError(
                'Error al obtener las estadisticas',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }
    }
}
