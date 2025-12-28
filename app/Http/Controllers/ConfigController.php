<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Configuracion;

use App\Models\Menu;
use DB;

class ConfigController extends BaseController
{

    public function index(): JsonResponse
    {
        try {
            $configuraciones = Configuracion::all();

            return $this->sendResponse(
                $configuraciones,
                'Configuraciones obtenidas exitosamente',
                200
            );
        } catch (\Exception $e) {
            return $this->sendError(
                'Error al obtener configuraciones',
                ['error' => $e->getMessage()],
                500
            );
        }
    }


    public function store(Request $request): JsonResponse
    {
        try {
            $configuracion = Configuracion::create($request->validated());

            $validated = $request->validate([
                'tipo' => 'required|string|max:100',
                'ejercicio' => 'required|string|max:100',
                'modificado' => 'sometimes|boolean'
            ]);

            // Por defecto modificado = 0
            $validated['modificado'] = true;

            DB::beginTransaction();

            // Crear configuración
            $configuracion = Configuracion::create($validated);
            
            // Actualizar menús
            $this->actualizarMenus($validated['tipo'], $validated['ejercicio']);
            
            DB::commit();


            return $this->sendResponse(
                $configuracion,
                'Configuración creada exitosamente',
                201
            );
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error al crear configuración: ' . $e->getMessage());

            return $this->sendError(
                'Error al crear configuración',
                ['error' => $e->getMessage()],
                500
            );
        }
    }


    public function show(int $id): JsonResponse
    {        
        try {            
            //\Log::info('Datos recibidos SHOW:');
            $configuracion = Configuracion::with('temporadaActiva')->first();
            
            
            if (!$configuracion) {
                return $this->sendError(
                    'Configuración no encontrada',
                    ['id' => 'No existe una configuración con este ID'],
                    404
                );
            }
            
            return $this->sendResponse(
                $configuracion,
                'Configuración obtenida exitosamente',
                200
            );
        } catch (\Exception $e) {
            return $this->sendError(
                'Error al obtener configuración',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {            
            

            $validator = Validator::make($request->all(), [
                'tipo' => 'sometimes|required|string|max:100',
                'ejercicio' => 'sometimes|required|string|max:100',
                'modificado' => 'required|boolean',
                'titulo' => 'nullable|string|max:250',
                'subtitulo' => 'nullable|string|max:250',
                'logo' => 'nullable|string|max:500',
                'navbar_color' => 'nullable|string|max:20',
                'gradient_from' => 'nullable|string|max:20',
                'gradient_to' => 'nullable|string|max:20',
            ]);

            // Marcar como modificado al actualizar
            // $validated['modificado'] = true;

            DB::beginTransaction();

            // Actualizar configuración
           $config = Configuracion::find($id);

           if (!$config) {
                return $this->sendError(
                    'Configuración no encontrada',
                    ['id' => 'No existe una configuración con este ID'],
                    404
                );
            }

            if ($request->has('tipo')) $config->tipo = $request->tipo;
            if ($request->has('ejercicio')) $config->ejercicio = $request->ejercicio;
            if ($request->has('modificado')) $config->modificado = $request->modificado;
            if ($request->has('titulo')) $config->titulo = $request->titulo;
            if ($request->has('subtitulo')) $config->subtitulo = $request->subtitulo;
            if ($request->has('logo')) $config->logo = $request->logo;
            if ($request->has('navbar_color')) $config->navbar_color = $request->navbar_color;
            if ($request->has('gradient_from')) $config->gradient_from = $request->gradient_from;
            if ($request->has('gradient_to')) $config->gradient_to = $request->gradient_to;
            
            // Actualizo menús tipo y ejercicio
            if ($request->has('tipo') || $request->has('ejercicio')) {
                $tipo = $request->tipo ?? $config->tipo;
                $ejercicio = $request->ejercicio ?? $config->ejercicio;                
                $this->actualizarMenus($tipo, $ejercicio);
            }

            $config->save();
            DB::commit();
            $config->load('temporadaActiva');
            
            return $this->sendResponse(
                $config,
                'Configuración actualizada correctamente',
                200
            );
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error al actualizar configuración: ' . $e->getMessage());

            return $this->sendError(
                'Error al actualizar configuración',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Actualizar solo configuración visual
    */
    public function updateVisual(Request $request, $id)
    {
        try {
            // Validaciones
            $validator = Validator::make($request->all(), [
                'titulo' => 'nullable|string|max:250',
                'subtitulo' => 'nullable|string|max:250',
                'logo' => 'nullable|string|max:500',
                'navbar_color' => 'nullable|string|max:20',
                'gradient_from' => 'nullable|string|max:20',
                'gradient_to' => 'nullable|string|max:20',
            ]);

            if ($validator->fails()) {
                return $this->sendError(
                    'Configuración no encontrada',
                    ['id' => 'No existe una configuración con este ID'],
                    422
                );
            }

            // Obtener configuración
            $config = Configuracion::find($id);

            if (!$config) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró la configuración'
                ], 404);
            }

            // Actualizar solo campos visuales
            if ($request->has('titulo')) $config->titulo = $request->titulo;
            if ($request->has('subtitulo')) $config->subtitulo = $request->subtitulo;
            if ($request->has('logo')) $config->logo = $request->logo;
            if ($request->has('navbar_color')) $config->navbar_color = $request->navbar_color;
            if ($request->has('gradient_from')) $config->gradient_from = $request->gradient_from;
            if ($request->has('gradient_to')) $config->gradient_to = $request->gradient_to;
            
            $config->save();

            return $this->sendResponse(
                $config,
                'Configuración visual actualizada correctamente',
                200
            );

        } catch (\Exception $e) {
            return $this->sendError(
                'Error al actualizar configuración visual',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    public function uploadLogo(Request $request)
    {
        try {
            // Validar archivo
            $validator = Validator::make($request->all(), [
                'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048', // Max 2MB
            ]);

            if ($validator->fails()) {
                return $this->sendError(
                    'Error de validación',
                    [$validator->errors()],
                    422
                );
            }

            // Eliminar logo anterior si existe
            $config = Configuracion::first();
            if ($config && $config->logo) {
                // Extraer nombre del archivo de la URL
                $oldLogoPath = str_replace(url('storage/'), '', $config->logo);
                if (Storage::disk('public')->exists($oldLogoPath)) {
                    Storage::disk('public')->delete($oldLogoPath);
                }
            }

            // Guardar nuevo logo
            $file = $request->file('logo');
            $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('logos', $filename, 'public');

            // URL completa del logo
            $logoUrl = url('storage/' . $path);

            // Actualizar en BD
            if ($config) {
                $config->logo = $logoUrl;
                $config->save();
            }

            return $this->sendResponse(
                ['logo' => $logoUrl, 'path' => $path ],
                'Logo subido correctamente',
                200
            );


        } catch (\Exception $e) {
            return $this->sendError(
                'Error al subir el logo',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $configuracion = Configuracion::find($id);

            if (!$configuracion) {
                return $this->sendError(
                    'Configuración no encontrada',
                    ['id' => 'No existe una configuración con este ID'],
                    404
                );
            }

            $configuracion->delete();

            return $this->sendResponse(
                null,
                'Configuración eliminada exitosamente',
                200
            );
        } catch (\Exception $e) {
            return $this->sendError(
                'Error al eliminar configuración',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    public function activa(): JsonResponse
    {
        try {
            // \Log::info('Datos recibidos ACTIVA');
            $configuracion = Configuracion::first();
            //$temporada = Temporada::where('activa', true)->first();
            if (!$configuracion) {
                return $this->sendError(
                    'No hay configuración disponible',
                    ['mensaje' => 'Debes crear una configuración primero'],
                    404
                );
            }            

            return $this->sendResponse(
                $configuracion,
                'Configuración activa obtenida exitosamente',
                200
            );
        } catch (\Exception $e) {
            return $this->sendError(
                'Error al obtener configuración activa',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Eliminar logo
     * DELETE /api/configuracion/delete-logo
     */
    public function deleteLogo()
    {
        try {
            $config = Configuracion::first();

            if (!$config || !$config->logo) {
                return $this->sendError(
                    'No hay logo para eliminar',
                    [],
                    404
                );
            }

            // Eliminar archivo del storage
            $logoPath = str_replace(url('storage/'), '', $config->logo);
            if (Storage::disk('public')->exists($logoPath)) {
                Storage::disk('public')->delete($logoPath);
            }

            // Actualizar BD (logo vacío)
            $config->logo = '';
            $config->save();

            return $this->sendResponse( null, 'Logo eliminado correctamente', 200);
        } catch (\Exception $e) {
            return $this->sendError(
                'Error al eliminar el logo',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Método privado para actualizar los labels de los menús
    */
    private function actualizarMenus($tipo, $ejercicio)
    {
        // Actualizar menú con ID 2 (Tipo)
        $menuTipo = Menu::find(2);
        if ($menuTipo) {
            $menuTipo->label = $tipo;
            $menuTipo->save();
            \Log::info('Menú ID 2 actualizado a: ' . $tipo);
        } else {
            \Log::warning('No se encontró el menú con ID 2');
        }

        // Actualizar menú con ID 5 (Ejercicio)
        $menuEjercicio = Menu::find(5);
        if ($menuEjercicio) {
            $menuEjercicio->label = $ejercicio;
            $menuEjercicio->save();
            \Log::info('Menú ID 5 actualizado a: ' . $ejercicio);
        } else {
            \Log::warning('No se encontró el menú con ID 5');
        }
    }
}
