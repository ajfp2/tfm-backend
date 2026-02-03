<?php

namespace App\Http\Controllers;

use App\Models\Correspondencia;
use App\Models\DetalleCorrespondencia;
use App\Models\SocioPersona;
use App\Models\CorrespondenciaJunta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class CorrespondenciaController extends BaseController
{
    /**
     * Listar toda la correspondencia
     * GET /api/correspondencia
     */
    public function index(Request $request)
    {
        try {
            $query = Correspondencia::with(['temporada', 'convocatoria', 'cargoFirmante']);

            // Filtros
            if ($request->has('temporada_id')) {
                $query->where('fk_temporadas', $request->temporada_id);
            }

            if ($request->has('estado')) {
                if ($request->estado == 'pendiente') {
                    $query->pendientes();
                } elseif ($request->estado == 'enviada') {
                    $query->enviadas();
                }
            }

            if ($request->has('con_convocatoria')) {
                if ($request->con_convocatoria == '1') {
                    $query->conConvocatoria();
                } else {
                    $query->independiente();
                }
            }

            $correspondencias = $query->recientes()->get();

            // Añadir estadísticas de destinatarios
            $correspondencias->each(function ($correspondencia) {
                $correspondencia->total_email = $correspondencia->totalEmail();
                $correspondencia->total_papel = $correspondencia->totalPapel();
                $correspondencia->total_realizados = $correspondencia->totalRealizados();
                $correspondencia->total_pendientes = $correspondencia->totalPendientes();
                $correspondencia->total_destinatarios = $correspondencia->destinatarios()->count();
            });

            return $this->sendResponse($correspondencias, 'Correspondencia obtenida correctamente');
        } catch (\Exception $e) {
            return $this->sendError('Error al obtener correspondencia', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtener correspondencia específica con destinatarios
     * GET /api/correspondencia/{id}
     */
    public function show($id)
    {
        try {
            $correspondencia = Correspondencia::with([
                'temporada',
                'convocatoria',
                'cargoFirmante',
                'destinatarios.persona'
            ])->findOrFail($id);

            // Añadir estadísticas
            $correspondencia->total_email = $correspondencia->totalEmail();
            $correspondencia->total_papel = $correspondencia->totalPapel();
            $correspondencia->total_realizados = $correspondencia->totalRealizados();
            $correspondencia->total_pendientes = $correspondencia->totalPendientes();

            return $this->sendResponse($correspondencia, 'Correspondencia obtenida correctamente');
        } catch (\Exception $e) {
            return $this->sendError('Correspondencia no encontrada', ['error' => $e->getMessage()], 404);
        }
    }

    /**
     * Crear nueva correspondencia
     * POST /api/correspondencia
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'descripcion' => 'required|string|max:100',
                'asunto' => 'required|string|max:150',
                'texto' => 'required',
                'fk_temporadas' => 'required|exists:temporadas,id',
                'fk_convocatoria' => 'nullable|exists:correspondencia_juntas,id',
                'firma_cargo' => 'nullable|exists:junta_directiva,id',
                'vb_presidente' => 'nullable|boolean',
                'archivo' => 'nullable|file|mimes:pdf,doc,docx|max:10240' // 10MB max
            ]);

            if ($validator->fails()) {
                return $this->sendError('Error de validación', [$validator->errors()], 422);
            }

            // Manejar archivo adjunto
            $rutaFichero = null;
            if ($request->hasFile('archivo')) {
                $file = $request->file('archivo');
                $filename = time() . '_' . $file->getClientOriginalName();
                $rutaFichero = $file->storeAs('correspondencia/adjuntos', $filename);
            } elseif ($request->fk_convocatoria) {
                // Si está vinculada a convocatoria, usar el PDF de la convocatoria
                $rutaFichero = 'correspondencia/convocatorias/convocatoria_' . $request->fk_convocatoria . '.pdf';
            }

            $correspondencia = Correspondencia::create([
                'descripcion' => $request->descripcion,
                'asunto' => $request->asunto,
                'texto' => $request->texto,
                'creado' => now(),
                'rutafichero' => $rutaFichero,
                'firma_cargo' => $request->firma_cargo,
                'vb_presidente' => $request->vb_presidente ?? 0,
                'estadofinalizado' => 0,
                'fk_convocatoria' => $request->fk_convocatoria,
                'fk_temporadas' => $request->fk_temporadas
            ]);

            $correspondencia->load(['temporada', 'convocatoria', 'cargoFirmante']);

            return $this->sendResponse($correspondencia, 'Correspondencia creada correctamente', 201);
        } catch (\Exception $e) {
            return $this->sendError('Error al crear correspondencia', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Actualizar correspondencia
     * PUT /api/correspondencia/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $correspondencia = Correspondencia::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'descripcion' => 'sometimes|required|string|max:100',
                'asunto' => 'sometimes|required|string|max:150',
                'texto' => 'sometimes|required',
                'firma_cargo' => 'nullable|exists:junta_directiva,id',
                'vb_presidente' => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                return $this->sendError('Error de validación', [$validator->errors()], 422);
            }

            $correspondencia->update($request->all());
            $correspondencia->load(['temporada', 'convocatoria', 'cargoFirmante']);

            return $this->sendResponse($correspondencia, 'Correspondencia actualizada correctamente');
        } catch (\Exception $e) {
            return $this->sendError('Error al actualizar correspondencia', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Eliminar correspondencia
     * DELETE /api/correspondencia/{id}
     */
    public function destroy($id)
    {
        try {
            $correspondencia = Correspondencia::findOrFail($id);

            // Eliminar destinatarios
            $correspondencia->destinatarios()->delete();

            // Eliminar archivo si existe y no es de convocatoria
            if ($correspondencia->rutafichero && 
                !str_contains($correspondencia->rutafichero, 'convocatorias') &&
                Storage::exists($correspondencia->rutafichero)) {
                Storage::delete($correspondencia->rutafichero);
            }

            $correspondencia->delete();

            return $this->sendResponse(null, 'Correspondencia eliminada correctamente');
        } catch (\Exception $e) {
            return $this->sendError('Error al eliminar correspondencia', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Añadir destinatarios a la correspondencia
     * POST /api/correspondencia/{id}/destinatarios
     */

    public function agregarDestinatarios(Request $request, $id){
        // \Log::info('Valor ID: ' . $id);
        $validator = Validator::make($request->all(), [
            'tipo' => 'required|in:todos_activos,todos_baja,deudores_activos,deudores_baja,manual',
            'socios_ids' => 'required_if:modo,manual|array',
            'socios_ids.*' => 'integer|exists:socios_personas,Id_Persona'
        ]);

        if(!$id) return $this->sendError('Validación fallida ', ["id" => $id, "param" => $request], 422);

        if ($validator->fails()) {
            return $this->sendError('Validación fallida', [$validator->errors()], 422);
        }

        try {
            $correspondencia = Correspondencia::findOrFail($id);
            $modo = $request->tipo;

            // Obtener socios según el modo
            $socios = collect();
            
            switch ($modo) {
                case 'todos_activos':
                    // Todos los socios activos
                    // $socios = \DB::table('socios_personas')->where('activo', true)->get();
                    $socios = SocioPersona::with(['alta', 'municipio'])->whereHas('alta')->get();
                break;
                    
                case 'todos_baja':
                    // Todos los socios de baja
                    // $socios = \DB::table('socios_personas')->where('activo', false)->get();
                    $socios = SocioPersona::with(['baja', 'municipio'])->whereHas('baja')->get();
                break;
                    
                case 'deudores_activos':
                    // Socios deudores activos
                    // Asumiendo que tienes una columna 'deudor' o similar
                    // $socios = \DB::table('socios_personas')->where('activo', true)->where('deudor', true) // o tu lógica para determinar deudores->get();
                    $socios = SocioPersona::with(['alta', 'municipio', 'historialAnual'])->whereHas('alta')->has('importe_pendiente', '>', 0)->get();
                break;
                    
                case 'deudores_baja':
                    // Socios deudores de baja
                    // $socios = \DB::table('socios_personas')->where('activo', false)->where('deudor', true)->get();
                    $socios = SocioPersona::with(['baja', 'municipio'])->where('deudor', true)->get();
                break;
                    
                case 'manual':
                    // Socios seleccionados manualmente
                    $socios = \DB::table('socios_personas')->whereIn('Id_Persona', $request->socios_ids)->get();
                break;
            }

            // Insertar destinatarios
            $insertados = 0;
            foreach ($socios as $socio) {
                // Verificar si ya existe como destinatario
                $existe = \DB::table('correspondencia_detallecorrespondencia')
                    ->where('fk_correspondencia', $correspondencia->id)
                    ->where('fk_persona', $socio->Id_Persona)
                    ->exists();

                if (!$existe) {
                    // Determinar tipo de envío (email o papel)
                    $papel = empty($socio->Email) || !filter_var($socio->Email, FILTER_VALIDATE_EMAIL);

                    \DB::table('correspondencia_detallecorrespondencia')->insert([
                        'fk_correspondencia' => $correspondencia->id,
                        'fk_persona' => $socio->Id_Persona,
                        'papel' => $papel,
                        'nombre' => $socio->Nombre,
                        'apellidos' => $socio->Apellidos,
                        'direccion' => $socio->Direccion ?? null,
                        'cp' => $socio->CP ?? null,
                        'poblacion' => $socio->Poblacion ?? null,
                        'provincia' => $socio->Provincia ?? null,
                        'pais' => $socio->Pais ?? null,
                        'email' => $socio->Email ?? null,
                        'realizado' => false,
                        'fechaenvio' => null
                    ]);
                    $insertados++;
                }
            }

            return $this->sendResponse([
                'total_socios' => $socios->count(),
                'insertados' => $insertados,
                'duplicados' => $socios->count() - $insertados
            ], "Se añadieron $insertados destinatarios correctamente");

        } catch (\Exception $e) {
            return $this->sendError('Error al añadir destinatarios', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Enviar correspondencia por email
     * POST /api/correspondencia/{id}/enviar-emails
     */
    public function enviarEmails($id)
    {
        try {
            $correspondencia = Correspondencia::with('destinatarios')->findOrFail($id);

            if (!$correspondencia->rutafichero || !Storage::exists($correspondencia->rutafichero)) {
                return $this->sendError('No hay archivo adjunto', [], 400);
            }
            $destinatariosEmail = $correspondencia->destinatarios()->porEmail()->pendientes()->get();
            // $destinatariosEmail = $correspondencia->destinatarios()->where('papel', false)->where('realizado', false)->get();

            if ($destinatariosEmail->isEmpty()) {
                return $this->sendError('No hay destinatarios de email pendientes', [], 400);
            }

            $enviados = 0;
            $errores = [];
            $archivoPath = Storage::path($correspondencia->rutafichero);

            foreach ($destinatariosEmail as $destinatario) {
                try {
                    // Enviar email usando Mailable
                    Mail::to($destinatario->email)->send(
                        new \App\Mail\CorrespondenciaEmail(
                            $correspondencia->asunto,
                            $correspondencia->texto,
                            $archivoPath
                        )
                    );

                    // Marcar como realizado
                    $destinatario->update([
                        'realizado' => true,
                        'fechaenvio' => now()
                    ]);

                    $enviados++;
                } catch (\Exception $e) {
                    $errores[] = [
                        'destinatario' => $destinatario->email,
                        'email' => $destinatario->email,
                        'error' => $e->getMessage()
                    ];
                }
            }

            // Actualizar fecha de envío de la correspondencia
            if ($enviados > 0) {
                $correspondencia->update(['diaenvio' => now()]);
            }

            return $this->sendResponse([
                'enviados' => $enviados,
                'errores' => $errores,
                'total' => $destinatariosEmail->count()
            ], "Se enviaron $enviados emails correctamente");

        } catch (\Exception $e) {
            return $this->sendError('Error al enviar emails', [
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar PDF para imprimir cartas
     * GET /api/correspondencia/{id}/generar-cartas-pdf
     */
    /**
 * Generar PDF con cartas para papel
 * GET /api/correspondencia/{id}/generar-cartas-pdf
 */
    public function generarCartasPdf($id)
    {
        try {
            $correspondencia = Correspondencia::with([
                'cargoFirmante',
                'destinatarios' => function($query) {
                    $query->where('papel', true);
                }
            ])->findOrFail($id);

            if ($correspondencia->destinatarios->isEmpty()) {
                return $this->sendError('No hay destinatarios de papel', [], 404);
            }

            // Cargar configuración para logo y datos
            $config = \App\Models\Configuracion::first();
            $penya = \App\Models\Penya::first();

            // Generar PDF con todas las cartas
            $pdf = Pdf::loadView('correspondencia.cartas-pdf', compact('correspondencia', 'config', 'penya'));

            $filename = 'cartas_' . $id . '_' . time() . '.pdf';
            
            return $pdf->download($filename);
        } catch (\Exception $e) {
            return $this->sendError('Error al generar PDF de cartas', ['error' => $e->getMessage()], 500);
        }
    }
    

    /**
     * Marcar cartas de papel como impresas
     * POST /api/correspondencia/{id}/marcar-impresas
     */
    public function marcarImpresas($id)
    {
        try {
            $correspondencia = Correspondencia::findOrFail($id);

            $correspondencia->destinatarios()
                ->porPapel()
                ->update([
                    'realizado' => 1,
                    'fechaenvio' => now()
                ]);

            // Actualizar estado si todo está completado
            if ($correspondencia->totalPendientes() === 0) {
                $correspondencia->update([
                    'estadofinalizado' => 1,
                    'diaenvio' => now()
                ]);

                // Marcar convocatoria como enviada si está vinculada
                if ($correspondencia->fk_convocatoria) {
                    $correspondencia->convocatoria->update(['estado' => 1]);
                }
            }

            return $this->sendResponse($correspondencia, 'Cartas marcadas como impresas');
        } catch (\Exception $e) {
            return $this->sendError('Error al marcar como impresas', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtener histórico de envíos
     * GET /api/correspondencia/historico
     */
    public function historico(Request $request)
    {
        try {
            $query = Correspondencia::with(['temporada', 'convocatoria', 'cargoFirmante'])
                ->enviadas();

            // Filtros
            if ($request->has('temporada_id')) {
                $query->where('fk_temporadas', $request->temporada_id);
            }

            if ($request->has('desde') && $request->has('hasta')) {
                $query->whereBetween('diaenvio', [$request->desde, $request->hasta]);
            }

            $historico = $query->recientes()->get();

            // Añadir estadísticas
            $historico->each(function ($correspondencia) {
                $correspondencia->total_email = $correspondencia->totalEmail();
                $correspondencia->total_papel = $correspondencia->totalPapel();
                $correspondencia->total_destinatarios = $correspondencia->destinatarios()->count();
            });

            return $this->sendResponse($historico, 'Histórico obtenido correctamente');
        } catch (\Exception $e) {
            return $this->sendError('Error al obtener histórico', ['error' => $e->getMessage()], 500);
        }
    }
}