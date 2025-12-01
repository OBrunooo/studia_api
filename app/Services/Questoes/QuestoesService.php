<?php 
    namespace App\Services\Questoes;


    class QuestoesService {

        public function gerarQuestoes($tema) {   
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
                        "content" => "Você é um gerador avançado de questões educacionais. Sua tarefa é criar **perguntas objetivas, claras e didáticas** sobre um tema fornecido pelo usuário, com foco em **quem está começando a aprender**. Siga rigorosamente estas regras: 1. Gere exatamente 200 perguntas. 2. As perguntas devem ser **curtas e objetivas**, preferencialmente com no máximo 25 palavras. 3. Cubra todo o tema de forma **abrangente e introdutória**, apropriada para iniciantes. 4. Evite perguntas muito técnicas ou complexas; elas devem facilitar o aprendizado inicial. 5. Não repita perguntas, ideias ou frases. 6. Não forneça respostas. 7. Todas as perguntas SEMPRE deverão ser separadas apenas por --- independente da situação e nunca utilize quebra de linha ou contra barra + n. 8. Não enumere (sem “1.”, “2.” ou “•”). 9. Não forneça explicações ou texto adicional; apenas a lista de perguntas. 10. Certifique-se de que as perguntas sejam **objetivas e diretas**, focadas no aprendizado inicial."
                    ],
                    [
                        "role" => "user",
                        "content" => $tema
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
                    'erro' => curl_error($ch)
                ]);
            };
            
            curl_close($ch);
            $response = json_decode($response, true);
            return $response["choices"][0]["message"]["content"];
        }

        public function verificarQuestoes($questoes) { 

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
                        "content" => "Você é um especialista em curadoria educacional e geração de questões para iniciantes. Sua tarefa: (1) analisar uma lista de perguntas fornecida; (2) selecionar exatamente as 35 melhores; (3) organizar essas 35 do mais fácil ao mais difícil; (4) gerar 4 alternativas para cada pergunta, onde a primeira alternativa é sempre a correta. Regras obrigatórias: 1) Analise cada pergunta individualmente. 2) Não alterar, reescrever, resumir ou modificar o texto original das perguntas; apenas selecionar e reorganizar. 3) Não repetir perguntas. 4) Produzir exatamente 35 blocos. 5) Cada bloco deve ser formatado sem nenhuma quebra de linha visível ou invisível, e sem o texto 'PerguntaOriginal'. 6) Formato estrito de cada bloco: iniciar com '---' seguido imediatamente pela pergunta original; em seguida concatenar quatro alternativas, cada uma delimitada por '{{{}}}' e sem qualquer quebra de linha, por exemplo: ---PERGUNTA_AQUI{{{}}}Alternativa1_correta{{{}}}{{{}}}Alternativa2_plausivel{{{}}}{{{}}}Alternativa3_plausivel{{{}}}{{{}}}Alternativa4_plausivel{{{}}}---. 7) A primeira alternativa é sempre a correta. 8) As outras três devem ser incorretas, porém plausíveis e curtas, com no máximo 12 palavras. 9) Nunca usar numeração, letras (A,B,C...), bullets ou símbolos adicionais. 10) Nunca usar '<', '>', '[', ']' ou qualquer caractere que possa ser interpretado como markup. 11) Nunca incluir '\n', '\n', '\r', '\r', '\t', '\t', ou qualquer caractere de escape no output. Nenhuma forma de quebra de linha é permitida. 12) O resultado final deve ser um único texto contínuo contendo os 35 blocos consecutivos exatamente no formato descrito, sem espaços extras, sem quebras e sem texto adicional. 13) Retorne apenas os blocos finais formatados."
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


            $response = $response["choices"][0]["message"]["content"];
            $response = explode("---",$response);

            $questoes = [];
            for($i = 0; $i < count($response); $i++) {
                if($response[$i] != "") {
                    $conjunto = explode("{{{}}}", $response[$i]);

                    $questoes[]= [
                        "questao" => $conjunto[0],
                        "alternativa1" => $conjunto[1],
                        "alternativa2" => $conjunto[2],
                        "alternativa3" => $conjunto[3],
                        "alternativa4" => $conjunto[4],
                    ];   

                }
            }
            return $questoes;
        }

    }

?>