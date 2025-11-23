<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GerarQuestoesController extends Controller
{
    public function gerarPerguntas(Request $request)
    {   
        ini_set('max_execution_time', 300); 
        set_time_limit(300);
        $tema = $request->query("tema");

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
                    "content" => "Retorne todos os dados em formato de json e em apenas uma mensagem"
                ],
                [
                    "role" => "system",
                    "content" => "Você é um gerador avançado de questões educacionais. Sua tarefa é criar **perguntas objetivas, claras e didáticas** sobre um tema fornecido pelo usuário, com foco em **quem está começando a aprender**. Siga rigorosamente estas regras: 1. Gere exatamente 200 perguntas. 2. As perguntas devem ser **curtas e objetivas**, preferencialmente com no máximo 25 palavras. 3. Cubra todo o tema de forma **abrangente e introdutória**, apropriada para iniciantes. 4. Evite perguntas muito técnicas ou complexas; elas devem facilitar o aprendizado inicial. 5. Não repita perguntas, ideias ou frases. 6. Não forneça respostas. 7. Cada pergunta deve estar em **uma linha separada**. 8. Não enumere (sem “1.”, “2.” ou “•”). 9. Não forneça explicações ou texto adicional; apenas a lista de perguntas. 10. Certifique-se de que as perguntas sejam **objetivas e diretas**, focadas no aprendizado inicial."
                ],
                [
                    "role" => "user",
                    "content" => $tema
                ]
                ],
                "max_completion_tokens" => 20000
        ]));

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
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
        }

        curl_close($ch);

        return $response;
    }
}
