<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Services\LogService;

class LoginRegisterController extends Controller
{
    public function login(Request $request) {
        try {
            $validator = Validator::make($request->all(),[
                "email" => "required|email",
                "senha" => "required"
            ],[
                "email.required" => "Email é obrigatório",
                "email.email" => "Digite um email válido",
                "senha.required" => "Senha é obrigatório"
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    "errors" => $validator->errors()
                ], 422);
            }

            if (!Auth::attempt([
                "email" => $request->input("email"), 
                "password" => $request->input("senha")])) {
                
                return response()->json([
                    "status" => "error",
                    "message" => "Credenciais inválidas"
                ], 401);
            }

            $user = Auth::user();
            $userInfo = [
                "id" => $user->id,
                "email" => $user->email,
                "name" => $user->name,
                "avatar" => $user->avatar_id
            ];
            $token = $user->createToken("flutter")->plainTextToken;
            LogService::info(action: "login", user: $user, message: "Login realizado com sucesso", data: [
                "ip" => $request->ip(),
                "user_id" => $user->id,
                "token" => $token
            ]);
            return response()->json([
                "user" => $userInfo,
                "token" => $token
            ], 200);     
        } catch (\Throwable $th) {
            LogService::error("login", null, $th);
            return response()->json([
                "status" => "error",
                "message" => "Erro ao realizar o login"
            ], 500);
        }
    }

    public function registrar(Request $request) {
        $validator = Validator::make($request->all(),[
            "email" => "required|email|unique:users,email",
            "senha" => "required|min:8",
            "nome" => "required|min:3",
        ],[
            "email.required" => "Email é obrigatório",
            "email.email" => "Digite um email válido",
            "email.unique" => "Este e-mail já está em uso",
            "senha.required" => "Senha é obrigatório",
            "senha.min" => "Senha deve conter no mínimo 8 caracteres",
            "nome.required" => "Nome é obrigatório",
            "nome.min" => "Nome deve conter no mínimo 3 caracteres"
        ]);
        
        try {
            if ($validator->fails()) {
                return response()->json([
                    "errors" => $validator->errors()
                ], 422);
            }
    
            $user = User::create([
                "email" => $request->input("email"),
                "password" => $request->input("senha"),
                "name" => $request->input("nome")
            ]);

            LogService::info(action: "registrar", user: $user, message: "Usuário registrado com sucesso", data: [
                "user_id" => $user->id,
                "email" => $user->email,
                "name" => $user->name
            ]);
    
            $token = $user->createToken("flutter")->plainTextToken;
    
            return response()->json([
                "user" => $user,
                "token" => $token
            ], 200);
        } catch (\Throwable $th) {
            LogService::error(action: "registrar", user: null, error: $th);
            return response()->json([
                "success" => false,
                "message" => "Ocorreu um erro para registrar usuário"
            ], 500);
        }

    }

    public function logout(Request $request) {
        try {
            $request->user()->currentAccessToken()->delete();
            LogService::info(action: "logout", user: $request->user(), message: "Logout realizado com sucesso", data: [
                "user_id" => $request->user()->id,
                "ip" => $request->ip()
            ]);
            return response()->json([
                "status" => "success",
                "mensagem" => "Logout realizado com sucesso!"
            ], 200);
        } catch (\Throwable $th) {
            LogService::error(action: "logout", user: $request->user(), error: $th);
            return response()->json([
                "status" => "error",
                "mensagem" => "Erro ao realizar o Logout"
            ], 500);
        }
    }
}
