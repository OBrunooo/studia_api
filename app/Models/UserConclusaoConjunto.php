<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tema;
use App\Models\User;

class UserConclusaoConjunto extends Model
{
    protected $table = "user_conclusao_conjunto";

    protected $fillable = [
        'conjunto_id',
        'user_id',
        'conclusao'
    ];    

    public static function criarUserConclusaoConjunto(User $user, Tema $tema) {
        $conjuntos = $tema->conjuntosTema();
        foreach($conjuntos as $conjunto) {
            self::create([
                "conjunto_id" => $conjunto,
                "user_id" => $user->id,
                "conclusao" => false
            ]);
        }
    }
}
