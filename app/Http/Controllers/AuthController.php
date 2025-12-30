<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class AuthController extends Controller
{
    public function verificaLogin(Request $request) {
        $validator = Validator::make($request->all(),[
            "email" => "required|email",
            "senha" => "required"
        ],[
            "email.required" => "Email é obrigatório",
            "email.email" => "Digite um email válido",
            "senha.required" => "Senha é obrigatório"
        ]);
        
        if ($validator->fails()) {
            return json_encode([
                "message" => $validator->errors()->first()
            ], 422);
        }
        if (!Auth::attempt([
            "email" => $request->email, 
            "password" => $request->senha])) {
            return json_encode([
                'message' => 'Credenciais inválidas'
            ]);
        }

        $user = Auth::user();

        $token = $user->createToken("flutter")->plainTextToken;
        

        return json_encode([
            "user" => $user,
            "token" => $token
        ]);
    }
}
