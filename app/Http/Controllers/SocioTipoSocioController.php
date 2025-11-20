<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SocioTipoSocio;

class SocioTipoSocioController extends BaseController
{
    /**
     * Listar los tipos de socio
     */
    public function index()
    {
        try{

            $tipos = SocioTipoSocio::orderBy('id_tipo')->get();
            return $this->sendResponse($tipos, 'Tipos de socio obtenidos correctamente', 200);
            
        } catch(\Exception $e) {
             \Log::error('Error al obtener los tipos de socios' . $e->getMessage());
            return $this->sendError( 'Error al obtener los tipos de socios', 
            ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()], 500);
        }

    }

    /**
     * Mostrar un tipo de socio
     */
    public function show($id)
    {
        try{

            $tipo = SocioTipoSocio::findOrFail($id);
            return $this->sendResponse($tipo, 'Tipo de socio obtenido correctamente', 200);

        } catch(\Exception $e) {
             \Log::error('Error al mostrar el tipo de socio' . $e->getMessage());
            return $this->sendError( 'Error al mostrar el tipo de socio', 
            ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()], 500);
        }

    }

    /**
     * Crear tipo de socio
     */
    public function store(Request $request)
    {
        try{
            $validated = $request->validate([
                'tipo' => 'required|string|max:50',
                'descripcion' => 'required|string|max:500',
                'exentos_pago' => 'sometimes|boolean'
            ]);

            $tipo = SocioTipoSocio::create($validated);
            return $this->sendResponse($tipo, 'Tipo de socio creado correctamente', 201);

        } catch(\Exception $e) {
             \Log::error('Error al crear el tipo de socio' . $e->getMessage());
            return $this->sendError( 'Error al crear el tipo de socio', 
            ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Actualizar tipo de socio
     */
    public function update(Request $request, $id)
    {
        try{

            $tipo = SocioTipoSocio::findOrFail($id);
            
            $validated = $request->validate([
                'tipo' => 'sometimes|required|string|max:50',
                'descripcion' => 'sometimes|required|string|max:500',
                'exentos_pago' => 'sometimes|boolean'
            ]);

            $tipo->update($validated);

            return $this->sendResponse($tipo->fresh(), 'Tipo de socio actualizado correctamente', 200);
        } catch(\Exception $e) {
             \Log::error('Error al actualizar el tipo de socio' . $e->getMessage());
            return $this->sendError( 'Error al actualizar el tipo de socio', 
            ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()], 500);
        }

    }

    /**
     * Eliminar tipo de socio
     */
    public function destroy($id)
    {
        try{
            $tipo = SocioTipoSocio::findOrFail($id);
            
            // Verificar si tiene socios asignados
            if ($tipo->sociosAlta()->exists() || $tipo->sociosBaja()->exists()) {
                return $this->sendError( 'o se puede eliminar el tipo de socio porque tiene socios asignados', [], 409);
            }
            
            $tipo->delete();

            return $this->sendResponse(NULL, 'Tipo de socio eliminado correctamente', 200);
            
        } catch(\Exception $e) {
             \Log::error('Error al eliminar el tipo de socio' . $e->getMessage());
            return $this->sendError( 'Error al eliminar el tipo de socio', 
            ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()], 500);
        }
    }
}
