<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


use App\Models\SocioNacionalidad;
use App\Models\SocioProvincia;
use App\Models\SocioMunicipio;
use App\Models\SocioFormaPago;

class SocioAuxiliarController extends BaseController
{
    /**
     * Obtener todas las nacionalidades
     */
    public function nacionalidades()
    {
        $nacionalidades = SocioNacionalidad::orderBy('pais')->get();

        return $this->sendResponse($nacionalidades, 'Nacionalidades obtenidas correctamente', 200);

    }

    /**
     * Obtener todas las provincias
     */
    public function provincias()
    {
        $provincias = SocioProvincia::orderBy('provincia')->get();

        return $this->sendResponse($provincias, 'Provincias obtenidas correctamente', 200);
    }

    /**
     * Obtener todos los municipios
     */
    public function municipios(Request $request)
    {
        $query = SocioMunicipio::query();
        
        // Filtrar por provincia si se envía
        if ($request->has('provincia_id')) {
            $query->where('provincia', $request->provincia_id);
        }
        
        $municipios = $query->orderBy('municipio')->get();

        return $this->sendResponse($municipios, 'Municipios obtenidos correctamente', 200);
        
    }

    /**
     * Obtener todas las formas de pago
     */
    public function formaspago()
    {
        $formasPago = SocioFormaPago::all();
        
        return $this->sendResponse($formasPago, 'Fromas de pago obtenidos correctamente', 200);
    }

}
