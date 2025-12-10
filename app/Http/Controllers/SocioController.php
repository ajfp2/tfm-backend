<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\SocioPersona;
use App\Models\SocioAlta;
use App\Models\SocioBaja;
use App\Models\HistorialAnual;
use App\Models\Temporada;

use Illuminate\Support\Facades\DB;
class SocioController extends BaseController
{
    /**
     * Listar todos los socios (activos, bajas o todos)
     */
    public function index(Request $request)
    {
        try{
        
            $tipo = $request->get('tipo', 'activos'); // activos, bajas, todos
            
            $query = SocioPersona::with(['municipio', 'provincia', 'pais', 'nacionalidad']);
            
            switch ($tipo) {
                case 'activos':
                    $query->with(['alta.tipoSocio', 'alta.formaPago'])->whereHas('alta');
                break;
                case 'bajas':
                    $query->with(['baja.tipoSocio', 'baja.formaPago'])->whereHas('baja');
                break;
                case 'todos':
                    $query->with(['alta.tipoSocio', 'alta.formaPago', 'baja.tipoSocio', 'baja.formaPago']);
                break;
            }
            
            // Buscar socio por nombre/apellidos/DNI
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('Nombre', 'like', "%{$search}%")
                    ->orWhere('Apellidos', 'like', "%{$search}%")
                    ->orWhere('DNI', 'like', "%{$search}%");
                });
            }
            
            // Filtrar por tipo de socio
            if ($request->has('tipo_socio')) {
                if ($tipo === 'activos') {
                    $query->whereHas('alta', function($q) use ($request) {
                        $q->where('fk_tipoSocio', $request->tipo_socio);
                    });
                } elseif ($tipo === 'bajas') {
                    $query->whereHas('baja', function($q) use ($request) {
                        $q->where('fk_tipoSocio', $request->tipo_socio);
                    });
                }
            }
            
            $socios = $query->orderBy('Apellidos')->orderBy('Nombre')->get();

            return $this->sendResponse($socios, 'Socios obtenidos correctamente', 200);
            

        } catch(\Exception $e) {
             \Log::error('Error al listar los socios' . $e->getMessage());
            return $this->sendError( 'Error al listar socios', 
            ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()], 500);
        }
    }

    public function deudores(){
        // try{

        //     $query = SocioPersona::with(['municipio', 'provincia', 'pais', 'nacionalidad']);
        //     $temporada = Temporada::where('activa', true)->first();
        
        //     if (!$temporada) {
        //         return $this->sendError(
        //             'No hay temporada activa',
        //             [],
        //             404
        //         );
        //     }

        //     return $this->sendResponse($temporada, 'Temporada activa obtenida correctamente.', 200);

        // } catch(\Exception $e) {
        //      \Log::error('Error al obtener la temporada activa: ' . $e->getMessage());
        //     return $this->sendError(
        //         'Error al obtener temporada activa',
        //         ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
        //         500
        //     );
        // }
    }

    public function exentos(){
        try{

            $socios = DB::table('socios_personas as sp')
                ->join('socios_alta as sa', 'sp.Id_Persona', '=', 'sa.a_Persona')
                ->join('socios_tipo_socio as ts', 'sa.fk_tipoSocio', '=', 'ts.id_tipo')
                ->where('ts.exentos_pago', 1)
                ->orderBy('sp.Apellidos')
                ->orderBy('sp.Nombre')
                ->get();
        
            return $this->sendResponse($socios, 'Socios exentos obtenidos correctamente.', 200);

        } catch(\Exception $e) {
             \Log::error('Error al obtener los socios exentos: ' . $e->getMessage());
            return $this->sendError(
                'Error al obtener los socios exentos',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Mostrar un socio completo (persona, alta/baja, historial)
     */
    public function show($id)
    {
        try{
            $socio = SocioPersona::with([
                'municipio',
                'provincia',
                'pais',
                'nacionalidad',
                'alta.tipoSocio',
                'alta.formaPago',
                'baja.tipoSocio',
                'baja.formaPago',
                'historialAnual.temporada',
                'cargosDirectivos.cargo',
                'cargosDirectivos.temporada'
            ])->findOrFail($id);

            return $this->sendResponse($socio, 'Socio obtenido correctamente', 200);

        } catch(\Exception $e) {
             \Log::error('Error al mostrar un socio' . $e->getMessage());
            return $this->sendError( 'Error al mostrar un socio', 
            ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Crear un socio completo (persona, alta, historial)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Datos de persona
            'Nombre' => 'required|string|max:50',
            'Apellidos' => 'required|string|max:100',
            'DNI' => 'required|string|max:9|unique:socios_personas,DNI',
            'Movil' => 'nullable|string|max:9',
            'Email' => 'nullable|email|max:150',
            'Talla' => 'nullable|string|max:4',
            'Sexo' => 'nullable|in:H,M,Otro',
            'FNac' => 'nullable|date',
            'Direccion' => 'nullable|string|max:100',
            'CP' => 'nullable|string|max:5',
            'Poblacion' => 'nullable|exists:socios_municipios,id',
            'Provincia' => 'nullable|exists:socios_provincias,id',
            'Pais' => 'nullable|exists:socios_nacionalidad,id',
            'Nacionalidad' => 'nullable|exists:socios_nacionalidad,id',
            'IBAN' => 'nullable|string|max:24',
            'BIC' => 'nullable|string|max:11',
            
            // Datos de alta
            'fk_tipoSocio' => 'required|exists:socios_tipo_socio,id_tipo',
            'fecha_alta' => 'required|date',
            'n_carnet' => 'nullable|integer',
            'sin_correspondencia' => 'sometimes|boolean',
            'c_carta' => 'sometimes|boolean',
            'c_email' => 'sometimes|boolean',
            'formaPago' => 'required|exists:socios_forma_pago,id',
            'fichaMadrid' => 'sometimes|boolean'
        ]);

        DB::beginTransaction();
        
        try {
            // Primero Creamos a la persona
            $datosPersona = [
                'Nombre' => $validated['Nombre'],
                'Apellidos' => $validated['Apellidos'],
                'DNI' => $validated['DNI'],
                'Movil' => $validated['Movil'] ?? null,
                'Email' => $validated['Email'] ?? null,
                'Talla' => $validated['Talla'] ?? null,
                'Sexo' => $validated['Sexo'] ?? null,
                'FNac' => $validated['FNac'] ?? null,
                'Direccion' => $validated['Direccion'] ?? null,
                'CP' => $validated['CP'] ?? null,
                'Poblacion' => $validated['Poblacion'] ?? null,
                'Provincia' => $validated['Provincia'] ?? null,
                'Pais' => $validated['Pais'] ?? null,
                'Nacionalidad' => $validated['Nacionalidad'] ?? null,
                'IBAN' => $validated['IBAN'] ?? null,
                'BIC' => $validated['BIC'] ?? null,
            ];
            
            $persona = SocioPersona::create($datosPersona);
            
            // 2. Obtener siguiente número de socio
            $ultimoNsocio = SocioAlta::max('nsocio') ?? 0;
            
            // 3. Creamos el alta en socios_altas
            $datosAlta = [
                'a_Persona' => $persona->Id_Persona,
                'nsocio' => $ultimoNsocio + 1,
                'fk_tipoSocio' => $validated['fk_tipoSocio'],
                'fecha_alta' => $validated['fecha_alta'],
                'n_carnet' => $validated['n_carnet'] ?? 0,
                'sin_correspondencia' => $validated['sin_correspondencia'] ?? false,
                'c_carta' => $validated['c_carta'] ?? true,
                'c_email' => $validated['c_email'] ?? true,
                'formaPago' => $validated['formaPago'],
                'fichaMadrid' => $validated['fichaMadrid'] ?? true,
            ];
            
            $socioAlta = SocioAlta::create($datosAlta);
            
            // 4. Creamos historial anual para la temporada activa
            $temporadaActiva = Temporada::where('activa', true)->first();
            if ($temporadaActiva) {
                $tipoSocio = \App\Models\SocioTipoSocio::find($validated['fk_tipoSocio']);
                
                HistorialAnual::create([
                    'a_socio' => $persona->Id_Persona,
                    'a_temporada' => $temporadaActiva->id,
                    'cuota_pagada' => false,
                    'exento' => $tipoSocio->exentos_pago ? 1 : 0
                ]);
            }
            
            DB::commit();
            
            return $this->sendResponse($persona->load([
                'alta.tipoSocio',
                'alta.formaPago',
                'municipio',
                'provincia',
                'pais',
                'nacionalidad'
            ]), 'Socio creado correctamente', 201);
            
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error al crear socio: ' . $e->getMessage());

            return $this->sendError( 'Error al crear el socio', 
            ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Actualizar socio (persona y datos de alta/baja)
     */
    public function update(Request $request, $id)
    {
        $persona = SocioPersona::findOrFail($id);
        
        $validated = $request->validate([
            // Datos de persona
            'Nombre' => 'sometimes|required|string|max:50',
            'Apellidos' => 'sometimes|required|string|max:100',
            'DNI' => 'sometimes|required|string|max:9|unique:socios_personas,DNI,' . $id . ',Id_Persona',
            'Movil' => 'nullable|string|max:9',
            'Email' => 'nullable|email|max:150',
            'Talla' => 'nullable|string|max:4',
            'Sexo' => 'nullable|in:H,M,Otro',
            'FNac' => 'nullable|date',
            'Direccion' => 'nullable|string|max:100',
            'CP' => 'nullable|string|max:5',
            'Poblacion' => 'nullable|exists:socios_municipios,id',
            'Provincia' => 'nullable|exists:socios_provincias,id',
            'Pais' => 'nullable|exists:socios_nacionalidad,id',
            'Nacionalidad' => 'nullable|exists:socios_nacionalidad,id',
            'IBAN' => 'nullable|string|max:24',
            'BIC' => 'nullable|string|max:11',
            
            // Datos de alta
            'fk_tipoSocio' => 'sometimes|exists:socios_tipo_socio,id_tipo',
            'fecha_alta' => 'sometimes|date',
            'n_carnet' => 'nullable|integer',
            'sin_correspondencia' => 'sometimes|boolean',
            'c_carta' => 'sometimes|boolean',
            'c_email' => 'sometimes|boolean',
            'formaPago' => 'sometimes|exists:socios_forma_pago,id',
            'fichaMadrid' => 'sometimes|boolean'
        ]);

        DB::beginTransaction();
        
        try {
            // 1. Actualizamos los datos de la persona
            $datosPersona = array_filter([
                'Nombre' => $validated['Nombre'] ?? null,
                'Apellidos' => $validated['Apellidos'] ?? null,
                'DNI' => $validated['DNI'] ?? null,
                'Movil' => $validated['Movil'] ?? null,
                'Email' => $validated['Email'] ?? null,
                'Talla' => $validated['Talla'] ?? null,
                'Sexo' => $validated['Sexo'] ?? null,
                'FNac' => $validated['FNac'] ?? null,
                'Direccion' => $validated['Direccion'] ?? null,
                'CP' => $validated['CP'] ?? null,
                'Poblacion' => $validated['Poblacion'] ?? null,
                'Provincia' => $validated['Provincia'] ?? null,
                'Pais' => $validated['Pais'] ?? null,
                'Nacionalidad' => $validated['Nacionalidad'] ?? null,
                'IBAN' => $validated['IBAN'] ?? null,
                'BIC' => $validated['BIC'] ?? null,
            ], function($value) {
                return $value !== null;
            });
            
            if (!empty($datosPersona)) {
                $persona->update($datosPersona);
            }
            
            // 2. Actualizar datos de alta si está de akta
            if ($persona->alta()->exists()) {
                $datosAlta = array_filter([
                    'fk_tipoSocio' => $validated['fk_tipoSocio'] ?? null,
                    'fecha_alta' => $validated['fecha_alta'] ?? null,
                    'n_carnet' => $validated['n_carnet'] ?? null,
                    'sin_correspondencia' => $validated['sin_correspondencia'] ?? null,
                    'c_carta' => $validated['c_carta'] ?? null,
                    'c_email' => $validated['c_email'] ?? null,
                    'formaPago' => $validated['formaPago'] ?? null,
                    'fichaMadrid' => $validated['fichaMadrid'] ?? null,
                ], function($value) {
                    return $value !== null;
                });
                
                if (!empty($datosAlta)) {
                    $persona->alta->update($datosAlta);
                }
            }
            
            DB::commit();
            
            return $this->sendResponse(
            $persona->fresh([
                'alta.tipoSocio',
                'alta.formaPago',
                'municipio',
                'provincia',
                'pais',
                'nacionalidad'
            ]), 'Socio actualizado correctamente', 200);
            
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error al actualizar socio: ' . $e->getMessage());
            return $this->sendError( 'Error al actualizar el socio', 
            ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Dar de baja a un socio
     */
    public function darBaja(Request $request, $id)
    {
        $validated = $request->validate([
            'fecha_baja' => 'required|date',
            'motivo_baja' => 'required|string|max:500',
            'deudor' => 'sometimes|boolean',
            'deuda' => 'nullable|numeric|min:0'
        ]);

        DB::beginTransaction();
        
        try {
            $persona = SocioPersona::findOrFail($id);
            $socioAlta = $persona->alta;
            
            if (!$socioAlta) {
                return $this->sendError( 'Este socio no está activo', [], 404);
            }
            
            // Crear registro en socios_baja
            SocioBaja::create([
                'a_Persona' => $socioAlta->a_Persona,
                'nsocio' => $socioAlta->nsocio,
                'fk_tipoSocio' => $socioAlta->fk_tipoSocio,
                'fecha_alta' => $socioAlta->fecha_alta,
                'fecha_baja' => $validated['fecha_baja'],
                'motivo_baja' => $validated['motivo_baja'],
                'deudor' => $validated['deudor'] ?? false,
                'deuda' => $validated['deuda'] ?? 0,
                'n_carnet' => $socioAlta->n_carnet,
                'sin_correspondencia' => $socioAlta->sin_correspondencia,
                'c_carta' => $socioAlta->c_carta,
                'c_email' => $socioAlta->c_email,
                'formaPago' => $socioAlta->formaPago,
                'fichaMadrid' => $socioAlta->fichaMadrid
            ]);
            
            // Eliminar de socios_alta
            $socioAlta->delete();
            
            DB::commit();

            return $this->sendResponse($persona->fresh(['baja.tipoSocio', 'baja.formaPago']), 'Socio dado de baja correctamente', 200);
            
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error al dar de baja socio: ' . $e->getMessage());
            return $this->sendError( 'Error al dar de baja al socio', ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Reactivar un socio (de baja a alta)
     */
    public function reactivar(Request $request, $id)
    {
        $validated = $request->validate([
            'fecha_alta' => 'required|date'
        ]);

        DB::beginTransaction();
        
        try {
            $persona = SocioPersona::findOrFail($id);
            $socioBaja = $persona->baja;
            
            if (!$socioBaja) {
                return $this->sendError( 'Este socio no está de baja', [], 404);
            }
            
            // Crear registro en socios_alta
            SocioAlta::create([
                'a_Persona' => $socioBaja->a_Persona,
                'nsocio' => $socioBaja->nsocio,
                'fk_tipoSocio' => $socioBaja->fk_tipoSocio,
                'fecha_alta' => $validated['fecha_alta'],
                'n_carnet' => $socioBaja->n_carnet,
                'sin_correspondencia' => $socioBaja->sin_correspondencia,
                'c_carta' => $socioBaja->c_carta,
                'c_email' => $socioBaja->c_email,
                'formaPago' => $socioBaja->formaPago,
                'fichaMadrid' => $socioBaja->fichaMadrid
            ]);
            
            // Crear historial anual para temporada activa
            $temporadaActiva = Temporada::where('activa', true)->first();
            if ($temporadaActiva) {
                $tipoSocio = \App\Models\SocioTipoSocio::find($socioBaja->fk_tipoSocio);
                
                HistorialAnual::firstOrCreate(
                    [
                        'a_socio' => $persona->Id_Persona,
                        'a_temporada' => $temporadaActiva->id
                    ],
                    [
                        'cuota_pagada' => false,
                        'exento' => $tipoSocio->exentos_pago ? 1 : 0
                    ]
                );
            }
            
            // Eliminar de socios_baja
            $socioBaja->delete();
            
            DB::commit();
                        
            return $this->sendResponse($persona->fresh(['alta.tipoSocio', 'alta.formaPago']), 'Socio reactivado correctamente', 200);
            
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error al reactivar socio: ' . $e->getMessage());
            return $this->sendError( 'rror al reactivar al socio', ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Eliminar un socio definitivamente (solo si está de baja)
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        
        try {
            $persona = SocioPersona::findOrFail($id);
            
            // Verificar que esté de baja
            if (!$persona->baja()->exists()) {
                return $this->sendError( 'Solo se pueden eliminar socios que estén dados de baja', [], 403);                
            }
            
            // Eliminar primero de la tabla socios_baja
            $persona->baja()->delete();
            
            // Eliminar la persona
            $persona->delete();
            
            DB::commit();
            
            return $this->sendResponse([], 'Socio eliminado definitivamente', 200);
            
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error al eliminar socio: ' . $e->getMessage());
                        
            return $this->sendError( 'Error al eliminar al socio', ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()], 500);
            
        }
    }
}
