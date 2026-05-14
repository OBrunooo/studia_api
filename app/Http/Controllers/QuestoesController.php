<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\UserTema;
use App\Models\Modelo;
use App\Models\Token;
use App\Models\Tema;
use App\Models\Questoes;
use App\Models\ConjuntoQuestoes;
use App\Models\UserConclusaoConjunto;


class QuestoesController extends Controller {

    public function buscarGerarQuestoesTema (Request $request) {
        try {
            $tema = $request->input("tema");
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
                return response()->json([
                    "success" => false,
                    "message" => "Ocorreu um erro ao realizar a conexão com o agente IA",
                    "errorMessage" => curl_error($ch)
                ], 500);
            };
            
            curl_close($ch);
            $response = json_decode($response, true);

            if($response === null){
                return response()->json([
                    "success" => false,
                    "message" => "Ocorreu um erro ao realizar a conexão com o agente IA"
                ], 500);
            };
            if (! isset($response['usage']['input_tokens'], $response['usage']['output_tokens'], $response['output'][1]['content'][0]['text'])) {
                return response()->json([
                    "success" => false,
                    "message" => "Resposta inválida do agente IA",
                ], 500);
            }
            $tokens = [
                "gpt-5-nano" => [
                    "input" => (int) $response["usage"]["input_tokens"],
                    "output" => (int) $response["usage"]["output_tokens"] 
                ]
            ]; 
            $tema = $response["output"][1]["content"][0]["text"];

            if($tema == "INVALIDO") {
                self::armazenaTokens($tokens);           
                return response()->json([
                    "success" => false,
                    "message" => "O tema digitado é inválido",
                    "response" => $response
                ], 500);
            }

            $buscaTema = Tema::where("nome", "=", $tema)->first();
            if($buscaTema !== null) {
                $userId = Auth::user()->id;

                $verificacao = UserTema::where("user_id", $userId)
                ->where("tema_id", $buscaTema->id)->first();
                if($verificacao !== null) {
                    self::armazenaTokens($tokens);
                    return response()->json([
                        "success" => false,
                        "message" => "O usuário já está cadastrado no tema $tema"
                    ], 500);
                }
                UserTema::create([
                    "user_id" => $userId,
                    "tema_id" => $buscaTema->id
                ]);
                self::armazenaTokens($tokens);
                return response()->json([
                    "success" => true,
                    "message" => "O usuário foi cadastrado ao tema com sucesso"
                ], 200);
            }
            return $this->gerarQuestoes($tema, $tokens);
        } catch (\Throwable $th) {
            return response()->json([
                "success" => false,
                "message" => "Ocorreu um erro ao buscar e gerar questões",
                "errorMessage" => $th->getMessage(),
                "errorLine" => $th->getLine()
            ], 500);
        }
    }

    public function gerarQuestoes($tema, $tokens) {  
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
                            "content" => "Você é um gerador avançado de questões educacionais. Sua tarefa é criar **perguntas objetivas, claras e didáticas** sobre um tema fornecido pelo usuário, com foco em **quem está começando a aprender**. Siga rigorosamente estas regras: 1. Gere exatamente 100 perguntas. 2. As perguntas devem ser **curtas e objetivas**, preferencialmente com no máximo 25 palavras. 3. Cubra todo o tema de forma **abrangente e introdutória**, apropriada para iniciantes. 4. Evite perguntas muito técnicas ou complexas; elas devem facilitar o aprendizado inicial. 5. Não repita perguntas, ideias ou frases. 6. Não forneça respostas. 7. Todas as perguntas SEMPRE deverão ser separadas apenas por --- independente da situação e nunca utilize quebra de linha ou contra barra + n. 8. Não enumere (sem “1.”, “2.” ou “•”). 9. Não forneça explicações ou texto adicional; apenas a lista de perguntas. 10. Certifique-se de que as perguntas sejam **objetivas e diretas**, focadas no aprendizado inicial."
                        ],
                        [
                            "role" => "user",
                            "content" => $tema
                        ]
                    ]
                ]));

            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Accept: application/json",
                "Content-Type: application/json",
                "Authorization: Bearer " . env('OPENAI_API_KEY')
            ]);


            $response = curl_exec($ch); 
            
            if($response === false){
                curl_close($ch);
                self::armazenaTokens($tokens);
                return response()->json([
                    "success" => false,
                    "message" => "Ocorreu um erro ao realizar a conexão com o agente IA"
                ], 500);
            };
            
            curl_close($ch);
            $response = json_decode($response, true);

            if($response === null){
                self::armazenaTokens($tokens);
                return response()->json([
                    "success" => false,
                    "message" => "Ocorreu um erro ao realizar a conexão com o agente IA"
                ], 500);
            };

            if (! isset($response['usage']['input_tokens'], $response['usage']['output_tokens'], $response['output'][1]['content'][0]['text'])) {
                self::armazenaTokens($tokens);
                return response()->json([
                    "success" => false,
                    "message" => "Resposta inválida do agente IA",
                ], 500);
            }

            $tokens["gpt-5-nano"]["input"] =  $tokens["gpt-5-nano"]["input"] + $response["usage"]["input_tokens"];
            $tokens["gpt-5-nano"]["output"] =  $tokens["gpt-5-nano"]["output"] + $response["usage"]["output_tokens"];

            $questoes = $response["output"][1]["content"][0]["text"];
            return $this->analisarESelecionarQuestoes($questoes, $tema, $tokens);   
        } catch (\Throwable $th) {
            self::armazenaTokens($tokens);
            return response()->json([
                "success" => false,
                "message" => "Ocorreu um erro ao armazenar questoes/conjuntos e tema",
                "errorMessage" => $th->getMessage(),
                "errorLine" => $th->getLine()
            ], 500); 
        }         

    }

    public function analisarESelecionarQuestoes($questoes, $tema, $tokens) {
        try {
            ini_set('max_execution_time', 600); 
            set_time_limit(600);

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, "https://api.openai.com/v1/responses");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 600);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                "model" => "gpt-5-nano",
                "input" => [
                    [
                        "role" => "system",
                        "content" => "Você é um especialista em curadoria educacional e geração de questões para iniciantes. Sua tarefa: (1) analisar uma lista de perguntas fornecida; (2) selecionar exatamente as 50 melhores; (3) organizar essas 50 do mais fácil ao mais difícil; (4) gerar 4 alternativas para cada pergunta, onde a primeira alternativa é sempre a correta. Regras obrigatórias: 1) Analise cada pergunta individualmente. 2) Não alterar, reescrever, resumir ou modificar o texto original das perguntas; apenas selecionar e reorganizar. 3) Não repetir perguntas. 4) Produzir exatamente 50 blocos. 5) Cada bloco deve ser formatado sem nenhuma quebra de linha visível ou invisível, e sem o texto 'PerguntaOriginal'. 6) Formato estrito de cada bloco: iniciar com '---' seguido imediatamente pela pergunta original; em seguida concatenar quatro alternativas, cada uma delimitada por '{{{}}}' e sem qualquer quebra de linha, por exemplo: ---PERGUNTA_AQUI{{{}}}Alternativa1_correta{{{}}}{{{}}}Alternativa2_plausivel{{{}}}{{{}}}Alternativa3_plausivel{{{}}}{{{}}}Alternativa4_plausivel{{{}}}---. 7) A primeira alternativa é sempre a correta. 8) As outras três devem ser incorretas, porém plausíveis e curtas, com no máximo 12 palavras. 9) Nunca usar numeração, letras (A,B,C...), bullets ou símbolos adicionais. 10) Nunca usar '<', '>', '[', ']' ou qualquer caractere que possa ser interpretado como markup. 11) Nunca incluir '\n', '\n', '\r', '\r', '\t', '\t', ou qualquer caractere de escape no output. Nenhuma forma de quebra de linha é permitida. 12) O resultado final deve ser um único texto contínuo contendo os 50 blocos consecutivos exatamente no formato descrito, sem espaços extras, sem quebras e sem texto adicional. 13) Retorne apenas os blocos finais formatados."
                    ],
                    [
                        "role" => "user",
                        "content" => $questoes
                    ]
                    ]
            ]));

            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Accept: application/json",
                "Content-Type: application/json",
                "Authorization: Bearer " . env('OPENAI_API_KEY')
            ]);

            
            $response = curl_exec($ch);


            if($response === false){
                curl_close($ch);
                self::armazenaTokens($tokens);
                return response()->json([
                    "success" => false,
                    "message" => "Ocorreu um erro ao realizar analisar as questões"
                ], 500);
            };
            $response = json_decode($response, true);
            curl_close($ch);
            
            if($response === null){
                self::armazenaTokens($tokens);
                return response()->json([
                    "success" => false,
                    "message" => "Ocorreu um erro ao realizar analisar as questões"
                ], 500);
            };

            if (! isset($response['usage']['input_tokens'], $response['usage']['output_tokens'], $response['output'][1]['content'][0]['text'])) {
                self::armazenaTokens($tokens);
                return response()->json([
                    "success" => false,
                    "message" => "Resposta inválida do agente IA",
                ], 500);
            }
            
            $tokens["gpt-5-nano"]["input"] =  $tokens["gpt-5-nano"]["input"] + $response["usage"]["input_tokens"];
            $tokens["gpt-5-nano"]["output"] =  $tokens["gpt-5-nano"]["output"] + $response["usage"]["output_tokens"];


            $response = $response["output"][1]["content"][0]["text"];
            $response = explode("---",$response);

            $conjuntos = [];
            for($i = 0; $i < count($response); $i++) {
                if($response[$i] != "") {
                    $conjunto = explode("{{{}}}", $response[$i]);
                    try {
                        $conjuntos[]= [
                            "questao" => $conjunto[0],
                            "alternativa1" => $conjunto[1],
                            "alternativa2" => $conjunto[2],
                            "alternativa3" => $conjunto[3],
                            "alternativa4" => $conjunto[4],
                        ];  
                    } catch (\Throwable $th) {
                    }

                }
            }
            if(count($conjuntos) < 35){
                self::armazenaTokens($tokens);
                return response()->json([
                    "success" => false,
                    "message" => "Ocorreu um erro ao realizar analisar as questões",
                ], 500);
            }
            return $this->verificarConjunto($conjuntos, $tema, $tokens);            
        } catch (\Throwable $th) {
            self::armazenaTokens($tokens);
            return response()->json([
                "success" => false,
                "message" => "Ocorreu um erro ao realizar analisar as questões",
                "errorMessage" => $th->getMessage(),
                "errorLine" => $th->getLine()
            ], 500); 
        }
    }

    public function verificarConjunto($conjuntos, $tema, $tokens) {
        try {
            $message = "";
            for($i = 0; $i < count($conjuntos); $i++) {
                $questao = $conjuntos[$i];
                $indice = $i + 1;
                try {
                    $message = $message . "$indice- Pergunta: ".$questao['questao']." \nAlternativas:\nAlternativa1: ".$questao['alternativa1']."\nAlternativa2:".$questao['alternativa2']."\nAlternativa3:".$questao['alternativa3']."\nAlternativa4:".$questao['alternativa4']."\n";
                } catch (\Throwable $th) {
                }
            };
            if($message === ""){
                self::armazenaTokens($tokens);
                return response()->json([
                    "success" => false,
                    "message" => "Ocorreu um erro ao realizar verificar os conjuntos",
                ], 500);
            }
            $ch = curl_init();
            set_time_limit(600);
            curl_setopt($ch, CURLOPT_URL, "https://api.openai.com/v1/responses");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 600);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                "model" => "gpt-5-nano",
                "input" => [
                    [
                        "role" => "system",
                        "content" => "Você é um verificador extremamente rígido de perguntas de múltipla escolha. Cada item contém uma pergunta seguida de quatro alternativas no formato: ---Pergunta{{{}}}alternativa1{{{}}}{{{}}}alternativa2{{{}}}{{{}}}alternativa3{{{}}}{{{}}}alternativa4{{{}}}--- Regras absolutamente obrigatórias: 1. A PERGUNTA deve ser correta, clara, objetiva e sem ambiguidade. 2. A PERGUNTA não pode permitir múltiplas respostas ou interpretações diferentes. 3. A alternativa1 deve ser a única verdadeira e responder exatamente ao que é perguntado. 4. A alternativa1 não pode ser vaga, incompleta, parcialmente verdadeira ou depender de interpretação. 5. As alternativas 2, 3 e 4 devem ser totalmente falsas, porém plausíveis dentro do contexto da pergunta. 6. Nenhuma alternativa falsa pode ser parcialmente verdadeira, interpretável como correta ou verdadeira em algum cenário. 7. Pergunta e alternativas devem ser coerentes entre si; qualquer desalinhamento invalida o item. 8. NÃO tolere nenhum erro: qualquer ambiguidade, inconsistência ou dupla interpretação deve ser marcado como erro. 9. Se absolutamente todas as perguntas estiverem perfeitas, retorne apenas: 'sucesso'. 10. Se houver qualquer pergunta incorreta, retorne apenas os números das perguntas erradas no formato: ,1,4,7, (sempre começando e terminando com vírgula, sem espaços). 11. Não escreva nada além disso. Agora valide rigorosamente a lista:"
                    ],
                    [
                        "role" => "user",
                        "content" => $message
                    ]
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
                self::armazenaTokens($tokens);
                return response()->json([
                    "success" => false,
                    "message" => "Ocorreu um erro ao realizar a conexão com o agente IA"
                ], 500);
            };
            
            curl_close($ch);
            $response = json_decode($response, true);


            if($response === null){
                self::armazenaTokens($tokens);
                return response()->json([
                    "success" => false,
                    "message" => "Ocorreu um erro ao realizar a conexão com o agente IA"
                ], 500);
            };
            if (! isset($response['usage']['input_tokens'], $response['usage']['output_tokens'], $response['output'][1]['content'][0]['text'])) {
                self::armazenaTokens($tokens);
                return response()->json([
                    "success" => false,
                    "message" => "Resposta inválida do agente IA",
                ], 500);
            }
            $tokens["gpt-5-nano"]["input"] =  $tokens["gpt-5-nano"]["input"] + $response["usage"]["input_tokens"];
            $tokens["gpt-5-nano"]["output"] =  $tokens["gpt-5-nano"]["output"] + $response["usage"]["output_tokens"];
            
            $response = $response["output"][1]["content"][0]["text"];

            $normalizedResponse = preg_replace('/\s+/', '', strtolower($response));
            if ($normalizedResponse !== 'sucesso' && $normalizedResponse !== 'sucesso.') {
                $partes = explode(',', $response);
                $erros = [];
                foreach ($partes as $chunk) {
                    $chunk = trim($chunk);
                    if ($chunk === '' || ! ctype_digit($chunk)) {
                        continue;
                    }
                    $n = (int) $chunk;
                    if ($n > 0) {
                        $erros[] = $n;
                    }
                }
                foreach ($erros as $n) {
                    unset($conjuntos[$n - 1]);
                }
            }

            return $this->armazenaConjuntosTema(array_values($conjuntos), $tema, $tokens);   
        } catch (\Throwable $th) {
            self::armazenaTokens($tokens);
            return response()->json([
                "success" => false,
                "message" => "Ocorreu um erro ao verificar conjuntos"
            ], 500);        
        }
    }

    public function armazenaConjuntosTema($conjuntos, $tema, $tokens) {
        try {
            if(count($conjuntos) < 35){
                self::armazenaTokens($tokens);
                return response()->json([
                    "success" => false,
                    "message" => "Ocorreu um erro ao armazenar conjuntos, pois o número de conjuntos é menor que 35",
                    'conjuntos' => $conjuntos
                ], 500);
            };

            DB::transaction(function () use ($conjuntos, $tema) {
                $idConjuntos = [];
                for ($i = 0; $i < 35; $i) {
                    
                    
                    $conjuntoQuestoesId = [];
                    for ($x = 0; $x <= 6; $x) {
                        $questao = Questoes::create([
                            "questao" => $conjuntos[$i]["questao"],
                            "alternativa1" => $conjuntos[$i]["alternativa1"],
                            "alternativa2" => $conjuntos[$i]["alternativa2"],
                            "alternativa3" => $conjuntos[$i]["alternativa3"],
                            "alternativa4" => $conjuntos[$i]["alternativa4"],
                        ]);
                        $i++;
                        $x++;
                        if ($questao != null) {
                            $conjuntoQuestoesId[] = $questao->id;
                        }
                    }
                    
                    if(count($conjuntoQuestoesId) < 7) {
                        throw new \Exception("Ocorreu um erro ao armazenar conjuntos");
                    }
                    
                    $conjunto = ConjuntoQuestoes::create([
                        'questao1_id' => $conjuntoQuestoesId[0],
                        'questao2_id' => $conjuntoQuestoesId[1],
                        'questao3_id' => $conjuntoQuestoesId[2],
                        'questao4_id' => $conjuntoQuestoesId[3],
                        'questao5_id' => $conjuntoQuestoesId[4],
                        'questao6_id' => $conjuntoQuestoesId[5],
                        'questao7_id' => $conjuntoQuestoesId[6]
                    ]);
                    array_push($idConjuntos, $conjunto->id);
                }

                if(count($idConjuntos) < 5)  {
                    throw new \Exception("Ocorreu um erro ao armazenar conjuntos");
                }

                $conjuntoQuestoesId = [];
                $conjunto = self::buscaConjuntoQuestoesPorId($idConjuntos[0]);
                array_push($conjuntoQuestoesId, $conjunto->questao1_id,$conjunto->questao2_id);
                $conjunto = self::buscaConjuntoQuestoesPorId($idConjuntos[1]);
                array_push($conjuntoQuestoesId, $conjunto->questao1_id,$conjunto->questao2_id);
                $conjunto = self::buscaConjuntoQuestoesPorId($idConjuntos[2]);
                array_push($conjuntoQuestoesId, $conjunto->questao1_id,$conjunto->questao2_id,$conjunto->questao3_id);

                $conjunto = ConjuntoQuestoes::create([
                    'questao1_id' => $conjuntoQuestoesId[0],
                    'questao2_id' => $conjuntoQuestoesId[1],
                    'questao3_id' => $conjuntoQuestoesId[2],
                    'questao4_id' => $conjuntoQuestoesId[3],
                    'questao5_id' => $conjuntoQuestoesId[4],
                    'questao6_id' => $conjuntoQuestoesId[5],
                    'questao7_id' => $conjuntoQuestoesId[6]
                ]);
                array_push($idConjuntos, $conjunto->id);

                $conjuntoQuestoesId = [];
                $conjunto = self::buscaConjuntoQuestoesPorId($idConjuntos[2]);
                array_push($conjuntoQuestoesId, $conjunto->questao4_id,$conjunto->questao5_id);
                $conjunto = self::buscaConjuntoQuestoesPorId($idConjuntos[3]);
                array_push($conjuntoQuestoesId, $conjunto->questao1_id,$conjunto->questao2_id);
                $conjunto = self::buscaConjuntoQuestoesPorId($idConjuntos[4]);
                array_push($conjuntoQuestoesId, $conjunto->questao1_id,$conjunto->questao2_id,$conjunto->questao3_id);

                $conjunto = ConjuntoQuestoes::create([
                    'questao1_id' => $conjuntoQuestoesId[0],
                    'questao2_id' => $conjuntoQuestoesId[1],
                    'questao3_id' => $conjuntoQuestoesId[2],
                    'questao4_id' => $conjuntoQuestoesId[3],
                    'questao5_id' => $conjuntoQuestoesId[4],
                    'questao6_id' => $conjuntoQuestoesId[5],
                    'questao7_id' => $conjuntoQuestoesId[6]
                ]);
                array_push($idConjuntos, $conjunto->id);

                for ($i = 0; $i < count($idConjuntos); $i ++) {
                    UserConclusaoConjunto::create([
                        "conjunto_id" => $idConjuntos[$i],
                        "user_id" => Auth::user()->id,
                    ]);
                }

                $temaModel = Tema::create([
                    "nome" => $tema,
                    "conjunto1_id" => $idConjuntos[0],
                    "conjunto2_id" => $idConjuntos[1],
                    "conjunto3_id" => $idConjuntos[2],
                    "conjunto4_id" => $idConjuntos[3],
                    "conjunto5_id" => $idConjuntos[4],
                    "conjunto6_id" => $idConjuntos[5],
                    "conjunto7_id" => $idConjuntos[6]
                ]);

                UserTema::create([
                    "user_id" => Auth::user()->id,
                    "tema_id" => $temaModel->id
                ]);
            });

            self::armazenaTokens($tokens);

            return response()->json([
                "success" => true,
                "message" => "O usuário foi cadastrado ao tema com sucesso"
            ], 200);
        } catch (\Throwable $th) {
            self::armazenaTokens($tokens);

            return response()->json([
                "success" => false,
                "message" => "Ocorreu um erro armazenar questoes/conjuntos e tema",
                "errorMessage" => $th->getMessage(),
                "errorLine" => $th->getLine()
            ], 500);  
        }
    }

    private static function armazenaTokens($tokens) {
        $modelosTokens = array_keys($tokens);
        foreach($modelosTokens as $tm) {
            $modelo = Modelo::where("nome", $tm)->first();
            if($modelo === null) {
                Token::create([
                    "modelo_id" => 2,
                    "input" => $tokens[$tm]['input'],
                    "output" => $tokens[$tm]['output'],
                ]);
            } else {
                Token::create([
                    "modelo_id" => $modelo->id,
                    "input" => $tokens[$tm]['input'],
                    "output" => $tokens[$tm]['output'],
                ]);
            }
        }          
    }


    private static function buscaConjuntoQuestoesPorId($id) {
        $conjunto = ConjuntoQuestoes::where("id", "=", $id)->first();
        if($conjunto === null) {
            throw new \Exception("Ocorreu um erro ao buscar conjunto de questões");
        }
        return $conjunto;
    }
}


