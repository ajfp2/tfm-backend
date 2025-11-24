<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;

class MenuController extends BaseController
{    
    /**
     * Obtiene todos los menús para el administrador
     */
    public function index()
    {
        $menus = Menu::with('children')->principales()->orderBy('order')->get();
        return $this->sendResponse($menus, 'Menúss obtenidos correctamente.', 200);
    }


    /**
     * Creamos un menú.
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
        return $this->sendResponse($menu, 'Menú creado correctamente', 201);

    }

    /**
     * Mostramos una opcion de mneu.
     */
    public function show(string $id)
    {
        //
    }


    /**
     * Actualizamos.
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

        return $this->sendResponse($menu, 'Menú actualizado correctamente', 200);
    }

    /**
     * Eliminamos menu.
     */
    public function destroy(string $id)
    {
        $menu = Menu::findOrFail($id);
        $menu->delete();

        return $this->sendResponse($menu, 'Menú eliminado correctamente', 200);
    }

    /**
     * Obtener el menú según el rol del usuario autenticado
     */
    public function getMenu(Request $request)
    {

        try{
            $user = auth()->user();
            // \Log::info('Datos recibidos en menu-User:' . $user->nombre);
            if (!$user) {
                return $this->sendError('Usuario no autenticado', [], 401);
            }
            // return $this->sendResponse($user, 'Menú completo correctamente', 200);

            $rolUsuario = $user->perfil;

            // Obtener los menús principales
            $menus = Menu::activos()
                ->principales()
                ->porRol($rolUsuario)
                ->orderBy('order')
                ->get();

            // Construir la estructura con los hijos recursivos
            $menuItems = $menus->map(function ($menu) use ($rolUsuario) {                
                return $this->buildMenuItem($menu, $rolUsuario);
            });
           
            return $this->sendResponse($menuItems, 'Menú completo correctamente', 200);        
        } catch(\Exception $e) {
             \Log::error('Error Menú: ' . $e->getMessage());
            return $this->sendError(
                'Error al obtener listado datos peña',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }
        
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

        // Añadimos icono
        if ($menu->icon) {
            $item['icon'] = $menu->icon;
        }

        // Añadimos ruta
        if ($menu->route) {
            $item['route'] = $menu->route;
        }

        // Obtenemos hijos
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
