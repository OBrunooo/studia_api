<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Services\LogService;

class NotificacaoUsuario extends Model
{
    protected $table = "notificacoes_usuarios";
    CONST TABLE = "notificacoes_usuarios";
    protected $fillable = [
        'user_id',
        'message',
        'tipo',
    ];

    public static function adicionarNotificacao($message, $tipo = "info", $user) {
        try {
            self::create([
                'user_id' => $user->id,
                'message' => $message,
                'tipo' => $tipo,
            ]);
            return true;
        } catch (\Throwable $th) {
            LogService::error(action: "adicionar-notificacao", user: Auth::user(), error: $th);
            return false;
        }	
    }
}
