<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Tema;
use App\Models\UserConclusaoConjunto;
use App\Models\NotificacaoUsuario;

class UserTema extends Model
{
    public $table = "user_tema";

    CONST TABLE = "user_tema";
    protected $fillable = [
        'user_id',
        'tema_id',
        'conclusao'
    ];

    public static function criarUserTema(User $user, Tema $tema) {
        self::create([
            "user_id" => $user->id,
            "tema_id" => $tema->id,
            "conclusao" => false
        ]);
        UserConclusaoConjunto::criarUserConclusaoConjunto($user, $tema);
        NotificacaoUsuario::create([
            "user_id" => $user->id,
            "message" => "O tema $tema->nome foi cadastrado com sucesso",
            "tipo" => "info"
        ]);
    }
}
