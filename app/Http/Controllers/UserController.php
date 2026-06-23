<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NotificacaoUsuario;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{

    public function userInfo(Request $request) {
        try {
            return response()->json([
                "user" => $request->user()
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => "Erro ao buscar informações do usuário",
                "error" => $th->getMessage()
            ], 500);
        }
    }

    public function updateAvatarUser(Request $request) {
        $request->validate([
            "avatar" => "required|integer"
        ], [
            "avatar.required" => "O campo avatar é obrigatório",
            "avatar.integer" => "O campo avatar deve ser um número inteiro"
        ]);
        try {
            $avatar = $request->input("avatar");
            $user = $request->user();
            $user->avatar_id = (int) $avatar;
            $user->save();
            return response()->json([
                "success" => true,
                "message" => "Avatar atualizado com sucesso"
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "success" => false,
                "message" => "Erro ao atualizar avatar do usuário",
                "error" => $th->getMessage()
            ], 500);
        }
    }

    public function updateNameUser(Request $request) {
        $request->validate([
            "name" => "required|string|min:3|max:255"
        ], [
            "name.required" => "O campo nome é obrigatório",
            "name.string" => "O campo nome deve ser uma string",
            "name.min" => "O campo nome deve ter no mínimo 3 caracteres",
            "name.max" => "O campo nome deve ter no máximo 255 caracteres"
        ]);
        try {
            $name = $request->input("name");
            $user = $request->user();
            $user->name = $name;
            $user->save();
            return response()->json([
                "success" => true,
                "message" => "Nome atualizado com sucesso"
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "success" => false,
                "message" => "Erro ao atualizar nome do usuário",
                "error" => $th->getMessage()
            ], 500);
        }
    }

    public function listarNotificacoesUser() {
        try {
            $notificacoes = NotificacaoUsuario::where("user_id", Auth::user()->id)->get();
            return response()->json([
                "notificacoes" => $notificacoes
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "success" => false,
                "message" => "Erro ao listar notificações do usuário",  
                "error" => $th->getMessage()
            ], 500);
        }
    }
}