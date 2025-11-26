<?php

namespace App\Http\Controllers;

use App\Models\Contacto;
use Illuminate\Http\Request;

class ContactoController extends BaseController
{
    /**
     * Listar todos los contactos/proveedores
     */
    public function index(Request $request)
    {
        try{
        
            $query = Contacto::with(['municipio', 'provinciaRelacion', 'paisRelacion']);
            
            // Buscar por nombre/CIF
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nom_emp', 'like', "%{$search}%")
                    ->orWhere('dni_cif', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
                });
            }
            
            $contactos = $query->orderBy('nom_emp')->get();

            return $this->sendResponse($contactos, 'Contactos/Proveedores obtenidos correctamente', 200);

        } catch(\Exception $e) {
             \Log::error('Error al listar los contactos' . $e->getMessage());
            return $this->sendError( 'Error al listar los contactos', 
            ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Mostrar un contacto
     */
    public function show($id)
    {
        try{
            $contacto = Contacto::with(['municipio', 'provinciaRelacion', 'paisRelacion'])->findOrFail($id);

            return $this->sendResponse($contacto, 'Contacto obtenido correctamente', 200);
        } catch(\Exception $e) {
             \Log::error('Error al obtener el contacto' . $e->getMessage());
            return $this->sendError( 'Error al obtener el contacto', 
            ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Crear contacto
     */
    public function store(Request $request)
    {
        try{
            $validated = $request->validate([
                'nom_emp' => 'required|string|max:150',
                'dni_cif' => 'required|string|max:9',
                'telefono' => 'required|integer',
                'fax' => 'nullable|integer',
                'email' => 'required|email|max:100',
                'direccion' => 'required|string|max:100',
                'cp' => 'required|string|max:5',
                'poblacion' => 'required|exists:socios_municipios,id',
                'provincia' => 'required|exists:socios_provincias,id',
                'pais' => 'required|exists:socios_nacionalidad,id',
                'contacto' => 'nullable|string|max:100',
                'IBAN' => 'nullable|string|max:24',
                'BIC' => 'nullable|string|max:11'
            ]);

            $contacto = Contacto::create($validated);

            return $this->sendResponse($contacto, 'Contacto creado correctamente', 201);
            

        } catch(\Exception $e) {
             \Log::error('Error al crear el contacto' . $e->getMessage());
            return $this->sendError( 'Error al crear el contacto', 
            ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Actualizar contacto
     */
    public function update(Request $request, $id)
    {
        try{
        
            $contacto = Contacto::findOrFail($id);
            
            $validated = $request->validate([
                'nom_emp' => 'sometimes|required|string|max:150',
                'dni_cif' => 'sometimes|required|string|max:9',
                'telefono' => 'sometimes|required|integer',
                'fax' => 'nullable|integer',
                'email' => 'sometimes|required|email|max:100',
                'direccion' => 'sometimes|required|string|max:100',
                'cp' => 'sometimes|required|string|max:5',
                'poblacion' => 'sometimes|required|exists:socios_municipios,id',
                'provincia' => 'sometimes|required|exists:socios_provincias,id',
                'pais' => 'sometimes|required|exists:socios_nacionalidad,id',
                'contacto' => 'nullable|string|max:100',
                'IBAN' => 'nullable|string|max:24',
                'BIC' => 'nullable|string|max:11'
            ]);

            $contacto->update($validated);

            return $this->sendResponse($contacto->fresh(['municipio', 'provinciaRelacion', 'paisRelacion']), 'Contacto actualizado correctamente', 200);

        } catch(\Exception $e) {
             \Log::error('Error al actualizar el contacto' . $e->getMessage());
            return $this->sendError( 'Error al actualizar el contacto', 
            ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Eliminar contacto
     */
    public function destroy($id)
    {
        try{
            $contacto = Contacto::findOrFail($id);
            $contacto->delete();            
            
            return $this->sendResponse(NULL, 'Contacto eliminado correctamente', 200);

        } catch(\Exception $e) {
             \Log::error('Error al eliminar el contacto' . $e->getMessage());
            return $this->sendError( 'Error al eliminar el contacto', 
            ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()], 500);
        }
    }
}
