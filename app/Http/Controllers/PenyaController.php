<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penya;

class PenyaController extends BaseController
{
    /**
     * Obtener datos de la peña
     */
    public function index()
    {
        try{
            $penya = Penya::first();
            
            return $this->sendResponse($penya, 'Datos obtenidos correctamente.', 200);

        } catch(\Exception $e) {
             \Log::error('Error al obtener listado de Peñas: ' . $e->getMessage());
            return $this->sendError(
                'Error al obtener listado datos peña',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Mostrar datos de la peña
     */
    public function show($id)
    {
        try{
            $penya = Penya::findOrFail($id);

            if (!$penya) {
                return $this->sendError(
                    'La peña con ese id no existe:'.$id,
                    ['id' => 'No existe datos con este ID '.$id],
                    404
                );
            }
            return $this->sendResponse($penya, 'Datos obtenidos correctamente.', 200);

        } catch(\Exception $e) {
             \Log::error('Error al obtener la Peña: ' . $e->getMessage());
            return $this->sendError(
                'Error al obtener datos peña',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }                
    }

    /**
     * Actualizar datos de la peña
     */
    public function update(Request $request, $id)
    {
        try {

            $penya = Penya::findOrFail($id);
        
            $validated = $request->validate([
                'cif' => 'sometimes|required|string|max:9',
                'nombre' => 'sometimes|required|string|max:100',
                'telefono' => 'sometimes|required|string|max:9',
                'direccion' => 'sometimes|required|string|max:100',
                'CP' => 'sometimes|required|string|max:5',
                'localidad' => 'sometimes|required|string|max:50',
                'provincia' => 'sometimes|required|string|max:50',
                'email' => 'sometimes|required|email|max:100',
                'pwd_email' => 'nullable|string|max:100',
                'user_banco' => 'sometimes|required|string|max:25',
                'pwd_banco' => 'nullable|string|max:50',
                'tarjeta_claves' => 'nullable|string|max:9',
                'iban' => 'sometimes|required|string|max:50',
                'digitos_control' => 'sometimes|required|string|max:4',
                'sufijo' => 'sometimes|required|string|max:3',
                'sede_social' => 'sometimes|required|string|max:50',
                'direccion_sede' => 'sometimes|required|string|max:100',
                'tel_sede' => 'sometimes|required|string|max:9',
                'bic' => 'sometimes|required|string|max:11'
            ]);

            $penya->update($validated);

            return $this->sendResponse($penya->fresh(), 'Datos actualizados correctamente.', 200);

        } catch(\Exception $e) {
             \Log::error('Error al actualizar datos Peña: ' . $e->getMessage());
            return $this->sendError(
                'Error al actualizar datos peña',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        } 


    }
}
