<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PasswordResetController extends Controller {

    public function sendToken(Request $request) {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => "error",
                'mensagem' => 'Usuário não encontrado'
                ], 404);
        }

        $caracteres = "0123456789";
        $caracteres = str_shuffle($caracteres);
        $caracteres = substr($caracteres,0,6);


        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($caracteres),
                'created_at' => now(),
            ]
        );


        try {
            $user->sendPasswordResetToken($caracteres);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'mensagem' => 'Não foi possível enviar o e-mail. Tente novamente mais tarde.',
            ], 500);
        }

        return response()->json([
            "status" => "sucesso",
            'mensagem' => 'Token enviado para o email'
        ], 200);
    }

public function resetPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'token' => 'required|string|size:6',
        'senha' => 'required|min:8|confirmed',
    ], [
        "email.required" => "O campo email é obrigatório",
        "email.email" => "Digite um email válido",
        "token.required" => "O campo token é obrigatório",
        "token.string" => "Token inválido",
        "token.size" => "Token inválido",
        "senha.required" => "O campo senha é obrigatório",
        "senha.confirmed" => "A confirmação do campo de senha não corresponde",
        "senha.min" => "A senha precisa possuir no mínimo 8 caracteres" 
    ]);


    // busca token
    $passwordReset = DB::table('password_reset_tokens')
        ->where('email', $request->email)
        ->first();

    if (!$passwordReset) {
        return response()->json(['message' => 'Token inválido ou expirado'], 400);
    }

    // valida token (string)
    if (!Hash::check((string) $request->token, $passwordReset->token)) {
        return response()->json(['message' => 'Token inválido ou expirado'], 400);
    }
    if ($passwordReset->created_at < now()->subMinutes(20)) {
        return response()->json(['message' => 'Token inválido ou expirado'], 400);
    }
    // atualiza senha
    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json(['message' => 'Usuário não encontrado'], 404);
    }

    $user->password = $request->input("senha");
    $user->save();

    // invalida token
    DB::table('password_reset_tokens')
        ->where('email', $request->email)
        ->delete();

    return response()->json([
        'message' => 'Senha redefinida com sucesso'
    ], 200);
}

}
