<?php 

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ConjuntoQuestoes;
use App\Models\Questoes;
use App\Models\UserConclusaoConjunto;

class ConjuntosController extends Controller {

    public static function questoesConjunto(Request $request) {
        $request->validate([
            "conjunto_id" => "required|exists:conjunto_questoes,id"
        ], [
            "conjunto_id.required" => "O campo conjunto_id é obrigatório",
            "conjunto_id.exists" => "O conjunto_id informado não existe"
        ]);
        try {
            $conjunto = ConjuntoQuestoes::where("id", "=", $request->input("conjunto_id"))->first();
            if($conjunto === null) {
                return response()->json([
                    "success" => false,
                    "message" => "O conjunto de questões não foi encontrado"
                ], 404);
            }
            $questoes = $conjunto->questoesConjunto();
            $questoes = Questoes::whereIn("id", $questoes)->select("id", "questao", "alternativa1", "alternativa2", "alternativa3", "alternativa4")->get();
            return response()->json([
                "success" => true,
                "questoes" => $questoes
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "success" => false,
                "message" => "Ocorreu um erro ao buscar as questões do conjunto",
                "errorMessage" => $th->getMessage(),
                "errorLine" => $th->getLine()
            ], 500);
        }
    }

    public function concluirConjunto(Request $request) {
        $request->validate([
            "conjunto_id" => "required|exists:conjunto_questoes,id"
        ], [
            "conjunto_id.required" => "O campo conjunto_id é obrigatório",
            "conjunto_id.exists" => "O conjunto_id informado não existe"
        ]);
        try {
            $conjunto = ConjuntoQuestoes::where("id", "=", $request->input("conjunto_id"))->first();
            if($conjunto === null) {
                return response()->json([
                    "success" => false,
                    "message" => "O conjunto de questões não foi encontrado"
                ], 404);
            }
            $userConclusaoConjunto = UserConclusaoConjunto::where("conjunto_id", "=", $request->input("conjunto_id"))->where("user_id", "=", Auth::user()->id)->first();
            if($userConclusaoConjunto === null) {
                return response()->json([
                    "success" => false,
                    "message" => "O conjunto de questões não foi encontrado"
                ], 404);
            }
            $userConclusaoConjunto->conclusao = true;
            $userConclusaoConjunto->save();
            return response()->json([
                "success" => true,
                "message" => "O conjunto de questões foi concluído com sucesso"
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "success" => false,
                "message" => "Ocorreu um erro ao verificar a conclusão do conjunto",
                "errorMessage" => $th->getMessage(),
                "errorLine" => $th->getLine()
            ], 500);
        }
    }
}               