<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;



class AuthController extends BaseController
{
    // const MI_KEY_TOKEN = 'Api-BackendTFM_2025';
    private $key_token = 'Api-BackendTFM_2025';
    
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return $this->sendError('Credenciales incorrectas.', ['El email o la contraseña son incorrectos.'], 401);
        }

        $user = Auth::user();
        if ($user->estado === 0) {
            return $this->sendError('Acceso denegado.', ['Su cuenta no está activa. Contacte al administrador.'], 403);
        }

        $token = $user->createToken($this->key_token)->plainTextToken;
        $expiresAt = now()->addHours(3);
        $user->tokens()->latest()->first()->update([
            'expires_at' => $expiresAt,
        ]);

        $expires = $expiresAt->toDateTimeString();
        return $this->sendResponseLogin($user, $token, $expires, 'Inicio de sesión exitoso.', 200);
    }

    /**
     * Refrescar token
     */
    public function refresh(Request $request)
    {
        // Elimino ek token actual
        $request->user()->currentAccessToken()->delete();
        
        // Creamos nuevo token
        $token = $request->user()->createToken('auth_token')->plainTextToken;
        return $this->sendResponse(['access_token' => $token], 'Token actualizado.', 200);
    }

    public function logout(Request $request){

        \Log::info('Datos recibidos LOGOUT:', $request->all());
        try{
            $user = $request->user();
            // $user = Auth::user();

            // Revoke all tokens...
            // $user->tokens()->delete();

            // Revocar token especifico
            // $user->tokens()->where('id', $tokenId)->delete();

            // Revocar token current user
            $user->currentAccessToken()->delete();
            return $this->sendResponse([], 'Logout exitoso desde el servidor.', 200);
        } catch (\Exception $e) {
             \Log::error('Error al logout: ' . $e->getMessage());
            return $this->sendError(
                'Error Logout',
                ['code', $e->getCode(), 'file', $e->getFile(), 'line', $e->getLine(), 'message' => $e->getMessage()],
                500
            );
        }


    }

}
