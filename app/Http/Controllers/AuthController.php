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

    public function login3(Request $request): JsonResponse
    {
        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return $this->sendError('Credenciales incorrectas.', ['El email o la contraseña son incorrectos.'], 401);
        }
        $user = Auth::user();
        if ($user->status !== 1) {
            return $this->sendError('Acceso denegado.', ['Su cuenta no está activa. Contacte al administrador.'], 403);
        }

        $fullToken = $user->createToken(self::MI_KEY_TOKEN)->plainTextToken;
        $token = explode('|', $fullToken)[1];
        $expiresAt = now()->addHour();
        $user->tokens()->latest()->first()->update([
            'expires_at' => $expiresAt,
        ]);
        $data = [
            'token' => $token,
            'expires_at' => $expiresAt->toDateTimeString(),
            'name' => $user->name,
            'email' => $user->email
        ];

        return $this->sendResponseLogin($data, $token, 'Inicio de sesión exitoso.', 200);
    }
    
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Credenciales incorrectas'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken(self::MI_KEY_TOKEN)->plainTextToken;
        $user['token'] = $token;
        return $this->sendResponseLogin($user, $token, 'Inicio de sesión exitoso.', 200);
    }

    public function logout()
    {
        Auth::user()->tokens->each(function ($token) {
            $token->forceDelete();
        });
        $response = [];
        return $this->sendResponse($response, 'Logout exitoso.', 200);
    }

    public function logout2(Request $request){
        $user = Auth::user();
        // $user->tokens()->where('id', $tokenId)->delete();
        $user->tokens()->delete();

        return response()->json(
            [
                'status'=> true,
                'message' => 'Logout exitoso'
            ]
        , 200);
    }

}
