<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Tema;
use App\Models\UserTema;
use App\Models\UserConclusaoConjunto;
use App\Actions\TrataTemaAction;
use App\Services\LogService;

class TemasController extends Controller {
 

    public function buscarGerarTema (Request $request) {
        try {
            $tema = $request->input("tema");
            $request->validate([
            "tema" => "required|string|min:3|max:255|regex:/^[\pL\pN\s\-\/.,()]+$/u"
            ], [
                "tema.required" => "O campo tema é obrigatório",
                "tema.string" => "O campo tema deve ser uma string",
                "tema.max" => "O campo tema deve ter no máximo 255 caracteres",
                "tema.min" => "O campo tema deve ter no mínimo 3 caracteres",
                "tema.regex" => "O campo tema deve conter apenas letras, números, espaços, hífens, barras, vírgulas e parênteses"
            ]);
            $buscaTema = Tema::where("nome", "=", $tema)->first();
            if(!empty($buscaTema)) {
                $userTema = UserTema::where("user_id", Auth::user()->id)->where("tema_id", $buscaTema->id)->first();
                if(!empty($userTema)) {
                    return response()->json([
                        "success" => true,
                        "message" => "O usuário já está cadastrado no tema $tema"
                    ], 200);
                }
                UserTema::criarUserTema(Auth::user(), $buscaTema);
                return response()->json([
                    "success" => true,
                    "message" => "O usuário foi cadastrado no tema $tema com sucesso"
                ], 200);
            }

            LogService::info(action: "cadastrar-tema", user: Auth::user(), message: "Cadastro do tema $tema enfileirado", data: [
                "user_id" => Auth::user()->id,
                "tema" => $tema,
                "ip" => $request->ip()
            ]);
            $trataTemaAction = new TrataTemaAction();
            $resultado = $trataTemaAction->handle($tema, Auth::user());
            if($resultado["success"]) {
                return response()->json([
                    "success" => true,
                    "message" => $resultado["message"] ?? "Buscando e gerando questões para o tema $tema"
                ], 200);
            }
            return response()->json([
                "success" => false,
                "message" => $resultado["message"] ?? "Ocorreu um erro ao buscar e gerar questões"
            ], 400);
        } catch (\Throwable $th) {
            LogService::error(action: "solicitar-buscar-gerar-questoes-tema", user: Auth::user(), error: $th);
            return response()->json([
                "success" => false,
                "message" => "Ocorreu um erro ao buscar e gerar questões",
                "errorMessage" => $th->getMessage(),
                "errorLine" => $th->getLine(),
                "file" => $th->getFile()
            ], 500);
        }

    }

    public function conjuntosTema(Request $request) {
        $request->validate([
            "tema_id" => "required|exists:temas,id"
        ], [
            "tema_id.required" => "O campo tema_id é obrigatório",
            "tema_id.exists" => "O tema_id informado não existe"
        ]);
        try {
            $tema = Tema::find($request->tema_id);
            $conjuntos = $tema->conjuntosTema();
            $conjuntosConclusao = [];
            foreach ($conjuntos as $conjunto) {
                $conjuntoConclusao = UserConclusaoConjunto::where("conjunto_id", $conjunto)->where("user_id", Auth::user()->id)->first();
                if($conjuntoConclusao) {
                    $conjuntosConclusao[] = [
                        "id" => $conjunto,
                        "conclusao" => $conjuntoConclusao->conclusao
                    ];
                }
            }
            return response()->json(["conjuntos" => $conjuntosConclusao]);
        } catch (\Exception $e) {
            LogService::error(action: "listar-conjuntos-tema", user: Auth::user(), error: $e, data: [
                "tema_id" => $request->tema_id,
            ]);
            return response()->json(["error" => "Erro ao listar conjuntos"], 500);
        }
    }

    public function temasUser(Request $request) {
        try {
            $user = Auth::user();
            $temasUser = $user->temasUser();
            if(empty($temasUser)) {
                return response()->json(["temas" => []]);
            }
            $temas = [];
            foreach ($temasUser as $tema) {
                $conclusao = $user->conclusaoTemasUser($tema->id);
                $temas[] = [
                    "id" => $tema->id,
                    "nome" => $tema->nome,
                    "porcentagem_conclusao" => $conclusao
                ];
            }
            return response()->json(["temas" => $temas]);
        } catch (\Exception $e) {
            LogService::error("listar-temas-usuario", Auth::user(), $e);
            return response()->json(["error" => "Erro ao listar temas"], 500);
        }
    }
}
