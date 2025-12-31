<?php 
namespace App\Services\LoginRegister;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class LoginRegisterService {
    protected $user;

    public function __construct(User $user) {
        $this->user = $user;
    }

    public function verificaLogin(string $email, string $senha) {
        if (!Auth::attempt([
            "email" => $email, 
            "password" => $senha])) {
            return [
                'message' => 'Credenciais inválidas'
            ];
        }

        $user = Auth::user();

        $token = $user->createToken("flutter")->plainTextToken;
        
        return [
            "user" => $user,
            "token" => $token
        ];
    }

    public function registrarUser(string $email, string $senha, string $nome) {
        try {
            $user = $this->user->create([
                "email" => $email,
                "password" => $senha,
                "name" => $nome
            ]);

        if (!Auth::attempt([
            "email" => $email, 
            "password" => $senha])) {
            return [
                'message' => 'Erro ao registrar usuário'
            ];
        }

        $user = Auth::user();

        $token = $user->createToken("flutter")->plainTextToken;
        
        return [
            "user" => $user,
            "token" => $token
        ];

        } catch (\Throwable $th) {
           return "Erro";
        }
    }
}

?>