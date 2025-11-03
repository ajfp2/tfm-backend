<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Socio;

class SocioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Socio::all(), 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
        $socio = Socio::create($request->all());
        return response()->json($socio, 201);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        
        $socio = Socio::find($id);
        if (!$socio) {
            return response()->json(['error' => 'Socio no encontrado'], 404);
        }
        return response()->json($socio, 200);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        
        $socio = Socio::find($id);
        if (!$socio) {
            return response()->json(['error' => 'Socio no encontrado'], 404);
        }
        $socio->update($request->all());
        return response()->json($socio, 200);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        
        $socio = Socio::find($id);
        if (!$socio) {
            return response()->json(['error' => 'Socio no encontrado'], 404);
        }
        $socio->delete();
        return response()->json(null, 204);

    }
}
