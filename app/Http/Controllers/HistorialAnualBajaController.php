<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HistorialAnualBaja;

class HistorialAnualBajaController extends BaseController
{
    /**
     * Listar historial de bajas
     */
    public function index(Request $request)
    {
        try{
            $query = HistorialAnualBaja::with(['socio', 'temporada']);
            
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

            return $this->sendResponse($historial, 'Historiales de bajas obtenidos correctamente', 200);

        } catch (\Exception $e) {            
            \Log::error('Error al obtener los historiales de baja: ' . $e->getMessage());
            return $this->sendError(
                'Error al obtener los historiales de baja',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Obtener historial de un socio de baja
     */
    public function porSocio($socioId)
    {
        try{
            $historial = HistorialAnualBaja::with('temporada')
                ->where('a_socio_baja', $socioId)
                ->orderBy('a_temporada', 'desc')
                ->get();

            return $this->sendResponse($historial, 'Historial de baja de socio', 200);
        } catch (\Exception $e) {            
            \Log::error('Error al obtener Historial de baja de socio: ' . $e->getMessage());
            return $this->sendError(
                'Error al obtener Historial de baja de socio',
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
            $historial = HistorialAnualBaja::where('a_socio_baja', $socioId)
                ->where('a_temporada', $temporadaId)
                ->firstOrFail();
            
            $historial->cuota_pagada = true;
            $historial->save();

            return $this->sendResponse($historial, 'Cuota marcada como pagada', 200);

        } catch (\Exception $e) {            
            \Log::error('Error al marcar Pagada cuota socio baja: ' . $e->getMessage());
            return $this->sendError(
                'Error al marcar Pagada cuota socio baja',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }
    }
}
