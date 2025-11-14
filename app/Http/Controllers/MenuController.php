<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;

class MenuController extends Controller
{    
    /**
     * Display a listing of the resource.
     * Obtiene todos los menús para el administrador
     */
    public function index()
    {
        $menus = Menu::with('children')->principales()->orderBy('order')->get();
        
        return response()->json([
            'data' => $menus
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:100',
            'icon' => 'nullable|string|max:50',
            'route' => 'nullable|string|max:150',
            'order' => 'required|integer',
            'parent_id' => 'nullable|exists:menus,id',
            'roles' => 'nullable|array',
            'roles.*' => 'integer|in:1,2',
            'activo' => 'boolean'
        ]);

        $menu = Menu::create($validated);

        return response()->json([
            'message' => 'Menú creado correctamente',
            'data' => $menu
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $menu = Menu::findOrFail($id);

        $validated = $request->validate([
            'label' => 'sometimes|required|string|max:100',
            'icon' => 'nullable|string|max:50',
            'route' => 'nullable|string|max:150',
            'order' => 'sometimes|required|integer',
            'parent_id' => 'nullable|exists:menus,id',
            'roles' => 'nullable|array',
            'roles.*' => 'integer|in:1,2',
            'activo' => 'sometimes|boolean'
        ]);

        $menu->update($validated);

        return response()->json([
            'message' => 'Menú actualizado correctamente',
            'data' => $menu
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $menu = Menu::findOrFail($id);
        $menu->delete();

        return response()->json([
            'message' => 'Menú eliminado correctamente'
        ], 200);
    }

    /**
     * Obtener el menú según el rol del usuario autenticado
     */
    public function getMenu(Request $request)
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Usuario no autenticado'
            ], 401);
        }

        $rolUsuario = $user->perfil;

        // Obtener menús principales
        $menus = Menu::activos()
            ->principales()
            ->porRol($rolUsuario)
            ->orderBy('order')
            ->get();

        // Construir estructura con children recursivos
        $menuItems = $menus->map(function ($menu) use ($rolUsuario) {
            return $this->buildMenuItem($menu, $rolUsuario);
        });

        return response()->json([
            'menuItems' => $menuItems
        ], 200);
    }

    /**
     * Construir item del menú recursivamente
     */
    private function buildMenuItem($menu, $rolUsuario)
    {
        $item = [
            'id' => $menu->id,
            'label' => $menu->label,
            'order' => $menu->order
        ];

        // Añadir icono si existe
        if ($menu->icon) {
            $item['icon'] = $menu->icon;
        }

        // Añadir ruta si existe
        if ($menu->route) {
            $item['route'] = $menu->route;
        }

        // Obtener hijos
        $children = Menu::activos()
            ->where('parent_id', $menu->id)
            ->porRol($rolUsuario)
            ->orderBy('order')
            ->get();

        // Si tiene hijos, añadirlos recursivamente
        if ($children->isNotEmpty()) {
            $item['children'] = $children->map(function ($child) use ($rolUsuario) {
                return $this->buildMenuItem($child, $rolUsuario);
            })->toArray();
        }

        return $item;
    }
}
