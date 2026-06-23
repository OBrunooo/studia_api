<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Tema;
use App\Models\UserTema;
use App\Models\UserConclusaoConjunto;
use App\Http\Controllers\QuestoesController;
use App\Jobs\BuscarGerarQuestoesTema;
use Illuminate\Support\Facades\Log;
use App\Services\LogService;

class TemasController extends Controller {
 

    public function buscarGerarQuestoesTema (Request $request) {
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
            BuscarGerarQuestoesTema::dispatch($request->input("tema"), Auth::user());
            LogService::info(action: "solicitar-buscar-gerar-questoes-tema", user: Auth::user(), message: "Solicitação de busca e geração de questões enfileirada para o tema $tema", data: [
                "user_id" => Auth::user()->id,
                "tema" => $tema,
                "ip" => $request->ip()
            ]);
            return response()->json([
                "success" => true,
                "message" => "Buscando e gerando questões para o tema $tema"
            ], 200);
        } catch (\Throwable $th) {
            LogService::error(action: "solicitar-buscar-gerar-questoes-tema", user: Auth::user(), error: $th);
            return response()->json([
                "success" => false,
                "message" => "Ocorreu um erro ao buscar e gerar questões"
            ], 500);
        }

    }

    public static function verificarTema($tema, $user) {
        try {
            ini_set('max_execution_time', 300); 
            set_time_limit(300);

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, "https://api.openai.com/v1/responses");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 300);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                "model" => "gpt-5-nano",
                "input" => [
                    [
                        "role" => "system",
                        "content" => 'Você receberá um texto curto que representa um possível tema para geração de questões. Um tema válido pode ser um assunto, conceito, área de estudo ou tecnologia, mesmo que seja apenas uma única palavra (ex: "Laravel", "fotossíntese", "derivadas"). Um tema inválido é algo genérico, objeto físico ou termo sem contexto conceitual (ex: "mouse", "cadeira", "coisa"). Se for inválido, responda apenas com INVALIDO. Se for válido, corrija erros ortográficos, remova excessos e padronize para um tema curto e claro, sem adicionar informações novas. Em caso de dúvida, considere como válido. Responda sempre com apenas uma linha.'
                    ],
                    [
                        "role" => "user",
                        "content" => $tema
                    ],
                ],
            ]));

            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Accept: application/json",
                "Content-Type: application/json",
                "Authorization: Bearer " . env('OPENAI_API_KEY')
            ]);

            $response = curl_exec($ch);
            if($response === false){
                curl_close($ch);
                return [
                    "success" => false,
                    "message" => "Ocorreu um erro ao realizar a conexão com o agente IA",
                    "errorMessage" => curl_error($ch)
                ];
            };
            
            curl_close($ch);
            $response = json_decode($response, true);

            if($response === null){
                return [
                    "success" => false,
                    "message" => "Ocorreu um erro ao realizar a conexão com o agente IA"
                ];
            };
            if (! isset($response['usage']['input_tokens'], $response['usage']['output_tokens'], $response['output'][1]['content'][0]['text'])) {
                return [
                    "success" => false,
                    "message" => "Resposta inválida do agente IA",
                ];
            }
            $tokens = [
                "gpt-5-nano" => [
                    "input" => (int) $response["usage"]["input_tokens"],
                    "output" => (int) $response["usage"]["output_tokens"] 
                ]
            ]; 
            $tema = $response["output"][1]["content"][0]["text"];
            $tema = mb_strtoupper($tema, 'UTF-8');
            if($tema == "INVALIDO") {
                QuestoesController::armazenaTokens($tokens);           
                return [
                    "success" => false,
                    "message" => "O tema $tema digitado é inválido",
                    "response" => $response
                ];
            }

            $buscaTema = Tema::where("nome", "=", $tema)->first();
            if($buscaTema !== null) {
                $userId = $user->id;

                $verificacao = UserTema::where("user_id", $userId)
                ->where("tema_id", $buscaTema->id)->first();
                if($verificacao !== null) {
                    QuestoesController::armazenaTokens($tokens);
                    return [
                        "success" => true,
                        "message" => "O usuário já está cadastrado no tema $tema"
                    ];
                }
                UserTema::create([
                    "user_id" => $userId,
                    "tema_id" => $buscaTema->id
                ]);
                $idsConjuntos = $buscaTema->conjuntosTema();
                foreach ($idsConjuntos as $id) {
                    UserConclusaoConjunto::create([
                        "conjunto_id" => $id,
                        "user_id" => $userId
                    ]);
                }
                QuestoesController::armazenaTokens($tokens);
                return [
                    "success" => true,
                    "message" => "O usuário foi cadastrado ao tema $tema com sucesso"
                ];
            }
            return (new QuestoesController())->gerarQuestoes($tema, $tokens, $user);
        } catch (\Throwable $th) {
            Log::info("Ocorreu um erro ao verificar o tema: " . $th->getMessage());
            return [
                "success" => false,
                "message" => "Ocorreu um erro ao buscar e gerar questões",
                "errorMessage" => $th->getMessage(),
                "errorLine" => $th->getLine()
            ];
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
