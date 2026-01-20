<?php 
    namespace App\Services\User;
    use App\Models\User;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Hash;  

    class UserService {
        public function atualizaSenha($senhas) {
            try {
                $user = Auth::user();
                
                if (!Hash::check($senhas['senhaAntiga'], $user->password)) {
                    return [
                        'status' => 'error',
                        'message' => 'Senha antiga incorreta'
                    ];
                }
    
                $user->password = Hash::make($senhas['senhaNova']);
                $user->save();
    
    
                return [
                    'status' => 'successo',
                    'mensagem' => 'Senha atualizada com sucesso'
                ];
            } catch (\Throwable $th) {
                return [
                    'status' => 'error',
                    'mensagem' => 'Ocorreu um erro ao atualizar senha'
                ];
            }
        }

        public function atualizaAvatar($avatarId) {
            try {
                $user = Auth::user();
                $user["avatar_id"] = $avatarId;
                $user->save();

                return [
                    "status" => "sucesso",
                    "mensagem" => "Avatar atualizado com sucesso"
                ];
            } catch (\Throwable $th) {
                return [
                    "status" => "error",
                    "mensagem" => "Erro ao atualizar avatar"
                ];
            }
        }
    }

?>