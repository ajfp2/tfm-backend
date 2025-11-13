<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class UserController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        // Para paginaciones pasaro por parametro Request y descomentar
        // $perPage = $request->get('per_page', 10); // 10 por defecto
        // $users = User::paginate($perPage);
        // return $this->sendResponse($users, 'Usuarios obtenidos correctamente.', 200);
        try{

            return $this->sendResponse(User::all(), 'Usuarios obtenidos correctamente.', 200);

        } catch (\Exception $e) {
            return $this->sendError(
                'Error al obtener listado usuarios',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }

        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store_logs(Request $request){
        try {
            // Debug: ver qué llega
            \Log::info('Datos recibidos:', $request->all());
            
            $validated = $request->validate([
                'nombre' => 'required|string|min:4|max:25',
                'apellidos' => 'required|string|min:4|max:50',
                'usuario' => 'required|string|min:4|max:25|unique:users',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:8',
                'telefono' => 'required|string|regex:/^[67][0-9]{8}$/',
                'perfil' => 'required|in:1,2',
                'foto' => 'nullable|image|max:5120'
            ]);

            $validated['estado'] = true;
            // Convertir perfil a integer
            $validated['perfil'] = (int) $validated['perfil'];
            
            // Hash password
            $validated['password'] = Hash::make($validated['password']);

            // Procesar foto
            if ($request->hasFile('foto')) {
                $path = $request->file('foto')->store('usuarios', 'public');
                $validated['foto'] = url('storage/' . $path);
            }

            $user = User::create($validated);
            
            return response()->json([
                'message' => 'Usuario creado correctamente',
                'data' => $user
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            \Log::error('Error al crear usuario: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al crear usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try{
            $validated = $request->validate([
                'nombre' => 'required|string|min:4|max:25',
                'apellidos' => 'required|string|min:4|max:50',
                'usuario' => 'required|string|min:4|max:25|unique:users',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:8',
                'telefono' => 'required|string|regex:/^[67][0-9]{8}$/',
                'perfil' => 'required|in:1,2',
                'foto' => 'nullable|image|max:5120'
            ]);

            // Ya no necesitas convertir perfil si aceptas string
            $validated['perfil'] = (int) $validated['perfil'];
            $validated['password'] = Hash::make($validated['password']);
            $validated['estado'] = true;

            if ($request->hasFile('foto')) {
                $path = $request->file('foto')->store('usuarios', 'public');
                $validated['foto'] = url('storage/' . $path);
            }

            $user = User::create($validated);
            
            return $this->sendResponse($user, 'Usuario creado correctamente.', 201);

        } catch (\Exception $e) {
             \Log::error('Error al crear usuario: ' . $e->getMessage());
            return $this->sendError(
                'Error al crear usuario',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try{
            $user = User::find($id);
            if (!$user) {
                return $this->sendError(
                    'Configuración no encontrada',
                    ['id' => 'No existe un usuario con este ID '.$id],
                    404
                );
            }

            return $this->sendResponse($user, 'Usuario obtenido correctamente.', 200);

        } catch (\Exception $e) {
             \Log::error('Error al obtener usuario: ' . $e->getMessage());
            return $this->sendError(
                'Error al obtener usuario',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try{
            $user = User::findOrFail($id);
            // ponemos el sometimes porque puede que el campo no este enviado
            $validated = $request->validate([
                'nombre' => 'sometimes|required|string|min:4|max:25',
                'apellidos' => 'sometimes|required|string|min:4|max:50',
                'usuario' => 'sometimes|required|string|min:4|max:25|unique:users,usuario,'.$id,// laravel comprueba si el usuario existe excepto en el de este id
                'email' => 'sometimes|required|email|unique:users,email,'.$id,
                'password' => 'nullable|min:8',
                'telefono' => 'sometimes|required|regex:/^[67][0-9]{8}$/',
                'perfil' => 'sometimes|required|integer|in:1,2',
                'foto' => 'nullable|image|max:5120'
            ]);

            if (isset($validated['perfil'])) {
                $validated['perfil'] = (int) $validated['perfil'];
            }

            // hacemos el hash si enviamos el password
            if (!empty($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
            }

            if ($request->hasFile('foto')) {
                // Elimino la foto anterior si existe
                if ($user->foto) {
                    Storage::disk('public')->delete(str_replace(asset('storage/'), '', $user->foto));
                }
                
                $path = $request->file('foto')->store('usuarios', 'public');
                $validated['foto'] = asset('storage/' . $path);
            }

            $user->update($validated);
            return $this->sendResponse($user, 'Usuario actualizado correctamente laravel.', 200);

        } catch (\Exception $e) {
            \Log::error('Error al actualizar usuario: ' . $e->getMessage());
            return $this->sendError(
                'Error al al actualizar usuario',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{
            $user = User::findOrFail($id);
        
            // No permitir eliminarme a mi mismo
            if (auth()->check() && auth()->id() == $id) {
                return $this->sendError('No puedes eliminarte a ti mismo', [], 403);            
            }
            
            // Eliminar foto si existe
            if ($user->foto) {
                $photoPath = str_replace(url('storage/'), '', $user->foto);
                Storage::disk('public')->delete($photoPath);
            }
            
            // Eliminamos el usuario
            $user->delete();
            return $this->sendResponse($user, 'Usuario eliminado correctamente.', 200);
        } catch (\Exception $e) {
            \Log::error('Error al eliminar usuario: ' . $e->getMessage());
            return $this->sendError(
                'Error al eliminar usuario',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                501
            );
        }
    }

    public function activar_user(Request $request, string $id): JsonResponse{
        try{
            // \Log::info('Datos recibidos Activa User:', $request->input('user'));
            
            $user = User::findOrFail($id);
            $user->estado = $request->input('user')['estado'];
            $user->save();

            return $this->sendResponse($user, 'Usuario Activado/Desactivado correctamente.', 200);
        }
        catch (\Exception $e) {
            \Log::error('Error al activar/desactivar usuario: ' . $e->getMessage());
            return $this->sendError(
                'Error al al activar/desactivar usuario',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }
        
    }
}
