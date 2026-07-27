<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Services\LogService;
use App\Models\Modelo;

class Token extends Model
{
    protected $fillable = [
        "modelo_id",
        "input",
        "output",
        "funcao"
    ];

    public static function armazenaTokens($tokens, $funcao) {
        try {
            foreach ($tokens as $nome_modelo => $token) {
                $modelo = Modelo::where("nome", $nome_modelo)->first();
                if(!$modelo) {
                    $modelo = Modelo::MODELO_DEFAULT;
                }
                $modelo = $modelo->id;
                self::create([
                    "modelo_id" => $modelo,
                    "input" => $token["input"],
                    "output" => $token["output"],
                    "funcao" => $funcao
                ]);
            }
        } catch (\Throwable $th) {
            LogService::error(action: "armazena-tokens", user: Auth::user(), error: $th, data: [
                "tokens" => $tokens,
                "funcao" => $funcao
            ]);
        }
    }
}
