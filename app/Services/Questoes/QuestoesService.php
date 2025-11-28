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
            // return $response["choices"][0]["message"]["content"];
            // exit;
            return $response["choices"][0]["message"]["content"];
        }

        public function verificarQuestoes($questoes) { 
            $questoes = explode("---",$questoes);
            $mensagemQuestoes = "";
            for($i = 0; $i < count($questoes); $i++) {
                $mensagemQuestoes = $mensagemQuestoes . $questoes[$i];
            };



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
                        "content" => "Você é um curador educacional especializado em selecionar perguntas de alta qualidade. Sua tarefa é analisar uma lista de perguntas sobre um tema e escolher **as 35 melhores** para quem está **começando a aprender**. Regras obrigatórias: 1. Analise cada pergunta da lista e selecione apenas **as 35 melhores**, considerando: - Clareza - Objetividade - Relevância para iniciantes - Cobertura do tema de forma equilibrada 2. Mantenha as perguntas **curtas e objetivas**, com no máximo 25 palavras. 3. Não modifique o conteúdo das perguntas; apenas selecione. 4. Não repita perguntas. 5. Cada pergunta deve ficar em **uma linha separada**. 6. Não enumere nem adicione texto adicional. 7. Retorne apenas as **35 perguntas selecionadas**, em formato de lista. 8. Todas as perguntas SEMPRE deverão ser separadas apenas por --- independente da situação independente da situação e nunca utilize quebra de linha ou contra barra + n."
                    ],
                    [
                        "role" => "user",
                        "content" => $mensagemQuestoes
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

            $questoes = $response["choices"][0]["message"]["content"];
            $questoes = explode("---",$questoes);
            return $questoes;
        }

    }

?>