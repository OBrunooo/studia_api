<?php 
    namespace App\Services\Questoes;
    use Illuminate\Http\Request;
    use App\Models\ConjuntoQuestoes;
    use App\Models\Questoes;



    class QuestoesService {

        protected $request;

        public function verificarTema($tema) {
            ini_set('max_execution_time', 300); 
            set_time_limit(300);

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, "https://api.openai.com/v1/chat/completions");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 300);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                "model" => "gpt-5-nano",
                "messages" => [
                    [
                        "role" => "system",
                        "content" => "Você irá receber um texto curto digitado pelo usuário que representa um tema para gerar questões. O texto pode estar com erros ortográficos, informalidades ou redundâncias. Sua tarefa é corrigir a ortografia, remover palavras desnecessárias e fazer uma leve abstração para transformar o texto em um tema claro, direto e padronizado, mantendo o sentido geral sem adicionar detalhes inexistentes. Retorne apenas o tema final, curto e objetivo. Exemplos: \"logika de programacao\"→\"lógica de programação\"; \"entender como funciona logika de programacao\"→\"lógica de programação\"; \"quero questao sobre como funciona a lei da gravdede\"→\"lei da gravidade\". Responda sempre com um único tema."
                    ],
                    [
                        "role" => "user",
                        "content" => $tema
                    ]
                    ],
                    "max_completion_tokens" => 10000
            ]));

            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Accept: application/json",
                "Content-Type: application/json",
                "Authorization: Bearer " . env('OPENAI_API_KEY')
            ]);

            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

            $response = curl_exec($ch);

            if($response === false){
                return response()->json([
                    'erro' => curl_error($ch)
                ]);
            };
            
            curl_close($ch);
            $response = json_decode($response, true);
            return [
                "tema" => $response["choices"][0]["message"]["content"],
                "token" => [
                    "entrada" => $response["usage"]["prompt_tokens"],
                    "saida" => $response["usage"]["completion_tokens"],
                    ]
            ];
            exit;
        }

        public function gerarQuestoes($tema, $token) {   
            ini_set('max_execution_time', 300); 
            set_time_limit(300);

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, "https://api.openai.com/v1/chat/completions");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 300);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                "model" => "gpt-5-nano",
                "messages" => [
                    [
                        "role" => "system",
                        "content" => "Você é um gerador avançado de questões educacionais. Sua tarefa é criar **perguntas objetivas, claras e didáticas** sobre um tema fornecido pelo usuário, com foco em **quem está começando a aprender**. Siga rigorosamente estas regras: 1. Gere exatamente 120 perguntas. 2. As perguntas devem ser **curtas e objetivas**, preferencialmente com no máximo 25 palavras. 3. Cubra todo o tema de forma **abrangente e introdutória**, apropriada para iniciantes. 4. Evite perguntas muito técnicas ou complexas; elas devem facilitar o aprendizado inicial. 5. Não repita perguntas, ideias ou frases. 6. Não forneça respostas. 7. Todas as perguntas SEMPRE deverão ser separadas apenas por --- independente da situação e nunca utilize quebra de linha ou contra barra + n. 8. Não enumere (sem “1.”, “2.” ou “•”). 9. Não forneça explicações ou texto adicional; apenas a lista de perguntas. 10. Certifique-se de que as perguntas sejam **objetivas e diretas**, focadas no aprendizado inicial."
                    ],
                    [
                        "role" => "user",
                        "content" => $tema
                    ]
                    ],
                    "max_completion_tokens" => 50000
            ]));

            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Accept: application/json",
                "Content-Type: application/json",
                "Authorization: Bearer " . env('OPENAI_API_KEY')
            ]);

            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

            $response = curl_exec($ch);

            if($response === false){
                return response()->json([
                    'erro' => curl_error($ch)
                ]);
            };
            
            curl_close($ch);
            $response = json_decode($response, true);

            $token["entrada"] = $token["entrada"] + $response["usage"]["prompt_tokens"];
            $token["saida"] = $token["saida"] + $response["usage"]["completion_tokens"];
            return [
                "questoes" => $response["choices"][0]["message"]["content"],
                "token" => $token
            ];
            exit;
        }

        public function verificarQuestoes($questoes, $token) { 

            $questoes = explode("---",$questoes);
            $mensagemQuestoes = "";
            for($i = 0; $i < count($questoes); $i++) {
                $mensagemQuestoes = $mensagemQuestoes . $questoes[$i];
            };



            ini_set('max_execution_time', 600); 
            set_time_limit(600);

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, "https://api.openai.com/v1/chat/completions");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 600);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                "model" => "gpt-5-nano",
                "messages" => [
                    [
                        "role" => "system",
                        "content" => "Você é um especialista em curadoria educacional e geração de questões para iniciantes. Sua tarefa: (1) analisar uma lista de perguntas fornecida; (2) selecionar exatamente as 70 melhores; (3) organizar essas 70 do mais fácil ao mais difícil; (4) gerar 4 alternativas para cada pergunta, onde a primeira alternativa é sempre a correta. Regras obrigatórias: 1) Analise cada pergunta individualmente. 2) Não alterar, reescrever, resumir ou modificar o texto original das perguntas; apenas selecionar e reorganizar. 3) Não repetir perguntas. 4) Produzir exatamente 70 blocos. 5) Cada bloco deve ser formatado sem nenhuma quebra de linha visível ou invisível, e sem o texto 'PerguntaOriginal'. 6) Formato estrito de cada bloco: iniciar com '---' seguido imediatamente pela pergunta original; em seguida concatenar quatro alternativas, cada uma delimitada por '{{{}}}' e sem qualquer quebra de linha, por exemplo: ---PERGUNTA_AQUI{{{}}}Alternativa1_correta{{{}}}{{{}}}Alternativa2_plausivel{{{}}}{{{}}}Alternativa3_plausivel{{{}}}{{{}}}Alternativa4_plausivel{{{}}}---. 7) A primeira alternativa é sempre a correta. 8) As outras três devem ser incorretas, porém plausíveis e curtas, com no máximo 12 palavras. 9) Nunca usar numeração, letras (A,B,C...), bullets ou símbolos adicionais. 10) Nunca usar '<', '>', '[', ']' ou qualquer caractere que possa ser interpretado como markup. 11) Nunca incluir '\n', '\n', '\r', '\r', '\t', '\t', ou qualquer caractere de escape no output. Nenhuma forma de quebra de linha é permitida. 12) O resultado final deve ser um único texto contínuo contendo os 70 blocos consecutivos exatamente no formato descrito, sem espaços extras, sem quebras e sem texto adicional. 13) Retorne apenas os blocos finais formatados."
                    ],
                    [
                        "role" => "user",
                        "content" => $mensagemQuestoes
                    ]
                    ],
                    "max_completion_tokens" => 100000
            ]));

            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Accept: application/json",
                "Content-Type: application/json",
                "Authorization: Bearer " . env('OPENAI_API_KEY')
            ]);

            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

            $response = curl_exec($ch);

            if($response === false){
                return response()->json([
                    'erro' => curl_error($ch)
                ]);
            };
            
            curl_close($ch);
            $response = json_decode($response, true);


            $token["entrada"] = $token["entrada"] + $response["usage"]["prompt_tokens"];
            $token["saida"] = $token["saida"] + $response["usage"]["completion_tokens"];

            $response = $response["choices"][0]["message"]["content"];
            $response = explode("---",$response);

            $questoes = [];
            for($i = 0; $i < count($response); $i++) {
                if($response[$i] != "") {
                    $conjunto = explode("{{{}}}", $response[$i]);
                    try {
                        $questoes[]= [
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
            return [
                "questoes" => $questoes,
                "token" => $token
            ];
            exit;
        }

        public function verificarConjunto($questoes, $tokenNano) {

            $message = "";
            for($i = 0; $i < count($questoes); $i++) {
                $questao = $questoes[$i];
                $indice = $i + 1;
                try {
                    $message = $message . "$indice- Pergunta: ".$questao['questao']." \nAlternativas:\nAlternativa1: ".$questao['alternativa1']."\nAlternativa2:".$questao['alternativa2']."\nAlternativa3:".$questao['alternativa3']."\nAlternativa4:".$questao['alternativa4']."\n";
                } catch (\Throwable $th) {
                }
            };
            $ch = curl_init();
            set_time_limit(600);
            curl_setopt($ch, CURLOPT_URL, "https://api.openai.com/v1/chat/completions");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 600);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                "model" => "gpt-5-mini",
                "messages" => [
                    [
                        "role" => "system",
                        "content" => "Você é um verificador extremamente rígido de perguntas de múltipla escolha. Cada item contém uma pergunta seguida de quatro alternativas no formato: ---Pergunta{{{}}}alternativa1{{{}}}{{{}}}alternativa2{{{}}}{{{}}}alternativa3{{{}}}{{{}}}alternativa4{{{}}}--- Regras absolutamente obrigatórias: 1. A PERGUNTA deve ser correta, clara, objetiva e sem ambiguidade. 2. A PERGUNTA não pode permitir múltiplas respostas ou interpretações diferentes. 3. A alternativa1 deve ser a única verdadeira e responder exatamente ao que é perguntado. 4. A alternativa1 não pode ser vaga, incompleta, parcialmente verdadeira ou depender de interpretação. 5. As alternativas 2, 3 e 4 devem ser totalmente falsas, porém plausíveis dentro do contexto da pergunta. 6. Nenhuma alternativa falsa pode ser parcialmente verdadeira, interpretável como correta ou verdadeira em algum cenário. 7. Pergunta e alternativas devem ser coerentes entre si; qualquer desalinhamento invalida o item. 8. NÃO tolere nenhum erro: qualquer ambiguidade, inconsistência ou dupla interpretação deve ser marcado como erro. 9. Se absolutamente todas as perguntas estiverem perfeitas, retorne apenas: sucesso 10. Se houver qualquer pergunta incorreta, retorne apenas os números das perguntas erradas no formato: ,1,4,7, (sempre começando e terminando com vírgula, sem espaços). 11. Não escreva nada além disso. Agora valide rigorosamente a lista:"
                    ],
                    [
                        "role" => "user",
                        "content" => $message
                    ]
                    ],
                    "max_completion_tokens" => 20000
            ]));

            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Accept: application/json",
                "Content-Type: application/json",
                "Authorization: Bearer " . env('OPENAI_API_KEY')
            ]);

            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

            $response = curl_exec($ch);

            if($response === false){
                return response()->json([
                    'erro requisição' => curl_error($ch)
                ]);
            };
            
            curl_close($ch);
            $response = json_decode($response, true);
            $tokenMini = [
                "entrada" => $response["usage"]["prompt_tokens"],
                "saida" => $response["usage"]["completion_tokens"],
            ];
            $response = $response["choices"][0]["message"]["content"];

            if($response == "sucesso") {
                return "ok";
                exit;
            }

            try {
                $response = explode(",", $response);
                $erros = [];
                for($i = 0; $i < count($response); $i++) {
                    if($response[$i] != ""){
                        try {
                            $erros[] = (int) $response[$i];
                        } catch (\Throwable $th) {
                        }
                    }
                };
                $oldQuestoes = $questoes;
                for($i = 0; $i < count($erros); $i++) {
                    unset($questoes[($erros[$i] - 1)]);
                };
                $questoes = array_values($questoes);
                return [
                    "questoes" => $questoes,
                    "token-nano" => $tokenNano,
                    "token-mini" => $tokenMini
                ];
                exit;
            } catch (\Throwable $th) {
                return "errorfim";
            }
        }

        public function armazenarQuestoes($dados) {
            $questoes = $dados["questoes"];
            for($i=0; $i <= 34; $i) {
                
                $conjuntoQuestoesId = [];
                for($x = 0; $x <= 6; $x++) {
                    $questao = Questoes::create([
                        "questao" => $questoes[$i]["questao"],
                        "alternativa1" => $questoes[$i]["alternativa1"],
                        "alternativa2" => $questoes[$i]["alternativa2"],
                        "alternativa3" => $questoes[$i]["alternativa3"],
                        "alternativa4" => $questoes[$i]["alternativa4"],
                        ]);
                    $i++;
                    $conjuntoQuestoesId[] = $questao->id; 
                }
                
                ConjuntoQuestoes::create([
                    'user_id' => 1,
                    'questao1_id' => $conjuntoQuestoesId[0],
                    'questao2_id' => $conjuntoQuestoesId[1],
                    'questao3_id' => $conjuntoQuestoesId[2],
                    'questao4_id' => $conjuntoQuestoesId[3],
                    'questao5_id' => $conjuntoQuestoesId[4],
                    'questao6_id' => $conjuntoQuestoesId[5],
                    'questao7_id' => $conjuntoQuestoesId[6]
                ]);
            };
        }

        public function listaConjuntos($userId) {

        }

    }

?>