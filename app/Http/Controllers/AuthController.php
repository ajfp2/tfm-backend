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

    public function logout(Request $request){
        $user = Auth::user();
        // $user->tokens()->where('id', $tokenId)->delete();
        $request->user()->currentAccessToken()->delete();
        // $user->tokens()->delete();
        return $this->sendResponse([], 'Logout exitoso desde el servidor.', 200);
        // return response()->json(
        //     [
        //         'status'=> true,
        //         'message' => 'Logout exitoso'
        //     ]
        // , 200);
    }

}
