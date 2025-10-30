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
    const MI_KEY_TOKEN = 'Api-BackendTFM_2025';
    
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

        $token = $user->createToken(self::MI_KEY_TOKEN)->plainTextToken;
        $expiresAt = now()->addHour();
        $user->tokens()->latest()->first()->update([
            'expires_at' => $expiresAt,
        ]);
        $user['token'] = $token;
        $user['expires_at'] = $expiresAt->toDateTimeString();
        return $this->sendResponseLogin($user, $token, 'Inicio de sesión exitoso.', 200);
    }

    public function logout22()
    {
        Auth::user()->tokens->each(function ($token) {
            $token->forceDelete();
        });
        $response = [];
        return $this->sendResponse($response, 'Logout exitoso.', 200);
    }

    public function logout(Request $request){
        $user = Auth::user();
        // $user->tokens()->where('id', $tokenId)->delete();
        $request->user()->currentAccessToken()->delete();
        // $user->tokens()->delete();
        return $this->sendResponse([], 'Logout exitoso.', 200);
        // return response()->json(
        //     [
        //         'status'=> true,
        //         'message' => 'Logout exitoso'
        //     ]
        // , 200);
    }

}
