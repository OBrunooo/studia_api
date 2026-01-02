<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\LoginRegister\LoginRegisterService;


class LoginRegisterController extends Controller
{

    protected $loginRegisterService;

    public function __construct (LoginRegisterService $loginRegisterService) {
        $this->loginRegisterService = $loginRegisterService;
    }

    public function login(Request $request) {
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
                "errors" => $validator->errors()
            ], 422);
        }

        return json_encode($this->loginRegisterService->verificaLogin($request->email, $request->senha));
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
        
        if ($validator->fails()) {
            return json_encode([
                "errors" => $validator->errors()
            ], 422);
        }      
        
        return json_encode($this->loginRegisterService->registrarUser($request->email, $request->senha, $request->nome));
    }

    public function logout(Request $request) {
        try {
            $request->user()->currentAccessToken()->delete();

            return json_encode([
                "status" => "success",
                "mensagem" => "Logout realizado com sucesso!"
            ]);
        } catch (\Throwable $th) {
            return json_encode([
                "status" => "error",
                "mensagem" => "Erro ao realizar o Logout"]);
        }
    }


}
