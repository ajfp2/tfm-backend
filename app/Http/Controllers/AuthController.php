<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;


class AuthController extends Controller
{
    
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Credenciales incorrectas'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('token-api')->plainTextToken;

        return response()->json(['token' => $token, 'user'=> $user], 200);
    }

    public function logout(Request $request){
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
