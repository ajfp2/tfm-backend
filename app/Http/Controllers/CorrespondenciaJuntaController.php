<?php

namespace App\Http\Controllers;

use App\Models\CorrespondenciaJunta;
use App\Models\Temporada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class CorrespondenciaJuntaController extends BaseController
{
    /**
     * Listar todas las convocatorias
     * GET /api/convocatorias
     */
    public function index(Request $request)
    {
        try {
            $query = CorrespondenciaJunta::with('temporada');

            // Filtros opcionales
            if ($request->has('temporada_id')) {
                $query->where('fk_temporadas', $request->temporada_id);
            }

            if ($request->has('estado')) {
                $query->where('estado', $request->estado);
            }

            if ($request->has('pdfgenerado')) {
                $query->where('pdfgenerado', $request->pdfgenerado);
            }

            if ($request->has('vb_presidente')) {
                $query->where('vb_presidente', $request->vb_presidente);
            }

            // Ordenar por fecha de junta descendente
            $convocatorias = $query->ordenarPorFecha('desc')->get();

            // Añadir contadores de correspondencia vinculada
            $convocatorias->each(function ($convocatoria) {
                $convocatoria->total_correspondencias = $convocatoria->correspondencias()->count();
                $convocatoria->correspondencias_enviadas = $convocatoria->correspondencias()->enviadas()->count();
            });

            return $this->sendResponse($convocatorias, 'Convocatorias obtenidas correctamente');
        } catch (\Exception $e) {
            return $this->sendError('Error al obtener convocatorias', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtener una convocatoria específica
     * GET /api/convocatorias/{id}
     */
    public function show($id)
    {
        try {            
            $convocatoria = CorrespondenciaJunta::with(['temporada', 'cargoFirmante', 'correspondencias'])->findOrFail($id);

            // Añadir información adicional
            $convocatoria->total_correspondencias = $convocatoria->correspondencias()->count();
            $convocatoria->correspondencias_enviadas = $convocatoria->correspondencias()->enviadas()->count();

            return $this->sendResponse($convocatoria, 'Convocatoria obtenida correctamente');
        } catch (\Exception $e) {
            return $this->sendError('Convocatoria no encontrada', ['error' => $e->getMessage()], 404);
        }
    }

    /**
     * Crear nueva convocatoria
     * POST /api/convocatorias
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'fecha_junta' => 'required|date',
                'hora1' => 'required',
                'hora2' => 'required',
                'lugar' => 'required|string|max:150',
                'asunto' => 'required|string|max:250',
                'texto' => 'required',
                'fk_temporadas' => 'required|exists:temporadas,id',
                'firma_cargo' => 'required|exists:junta_directiva,id',
                'vb_presidente' => 'nullable|boolean',
                'fecha_envio' => 'nullable|date'
            ]);

            if ($validator->fails()) {
                return $this->sendError('Error de validación', [$validator->errors()], 422);
            }

            // Generar número de convocatoria automático
            $ultimaConvocatoria = CorrespondenciaJunta::where('fk_temporadas', $request->fk_temporadas)->max('convocatoria');
            $numeroConvocatoria = ($ultimaConvocatoria ?? 0) + 1;

            $convocatoria = CorrespondenciaJunta::create([
                'convocatoria' => $numeroConvocatoria,
                'fecha' => now(),
                'fecha_junta' => $request->fecha_junta,
                'hora1' => $request->hora1,
                'hora2' => $request->hora2,
                'lugar' => $request->lugar,
                'asunto' => $request->asunto,
                'texto' => $request->texto,
                'firma_cargo' => $request->firma_cargo,
                'vb_presidente' => $request->vb_presidente ?? 0,
                'fecha_envio' => $request->fecha_envio ?? now()->toDateString(),
                'estado' => 0,
                'pdfgenerado' => 0,
                'fk_temporadas' => $request->fk_temporadas
            ]);

            $convocatoria->load(['temporada', 'cargoFirmante']);

            return $this->sendResponse($convocatoria, 'Convocatoria creada correctamente', 201);
        } catch (\Exception $e) {
            return $this->sendError('Error al crear convocatoria', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Actualizar convocatoria
     * PUT /api/convocatorias/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $convocatoria = CorrespondenciaJunta::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'fecha_junta' => 'sometimes|required|date',
                'hora1' => 'sometimes|required',
                'hora2' => 'sometimes|required',
                'lugar' => 'sometimes|required|string|max:150',
                'asunto' => 'sometimes|required|string|max:250',
                'texto' => 'sometimes|required',
                'firma_cargo' => 'sometimes|required|exists:junta_directiva,id',
                'vb_presidente' => 'nullable|boolean',
                'fecha_envio' => 'nullable|date',
                'estado' => 'nullable|boolean',
                'pdfgenerado' => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                return $this->sendError('Error de validación', [$validator->errors()], 422);
            }

            $convocatoria->update($request->all());
            $convocatoria->load(['temporada', 'cargoFirmante']);

            return $this->sendResponse($convocatoria, 'Convocatoria actualizada correctamente');
        } catch (\Exception $e) {
            return $this->sendError('Error al actualizar convocatoria', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Eliminar convocatoria
     * DELETE /api/convocatorias/{id}
    */
    public function destroy($id){
        try {
            $convocatoria = CorrespondenciaJunta::findOrFail($id);

            // Verificar si tiene correspondencias vinculadas
            if ($convocatoria->correspondencias()->count() > 0) {
                return $this->sendError(
                    'No se puede eliminar la convocatoria porque tiene correspondencias vinculadas',
                    [],
                    409
                );
            }

            // Eliminar PDF si existe
            if ($convocatoria->pdfgenerado && Storage::exists('correspondencia/convocatorias/' . $id . '.pdf')) {
                Storage::delete('correspondencia/convocatorias/' . $id . '.pdf');
            }

            $convocatoria->delete();

            return $this->sendResponse(null, 'Convocatoria eliminada correctamente');
        } catch (\Exception $e) {
            return $this->sendError('Error al eliminar convocatoria', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Generar PDF de la convocatoria
     * POST /api/convocatorias/{id}/generar-pdf
    */
    public function generarPdf($id)
    {
        try {
            $convocatoria = CorrespondenciaJunta::with(['temporada', 'cargoFirmante'])->findOrFail($id);
            // Obtengo config para el logo
            $config = \App\Models\Configuracion::first();

            // Generar PDF con DomPDF
            $pdf = Pdf::loadView('convocatorias.pdf', compact('convocatoria', 'config'));

            // Guardar PDF en storage
            $filename = 'convocatoria_' . $id . '.pdf';
            $path = 'correspondencia/convocatorias/' . $filename;
            Storage::put($path, $pdf->output());

            // Actualizar estado
            $convocatoria->update(['pdfgenerado' => 1]);

            return $this->sendResponse([
                'convocatoria' => $convocatoria,
                'pdf_path' => $path,
                'pdf_url' => Storage::url($path)
            ], 'PDF generado correctamente');
        } catch (\Exception $e) {
            return $this->sendError('Error al generar PDF', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Dar/Quitar VºBº del presidente
     * POST /api/convocatorias/{id}/vobo
     */
    public function toggleVoBo($id)
    {
        try {
            $convocatoria = CorrespondenciaJunta::findOrFail($id);
            
            $convocatoria->update([
                'vb_presidente' => !$convocatoria->vb_presidente
            ]);

            return $this->sendResponse(
                $convocatoria,
                $convocatoria->vb_presidente ? 'VºBº otorgado' : 'VºBº retirado'
            );
        } catch (\Exception $e) {
            return $this->sendError('Error al actualizar VºBº', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Descargar PDF de la convocatoria
     * GET /api/convocatorias/{id}/descargar-pdf
     */
    public function descargarPdf($id)
    {
        try {
            $convocatoria = CorrespondenciaJunta::findOrFail($id);

            if (!$convocatoria->pdfgenerado) {
                return $this->sendError('El PDF aún no ha sido generado', [], 404);
            }

            $path = 'correspondencia/convocatorias/convocatoria_' . $id . '.pdf';

            if (!Storage::exists($path)) {
                return $this->sendError('Archivo PDF no encontrado', [], 404);
            }

            return Storage::download($path, 'convocatoria_' . $convocatoria->convocatoria . '.pdf');
        } catch (\Exception $e) {
            return $this->sendError('Error al descargar PDF', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Marcar como enviada
     * POST /api/convocatorias/{id}/marcar-enviada
     */
    public function marcarEnviada($id)
    {
        try {
            $convocatoria = CorrespondenciaJunta::findOrFail($id);
            
            $convocatoria->update([
                'estado' => 1,
                'fecha_envio' => now()->toDateString()
            ]);

            return $this->sendResponse($convocatoria, 'Convocatoria marcada como enviada');
        } catch (\Exception $e) {
            return $this->sendError('Error al marcar como enviada', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtener convocatorias con PDF generado (para selector de envíos)
     * GET /api/convocatorias/con-pdf
     */
    public function conPdf()
    {
        try {
            $convocatorias = CorrespondenciaJunta::with('temporada')
                ->conPdf()
                ->ordenarPorFecha('desc')
                ->get(['id', 'convocatoria', 'asunto', 'fecha_junta', 'fk_temporadas']);

            return $this->sendResponse($convocatorias, 'Convocatorias con PDF obtenidas');
        } catch (\Exception $e) {
            return $this->sendError('Error al obtener convocatorias', ['error' => $e->getMessage()], 500);
        }
    }
}
