<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Penya;

class PenyaController extends BaseController{

    /**
     * Mostrar datos de la peña simples
     */
    public function show()
    {
        try{
            $penya = Penya::getInstance();

            if (!$penya) {
                return $this->sendError(
                    'La peña no existe',
                    ['id' => 'No existe datos'],
                    404
                );
            }
            return $this->sendResponse($penya[0], 'Datos obtenidos correctamente.', 200);

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
     * Mostrar datos de la peña simples
     */
    public function showDatosBanco()
    {
        try{
            $penya = Penya::getInstanceBank();

            if (!$penya) {
                return $this->sendError(
                    'La peña no existe',
                    ['penya' => 'No existe datos'],
                    404
                );
            }
            return $this->sendResponse($penya[0], 'Datos banco obtenidos correctamente.', 200);

        } catch(\Exception $e) {
             \Log::error('Error al obtener los datos bancarios de la Peña: ' . $e->getMessage());
            return $this->sendError(
                'Error al obtener los datos bancarios de la Peña',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }                
    }

    /**
     * Actualizar datos de la peña
     */
    public function updateDatos(Request $request)
    {
        try {

            $penya = Penya::first();
        
            $validator = Validator::make($request->all(), [
                'cif' => 'sometimes|required|string|max:9',
                'nombre' => 'sometimes|required|string|max:100',
                'telefono' => 'sometimes|required|string|max:9',
                'direccion' => 'sometimes|required|string|max:100',
                'CP' => 'sometimes|required|string|max:5',
                'localidad' => 'sometimes|required|string|max:50',
                'provincia' => 'sometimes|required|string|max:50',
                'email' => 'sometimes|required|email|max:100',
                'pwd_email' => 'nullable|string|max:100',                
                'sede_social' => 'sometimes|required|string|max:50',
                'direccion_sede' => 'sometimes|required|string|max:100',
                'tel_sede' => 'sometimes|required|string|max:9'
            ]);

            if ($validator->fails()) {
                return $this->sendError(
                    'Error de validación',
                    [$validator->errors()],
                    422
                );
            }

            //$penya->update($validator);

            $penya->cif = $request->cif;
            $penya->nombre = $request->nombre;
            $penya->telefono = $request->telefono;
            $penya->direccion = $request->direccion;
            $penya->CP = $request->CP;
            $penya->localidad = $request->localidad;
            $penya->provincia = $request->provincia;
            $penya->email = $request->email;            
            $penya->sede_social = $request->sede_social;
            $penya->direccion_sede = $request->direccion_sede;
            $penya->tel_sede = $request->tel_sede;
            
            // Solo actualizamos la contraseña si se envia
            if ($request->filled('pwd_banco')) {
                $penya->pwd_email = $request->pwd_email;
            }

            $penya->save();

            return $this->sendResponse($penya, 'Datos actualizados correctamente.', 200);

        } catch(\Exception $e) {
             \Log::error('Error al actualizar datos Peña: ' . $e->getMessage());
            return $this->sendError(
                'Error al actualizar datos peña',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Actualizar datos bancarios de la peña
     * PUT /api/penya/datos-bancarios
     */
    public function updateDatosBancarios(Request $request)
    {
        try {
            // Validaciones
            $validator = Validator::make($request->all(), [
                'user_banco' => 'sometimes|required|string|max:25',
                'pwd_banco' => 'nullable|string|max:255',
                'tarjeta_claves' => 'nullable|string|max:9',
                'iban' => 'sometimes|required|string|max:50',
                'digitos_control' => 'sometimes|required|string|max:4',
                'sufijo' => 'sometimes|required|string|max:3',
                'bic' => 'sometimes|required|string|max:11'
            ]);

            if ($validator->fails()) {
                return $this->sendError(
                    'Error de validación',
                    [$validator->errors()],
                    422
                );
            }

            // Validar IBAN con Módulo 97
            $ibanLimpio = str_replace(' ', '', $request->iban);
            if (!$this->validarIbanModulo97($ibanLimpio)) {
                return $this->sendError(
                    'El IBAN no es válido según Módulo 97',
                    ['iban' => ['El IBAN no es válido']],
                    422
                );
            }

            // Obtener la única instancia
            $penya = Penya::first();

            // Actualizar solo campos bancarios
            $penya->user_banco = $request->user_banco;            
            $penya->tarjeta_claves = $request->tarjeta_claves;
            $penya->iban = $ibanLimpio;
            $penya->digitos_control = $request->digitos_control;
            $penya->sufijo = $request->sufijo;
            $penya->bic = $request->bic;

            // Solo actualizamos la contraseña si se envia
            if ($request->filled('pwd_banco')) {
                $penya->pwd_banco = $request->pwd_banco;
            }

            $penya->save();
            return $this->sendResponse($penya->fresh(), 'Datos bancarios actualizados correctamente', 200);

        } catch (\Exception $e) {
            \Log::error('Error al actualizar datos bancarios Peña: ' . $e->getMessage());
            return $this->sendError(
                'Error al actualizar datos bancarios',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Validar IBAN con algoritmo Módulo 97
     */
    private function validarIbanModulo97(string $iban): bool
    {
        if (empty($iban) || strlen($iban) < 15) {
            return false;
        }

        // Mover los 4 primeros caracteres al final
        $ibanReordenado = substr($iban, 4) . substr($iban, 0, 4);

        // Convertir letras a números (A=10, B=11, ..., Z=35)
        $ibanNumerico = '';
        for ($i = 0; $i < strlen($ibanReordenado); $i++) {
            $char = $ibanReordenado[$i];
            if (ctype_alpha($char)) {
                $ibanNumerico .= (ord(strtoupper($char)) - 55);
            } else {
                $ibanNumerico .= $char;
            }
        }

        // Calcular módulo 97
        return bcmod($ibanNumerico, '97') === '1';
    }
}
