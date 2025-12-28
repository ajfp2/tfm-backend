<?php

namespace App\Http\Controllers;

use App\Models\Temporada;
use App\Models\SocioPersona;
use App\Models\SocioAlta;
use App\Models\SocioBaja;
use App\Models\HistorialAnual;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends BaseController
{
    /**
     * Obtener estadísticas para el dashboard
     */
    public function index()
    {
        try {
            // Obtener temporada activa
            $temporadaActiva = Temporada::where('activa', true)->first();

            // Estadísticas generales de Totales socios
            $totalSocios = SocioPersona::count();
            $sociosActivos = SocioAlta::count();
            $sociosBajas = SocioBaja::count();
            
            // Socios deudores (bajas con deuda > 0)
            $sociosDeudores = SocioBaja::where('deudor', true)->where('deuda', '>', 0)->count();

            // Total de deuda
            $totalDeuda = SocioBaja::where('deudor', true)->sum('deuda');

            // Estadísticas por género (socios activos)
            $estadisticasGenero = DB::table('socios_personas as sp')
                ->join('socios_alta as sa', 'sp.Id_Persona', '=', 'sa.a_Persona')
                ->select('sp.sexo', DB::raw('count(*) as total'))
                ->groupBy('sp.sexo')
                ->get();

            $hombres = $estadisticasGenero->firstWhere('sexo', 'H')->total ?? 0;
            $mujeres = $estadisticasGenero->firstWhere('sexo', 'M')->total ?? 0;

            // Estadísticas de cuotas si hay temporada activa
            $estadisticasCuotas = null;
            if ($temporadaActiva) {
                $estadisticasCuotas = $this->obtenerEstadisticasCuotas($temporadaActiva->id);
            }

            // Últimas altas (últimos 5 socios dados de alta)
            $ultimasAltas = DB::table('socios_personas as sp')
                ->join('socios_alta as sa', 'sp.Id_Persona', '=', 'sa.a_Persona')
                ->select(
                    'sa.a_Persona',
                    'sp.nombre',
                    'sp.apellidos',
                    'sp.dni',
                    'sa.fecha_alta',
                    'sa.n_carnet'
                )
                ->orderBy('sa.fecha_alta', 'desc')
                ->limit(5)
                ->get();

            // Últimas bajas (últimas 5 bajas)
            $ultimasBajas = DB::table('socios_personas as sp')
                ->join('socios_baja as sb', 'sp.Id_Persona', '=', 'sb.a_Persona')
                ->select(
                    'sb.a_Persona',
                    'sp.nombre',
                    'sp.apellidos',
                    'sp.dni',
                    'sb.fecha_baja',
                    'sb.motivo_baja',
                    'sb.deudor',
                    'sb.deuda'
                )
                ->orderBy('sb.fecha_baja', 'desc')
                ->limit(5)
                ->get();

            $return = [
                'temporada_activa' => $temporadaActiva,
                'socios' => [
                    'total' => $totalSocios,
                    'activos' => $sociosActivos,
                    'bajas' => $sociosBajas,
                    'deudores' => $sociosDeudores,
                    'hombres' => $hombres,
                    'mujeres' => $mujeres
                ],
                'deuda' => [
                    'total' => round($totalDeuda, 2),
                    'cantidad_deudores' => $sociosDeudores
                ],
                'cuotas' => $estadisticasCuotas,
                'actividad_reciente' => [
                    'ultimas_altas' => $ultimasAltas,
                    'ultimas_bajas' => $ultimasBajas
                ]
            ];
            return $this->sendResponse($return, 'Estadísticas Dashboard obtenidas correctamente', 200);

        } catch (\Exception $e) {
            return $this->sendError('Error al obtener estadísticas del dashboard', 
            ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtener estadísticas de cuotas para una temporada
     */
    private function obtenerEstadisticasCuotas($temporadaId)
    {
        $totalCuotas = HistorialAnual::where('a_temporada', $temporadaId)->count();

        $cuotasPagadas = HistorialAnual::where('a_temporada', $temporadaId)->where('cuota_pagada', true)->count();
        
        $cuotasPendientes = $totalCuotas - $cuotasPagadas;
        
        $importeTotal = HistorialAnual::where('a_temporada', $temporadaId)->sum('importe'); // Parte de contabilidad
        
        $importePagado = HistorialAnual::where('a_temporada', $temporadaId)->where('cuota_pagada', true)->sum('importe');
        
        $importePendiente = $importeTotal - $importePagado;
        
        $porcentajePagado = $totalCuotas > 0 ? round(($cuotasPagadas / $totalCuotas) * 100, 2) : 0;

        return [
            'total' => $totalCuotas,
            'pagadas' => $cuotasPagadas,
            'pendientes' => $cuotasPendientes,
            'porcentaje_pagado' => $porcentajePagado,
            'importe' => [
                'total' => round($importeTotal, 2),
                'pagado' => round($importePagado, 2),
                'pendiente' => round($importePendiente, 2)
            ]
        ];
    }

    /**
     * Obtener estadísticas de evolución por meses
    */
    public function evolucion(Request $request)
    {
        try {
            $meses = $request->input('meses', 12); // Por defecto últimos 12 meses
            
            // Evolución de altas por mes
            $evolucionAltas = DB::table('socios_alta')
                ->select(
                    DB::raw('YEAR(fecha_alta) as year'),
                    DB::raw('MONTH(fecha_alta) as month'),
                    DB::raw('COUNT(*) as total')
                )
                ->where('fecha_alta', '>=', DB::raw("DATE_SUB(NOW(), INTERVAL $meses MONTH)"))
                ->groupBy('year', 'month')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();

            // Evolución de bajas por mes
            $evolucionBajas = DB::table('socios_baja')
                ->select(
                    DB::raw('YEAR(fecha_baja) as year'),
                    DB::raw('MONTH(fecha_baja) as month'),
                    DB::raw('COUNT(*) as total')
                )
                ->where('fecha_baja', '>=', DB::raw("DATE_SUB(NOW(), INTERVAL $meses MONTH)"))
                ->groupBy('year', 'month')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();

            
            return $this->sendResponse([
                    'altas' => $evolucionAltas,
                    'bajas' => $evolucionBajas
                ], 'Estadísticas de evolución obtenidas correctamente', 200);
        } catch (\Exception $e) {
            return $this->sendError( 'Error al obtener evolución', 
            ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtener estadísticas por tipo de socio
     */
    public function tiposSocio()
    {
        try {
            $estadisticas = DB::table('socios_personas as sp')
                ->join('socios_alta as sa', 'sp.Id_Persona', '=', 'sa.a_Persona')
                ->join('tipos_socio as ts', 'sa.fk_tipoSocio', '=', 'ts.id_tipo')
                ->select(
                    'ts.tipo',
                    DB::raw('COUNT(*) as total')
                )
                ->groupBy('ts.tipo')
                ->orderBy('total', 'desc')
                ->get();

            return $this->sendResponse($estadisticas, 'Estadísticas obtenidas correctamente', 200);

        } catch (\Exception $e) {
            return $this->sendError( 'Error al obtener estadísticas por tipo de soci0', 
            ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()], 500);           
        }
    }

    /**
     * Obtener saldos de las últimas N temporadas
    */
    public function saldosTemporadas(Request $request)
    {
        try {
            $limit = $request->input('limit', 5);

            // Obtener las últimas N temporadas ordenadas por fecha_inicio
            $temporadas = Temporada::orderBy('fechaIni', 'desc')
                ->limit($limit)
                ->get()
                ->reverse() // Invertir para que vaya de más antigua a más reciente
                ->values();

            $estadisticas = [];
            
            foreach ($temporadas as $temporada) {
                // Saldo inicial (de la temporada)
                $num = random_int(1,200);
                $saldoInicial = $temporada->saldoIni ?? 0;

                $saldoFinal = $temporada->saldoFin ?? 0;

                // Saldo medio = (Saldo inicial + Saldo final) / 2
                $saldoMedio = ($saldoInicial + $saldoFinal) / 2;

                $estadisticas[] = [
                    'temporada' => $temporada->temporada,
                    'abreviatura' => $temporada->abreviatura,
                    'saldo_inicial' => round($saldoInicial, 2),
                    'saldo_final' => round($saldoFinal, 2),
                    'saldo_medio' => round($saldoMedio, 2),
                    'ingresos' => round(1445 + $num, 2),
                    'gastos' => round(950.50 + $num, 2)
                ];
            }

            return $this->sendResponse($estadisticas, 'Estadísticas de saldos temporadas obtenidos correctamente', 200);

        } catch (\Exception $e) {
            return $this->sendError( 'Error al obtener saldos de temporadas', 
            ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()], 500);             
        }
    }
}
