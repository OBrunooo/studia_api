<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\GerandoTema;
use App\Models\Tema;
use App\Models\Token;
use App\Models\Questoes;
use App\Models\ConjuntoQuestoes;
use App\Models\NotificacaoUsuario;
use App\Models\UserTema;
use App\Models\User;
use App\Models\Log;
use Illuminate\Support\Facades\DB;

class GerarTemaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 480;
    public int $tries = 2;

    public function __construct(public int $gerandoTemaId) {}

    private $tema;

    public function handle(): void
    {
        try {
            $gerandoTema = GerandoTema::find($this->gerandoTemaId);
            if($gerandoTema === null) {
                Log::error(action: "gerar-tema", message: "Gerando tema não encontrado: " . $this->gerandoTemaId);
                return;
            }
            $this->tema = $gerandoTema->tema;
            $buscaTema = Tema::where("nome", "=", $this->tema)->first();
            if($buscaTema !== null) {
                $gerandoTema = GerandoTema::where("tema", $gerandoTema->tema)->get();
                foreach($gerandoTema as $g) {
                    $userTema = UserTema::where("user_id", $g->user_id)->where("tema_id", $buscaTema->id)->first();
                    if($userTema === null) {
                        $user = User::where("id", "=", $g->user_id)->first();
                        UserTema::criarUserTema($user, $buscaTema);
                        $g->delete();
                        continue;
                    }
                    NotificacaoUsuario::create([
                        "user_id" => $g->user_id,
                        "message" => "Você já está cadastrado(a) no tema: " . $this->tema,
                        "tipo" => "info"
                    ]);
                    $g->delete();
                }
                return;
            }
            Log::info(action: "gerar-tema", message: "Gerando tema: $this->tema");
            $resultado = $this->gerarQuestoes();
            if($resultado["success"] != true) {
                $this->errroAoGerarTema();
                $resultado['funcao'] = "gerar-questoes";
                Log::error(action: "gerar-tema", message: "Erro ao gerar tema: " . $resultado["message"], data: $resultado);
                return;
            }
            $gerarAlternativas = $this->gerarAlternativas($resultado["questoes"]);
            if($gerarAlternativas["success"] != true) {
                $this->errroAoGerarTema();
                $gerarAlternativas['funcao'] = "analisar-e-selecionar-questoes";
                Log::error(action: "gerar-tema", message: "Erro ao analisar e selecionar questões: " . $gerarAlternativas["message"], data: $gerarAlternativas);
                return;
            }
            $verificarConjuntos = $this->verificarConjuntos($gerarAlternativas["conjuntos"]);
            if($verificarConjuntos["success"] != true) {
                $this->errroAoGerarTema();
                $verificarConjuntos['funcao'] = "verificar-conjuntos";
                Log::error(action: "gerar-tema", message: "Erro ao verificar conjuntos: " . $verificarConjuntos["message"], data: $verificarConjuntos);
                return;
            }
            $armazenaConjuntosTema = $this->armazenaConjuntosTema($verificarConjuntos["conjuntos"]);
            if($armazenaConjuntosTema["success"] != true) {
                $this->errroAoGerarTema();
                $armazenaConjuntosTema['funcao'] = "armazena-conjuntos-tema";
                Log::error(action: "gerar-tema", message: "Erro ao armazenar conjuntos: " . $armazenaConjuntosTema["message"], data: $armazenaConjuntosTema);
                return;
            }
            $gerandoTema = GerandoTema::where("tema", $gerandoTema->tema)->get();
            foreach($gerandoTema as $g) {
                $user = User::where("id", "=", $g->user_id)->first();
                $temaGerado = Tema::where("nome", "=", $this->tema)->first();
                UserTema::criarUserTema($user, $temaGerado);
                $g->delete();
            }
            Log::info(action: "gerar-tema", message: "Tema gerado com sucesso: ".$armazenaConjuntosTema["temaId"]);
        }
        catch (\Throwable $th) {
            Log::error(action: "gerar-tema", message: "Erro ao gerar tema: " . $th->getMessage(), data: [
                "errorMessage" => $th->getMessage(),
                "errorLine" => $th->getLine(),
                "file" => $th->getFile(),
            ]);
            $this->errroAoGerarTema();
            return;
        }
    }   

    private function gerarQuestoes() {
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
                            "content" => "Você é um gerador avançado de questões educacionais. Sua tarefa é criar **perguntas objetivas, claras e didáticas** sobre um tema fornecido pelo usuário, com foco em **quem está começando a aprender**.

        Siga rigorosamente estas regras:

        1. Gere exatamente 150 perguntas.
        2. As perguntas devem ser **curtas e objetivas**, preferencialmente com no máximo 25 palavras.
        3. Cubra todo o tema de forma **abrangente e introdutória**, apropriada para iniciantes.
        4. Evite perguntas muito técnicas ou complexas; elas devem facilitar o aprendizado inicial.
        5. Utilize como base conteúdos introdutórios presentes em livros acadêmicos, materiais educacionais reconhecidos, artigos confiáveis e referências amplamente aceitas na área, garantindo veracidade e consistência pedagógica.
        6. As perguntas devem priorizar conceitos básicos, definições simples, aplicações iniciais e compreensão fundamental do tema.
        7. NÃO gere perguntas avançadas, aprofundadas, altamente técnicas ou de nível especialista, exceto quando o usuário solicitar explicitamente um nível mais difícil.
        8. Evite termos excessivamente técnicos, pegadinhas, contextualizações complexas ou questões que exijam conhecimento prévio avançado.
        9. Não repita perguntas, ideias ou frases.
        10. Não forneça respostas.
        11. Todas as perguntas SEMPRE deverão ser separadas apenas por --- independente da situação e nunca utilize quebra de linha ou contra barra + n.
        12. Não enumere (sem “1.”, “2.” ou “•”).
        13. Não forneça explicações ou texto adicional; apenas a lista de perguntas.
        14. Certifique-se de que as perguntas sejam **objetivas, diretas e fáceis de compreender**, focadas no aprendizado inicial."
                        ],
                        [
                            "role" => "user",
                            "content" => $this->tema
                        ]
                    ]
                ]));

            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Accept: application/json",
                "Content-Type: application/json",
                "Authorization: Bearer " . config('services.openai.key')
            ]);


            $response = curl_exec($ch); 
            
            if($response === false){
                curl_close($ch);
                return [
                    "success" => false,
                    "message" => "Ocorreu um erro ao realizar a conexão com o agente IA"
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

            $tokens = [];
            $tokens["gpt-5-nano"]["input"] =  $response["usage"]["input_tokens"];
            $tokens["gpt-5-nano"]["output"] =  $response["usage"]["output_tokens"];

            $questoes = $response["output"][1]["content"][0]["text"];
            if(isset($tokens)) {
                Token::armazenaTokens($tokens, "gerar-questoes");
            }
            return [
                "success" => true,
                "questoes" => $questoes,
            ];
        } catch (\Throwable $th) {
            if(isset($tokens)) {
                Token::armazenaTokens($tokens, "gerar-questoes");
            }
            return [
                "success" => false,
                "message" => "Ocorreu um erro ao armazenar questoes/conjuntos e tema",
                "errorMessage" => $th->getMessage(),
                "errorLine" => $th->getLine(),
                "file" => $th->getFile(),
            ]; 
        }         
    
    
    }

    private function gerarAlternativas($questoes) {
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
                        "content" => "Você é um especialista em curadoria educacional e geração de questões para iniciantes. Sua tarefa: (1) analisar uma lista de perguntas fornecida; (2) selecionar exatamente as 70 melhores; (3) organizar essas 70 do mais fácil ao mais difícil; (4) gerar 4 alternativas para cada pergunta, onde a primeira alternativa é sempre a correta. Regras obrigatórias: 1) Analise cada pergunta individualmente. 2) Não alterar, reescrever, resumir ou modificar o texto original das perguntas; apenas selecionar e reorganizar. 3) Não repetir perguntas. 4) Produzir exatamente 70 blocos. 5) Cada bloco deve ser formatado sem nenhuma quebra de linha visível ou invisível, e sem o texto 'PerguntaOriginal'. 6) Formato estrito de cada bloco: iniciar com '---' seguido imediatamente pela pergunta original; em seguida concatenar quatro alternativas, cada uma delimitada por '{{{}}}' e sem qualquer quebra de linha, por exemplo: ---PERGUNTA_AQUI{{{}}}Alternativa1_correta{{{}}}{{{}}}Alternativa2_plausivel{{{}}}{{{}}}Alternativa3_plausivel{{{}}}{{{}}}Alternativa4_plausivel{{{}}}---. 7) A primeira alternativa é sempre a correta. 8) As outras três devem ser incorretas, porém plausíveis e curtas, com no máximo 12 palavras. 9) Nunca usar numeração, letras (A,B,C...), bullets ou símbolos adicionais. 10) Nunca usar '<', '>', '[', ']' ou qualquer caractere que possa ser interpretado como markup. 11) Nunca incluir '\n', '\n', '\r', '\r', '\t', '\t', ou qualquer caractere de escape no output. Nenhuma forma de quebra de linha é permitida. 12) O resultado final deve ser um único texto contínuo contendo os 70 blocos consecutivos exatamente no formato descrito, sem espaços extras, sem quebras e sem texto adicional. 13) Retorne apenas os blocos finais formatados."
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
                "Authorization: Bearer " . config('services.openai.key')
            ]);

            
            $response = curl_exec($ch);


            if($response === false){
                curl_close($ch);
                return [
                    "success" => false,
                    "message" => "Ocorreu um erro ao realizar analisar as questões, pois a resposta do agente IA é inválida"
                ];
            };
            $response = json_decode($response, true);
            curl_close($ch);
            
            if($response === null){
                return [
                    "success" => false,
                    "message" => "Ocorreu um erro ao realizar analisar as questões, pois a resposta do agente IA é inválida"
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
                    "input" => $response["usage"]["input_tokens"],
                    "output" => $response["usage"]["output_tokens"],
                ],
            ];

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
                if(isset($tokens)) {
                    Token::armazenaTokens($tokens, "analisar-e-selecionar-questoes");
                }
                return [
                    "success" => false,
                    "message" => "Ocorreu um erro ao realizar analisar as questões, pois o número de conjuntos é menor que 35",
                ];
            }
            if(isset($tokens)) {
                Token::armazenaTokens($tokens, "analisar-e-selecionar-questoes");
            }
            return [
                "success" => true,
                "conjuntos" => $conjuntos,
            ];
        } catch (\Throwable $th) {
            if(isset($tokens)) {
                Token::armazenaTokens($tokens, "analisar-e-selecionar-questoes");
            }
            return [
                "success" => false,
                "message" => "Ocorreu um erro ao realizar analisar as questões",
                "errorMessage" => $th->getMessage(),
                "errorLine" => $th->getLine(),
                "conjuntos" => $conjuntos
            ]; 
        }
    }

    private function verificarConjuntos($conjuntos) {
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
                return [
                    "success" => false,
                    "message" => "Ocorreu um erro ao realizar verificar os conjuntos",
                ];
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
                        "content" => "Você é um verificador extremamente rigoroso de perguntas de múltipla escolha.

        Cada item contém uma pergunta seguida de quatro alternativas no formato:

        ---Pergunta{{{}}}alternativa1{{{}}}{{{}}}alternativa2{{{}}}{{{}}}alternativa3{{{}}}{{{}}}alternativa4{{{}}}---

        Sua tarefa é validar a qualidade de cada questão seguindo obrigatoriamente estas regras:

        1. A PERGUNTA deve ser correta, clara, objetiva e possuir apenas uma resposta correta.

        2. A PERGUNTA não deve possuir ambiguidade real, dupla interpretação relevante ou depender de informações não fornecidas.

        3. A alternativa1 deve ser a única alternativa correta e deve responder adequadamente ao enunciado.

        4. A alternativa1 deve estar completa, correta e não pode ser uma resposta parcialmente verdadeira ou insuficiente.

        5. As alternativas 2, 3 e 4 devem estar incorretas no contexto específico da pergunta.

        6. As alternativas incorretas devem ser plausíveis como distratores, mas não podem ser consideradas respostas corretas para a pergunta apresentada.

        7. Pequenas semelhanças, termos relacionados ou partes parcialmente verdadeiras nas alternativas incorretas NÃO invalidam a questão, desde que a alternativa continue claramente incorreta como resposta final.

        8. Avalie a questão considerando o conhecimento padrão da área. Não invalide perguntas apenas por existirem interpretações extremamente específicas ou cenários incomuns.

        9. Caso exista dúvida razoável sobre qual alternativa é correta, considere a questão inválida.

        10. Caso a questão esteja tecnicamente correta, possua apenas uma resposta válida e seja adequada para avaliação do conhecimento, considere-a válida.

        11. Se absolutamente todas as perguntas estiverem perfeitas, retorne apenas:
        sucesso

        12. Se houver qualquer pergunta incorreta, retorne apenas os números das perguntas erradas no formato:
        ,1,4,7,

        Sempre comece e termine com vírgula, sem espaços ou qualquer texto adicional.

        Não explique suas decisões.
        Não escreva nada além do formato solicitado.

        Agora valide rigorosamente a lista:"
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
                "Authorization: Bearer " . config('services.openai.key')
            ]);


            $response = curl_exec($ch);

            if($response === false){
                curl_close($ch);
                return [
                    "success" => false,
                    "message" => "Ocorreu um erro ao realizar a conexão com o agente IA"
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
                    "input" => $response["usage"]["input_tokens"],
                    "output" => $response["usage"]["output_tokens"],
                ],
            ];

            $response = $response["output"][1]["content"][0]["text"];

            $normalizedResponse = preg_replace('/\s+/', '', strtolower($response));
            if ($normalizedResponse !== 'sucesso' && $normalizedResponse !== 'sucesso.') {
                $partes = explode(',', $response);
                $erros = [];
                foreach ($partes as $p) {
                    $p = trim($p);
                    if ($p === '' || ! ctype_digit($p)) {
                        continue;
                    }
                    $n = (int) $p;
                    if ($n > 0) {
                        $erros[] = $n;
                    }
                }
                foreach ($erros as $n) {
                    unset($conjuntos[$n - 1]);
                }
            }
            Token::armazenaTokens($tokens, "verificar-conjuntos");
            return [
                "success" => true,
                "conjuntos" => array_values($conjuntos),
            ];
        } catch (\Throwable $th) {
            if(isset($tokens)) {
                Token::armazenaTokens($tokens, "verificar-conjuntos");
            }
            return [
                "success" => false,
                "message" => "Ocorreu um erro ao verificar conjuntos",
            ];        
        }
    }

    private function armazenaConjuntosTema($conjuntos) {
        try {
            if(count($conjuntos) < 35){
                return [
                    "success" => false,
                    "message" => "Ocorreu um erro ao armazenar conjuntos, pois o número de conjuntos é menor que 35",
                    "conjuntos" => $conjuntos
                ];
            };
            $temaId;
            $temaNome = $this->tema;
            DB::transaction(function () use ($conjuntos, $temaNome, &$temaId) {
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
                    $idConjuntos[] = $conjunto->id;
                }

                if(count($idConjuntos) < 5)  {
                    throw new \Exception("Ocorreu um erro ao armazenar conjuntos");
                }

                $conjuntoQuestoesId = [];
                $conjunto = ConjuntoQuestoes::where("id", "=", $idConjuntos[0])->first();
                array_push($conjuntoQuestoesId, $conjunto->questao1_id,$conjunto->questao2_id);
                $conjunto = ConjuntoQuestoes::where("id", "=", $idConjuntos[1])->first();
                array_push($conjuntoQuestoesId, $conjunto->questao1_id,$conjunto->questao2_id);
                $conjunto = ConjuntoQuestoes::where("id", "=", $idConjuntos[2])->first();
                array_push($conjuntoQuestoesId, $conjunto->questao1_id,$conjunto->questao2_id,$conjunto->questao3_id);

                $novoConjunto = ConjuntoQuestoes::create([
                    'questao1_id' => $conjuntoQuestoesId[0],
                    'questao2_id' => $conjuntoQuestoesId[1],
                    'questao3_id' => $conjuntoQuestoesId[2],
                    'questao4_id' => $conjuntoQuestoesId[3],
                    'questao5_id' => $conjuntoQuestoesId[4],
                    'questao6_id' => $conjuntoQuestoesId[5],
                    'questao7_id' => $conjuntoQuestoesId[6]
                ]);
                $idConjuntos[] = $novoConjunto->id;

                $conjuntoQuestoesId = [];
                $conjunto = ConjuntoQuestoes::where("id", "=", $idConjuntos[2])->first();
                array_push($conjuntoQuestoesId, $conjunto->questao4_id,$conjunto->questao5_id);
                $conjunto = ConjuntoQuestoes::where("id", "=", $idConjuntos[3])->first();
                array_push($conjuntoQuestoesId, $conjunto->questao1_id,$conjunto->questao2_id);
                $conjunto = ConjuntoQuestoes::where("id", "=", $idConjuntos[4])->first();
                array_push($conjuntoQuestoesId, $conjunto->questao1_id,$conjunto->questao2_id,$conjunto->questao3_id);

                $novoConjunto = ConjuntoQuestoes::create([
                    'questao1_id' => $conjuntoQuestoesId[0],
                    'questao2_id' => $conjuntoQuestoesId[1],
                    'questao3_id' => $conjuntoQuestoesId[2],
                    'questao4_id' => $conjuntoQuestoesId[3],
                    'questao5_id' => $conjuntoQuestoesId[4],
                    'questao6_id' => $conjuntoQuestoesId[5],
                    'questao7_id' => $conjuntoQuestoesId[6]
                ]);
                $idConjuntos[] = $novoConjunto->id;

                $novoTema = Tema::create([
                    "nome" => $temaNome,
                    "conjunto1_id" => $idConjuntos[0],
                    "conjunto2_id" => $idConjuntos[1],
                    "conjunto3_id" => $idConjuntos[2],
                    "conjunto4_id" => $idConjuntos[3],
                    "conjunto5_id" => $idConjuntos[4],
                    "conjunto6_id" => $idConjuntos[5],
                    "conjunto7_id" => $idConjuntos[6]
                ]);
                $temaId = $novoTema->id;
            });

            return [
                "success" => true,
                "message" => "O tema $temaNome foi cadastrado com sucesso",
                "temaId" => $temaId
            ];
        } catch (\Throwable $th) {
            return [
                "success" => false,
                "message" => "Ocorreu um erro armazenar questoes/conjuntos e tema",
                "errorMessage" => $th->getMessage(),
                "errorLine" => $th->getLine()
            ];  
        }
    }

    private function errroAoGerarTema() {
        $gerandoTema = GerandoTema::where("tema", $this->tema)->get();
        foreach($gerandoTema as $g) {
            NotificacaoUsuario::create([
                "user_id" => $g->user_id,
                "message" => "Não foi possível gerar o tema: " . $this->tema. ". Por favor, tente novamente mais tarde.",
                "tipo" => "error"
            ]);
            $g->delete();
        }
        return true;
    }

    public function failed(\Throwable $th): void
    {
        $gerandoTema = GerandoTema::find($this->gerandoTemaId);

        Log::error(
            action: "gerar-tema-falhou",
            message: "Job falhou ao gerar tema: " . $th->getMessage(),
            data: [
                "gerandoTemaId" => $this->gerandoTemaId,
                "tema" => $gerandoTema?->tema,
            ]
        );

        if ($gerandoTema !== null) {
            $this->tema = $gerandoTema->tema;
            $this->errroAoGerarTema();
        }
    }

}