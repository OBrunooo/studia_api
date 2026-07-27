<?php

namespace App\Actions;

use App\Models\GerandoTema;
use App\Models\Token;
use App\Models\UserTema;
use App\Models\Tema;
use App\Jobs\GerarTemaJob;

class TrataTemaAction {
    public function handle($tema, $user) {
        try {
            $buscaTema = Tema::where("nome", "=", $tema)->first();
            if($buscaTema !== null) {
                $userId = $user->id;

                $verificacao = UserTema::where("user_id", $userId)
                ->where("tema_id", $buscaTema->id)->first();
                if($verificacao !== null) {
                    return [
                        "success" => true,
                        "message" => "Você já está cadastrado no tema $tema"
                    ];
                }
                UserTema::criarUserTema($user, $buscaTema);
                return [
                    "success" => true,
                    "message" => "Você foi cadastrado ao tema $tema com sucesso"
                ];
            }

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
                        "content" => "Você receberá um texto curto informado por um usuário.

Sua tarefa é determinar se esse texto representa um tema adequado para gerar questões ou quizzes.

Se o texto NÃO representar claramente um tema de estudo, conceito, disciplina, tecnologia, linguagem, framework, metodologia, evento histórico, obra, fenômeno, processo, área do conhecimento ou qualquer outro assunto sobre o qual seja possível elaborar perguntas, responda exatamente:

INVALIDO

Se o texto representar um tema válido:
- corrija erros ortográficos;
- corrija acentuação;
- remova espaços e palavras desnecessárias;
- padronize a capitalização (maiúsculas e minúsculas) quando necessário;
- mantenha o tema curto e objetivo;
- preserve o idioma original;
- não traduza;
- não acrescente palavras, contexto, explicações ou detalhes;
- não altere o significado do tema.

Exemplos:

Entrada: logika de programacao
Saída: Lógica de Programação

Entrada: laravel
Saída: Laravel

Entrada: revoluçao francesa
Saída: Revolução Francesa

Entrada: fotosintese
Saída: Fotossíntese

Entrada: tcp/ip
Saída: TCP/IP

Entrada: cadeira
Saída: INVALIDO

Entrada: coisa
Saída: INVALIDO

Entrada: legal
Saída: INVALIDO

Entrada: abc123
Saída: INVALIDO

Em caso de dúvida, responda exatamente:

INVALIDO

A resposta deve conter apenas uma única linha, sem aspas, comentários ou qualquer texto adicional."
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
                "Authorization: Bearer " . config('services.openai.key')
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
            $resultado = $response["output"][1]["content"][0]["text"];
            $resultado = mb_strtoupper($resultado, 'UTF-8');
            if($resultado == "INVALIDO") {
                Token::armazenaTokens($tokens, "trata-tema");
                return [
                    "success" => false,
                    "message" => "O tema $tema digitado é inválido"
                ];
            }
            $tema = $resultado;
            $buscaTema = Tema::where("nome", "=", $tema)->first();
            if($buscaTema !== null) {
                $userId = $user->id;

                $verificacao = UserTema::where("user_id", $userId)
                ->where("tema_id", $buscaTema->id)->first();
                if($verificacao !== null) {
                    Token::armazenaTokens($tokens, "trata-tema");
                    return [
                        "success" => true,
                        "message" => "Você já está cadastrado no tema $tema"
                    ];
                }
                UserTema::criarUserTema($user, $buscaTema);
                Token::armazenaTokens($tokens, "trata-tema");
                return [
                    "success" => true,
                    "message" => "Você foi cadastrado ao tema $tema com sucesso"
                ];
            }
            Token::armazenaTokens($tokens, "trata-tema");
            $gerandoTema = GerandoTema::where("tema", $tema)->first();
            if($gerandoTema !== null) {
                return [
                    "success" => true,
                    "message" => "Seu tema está sendo tratado, quando estiver pronto, você será notificado"
                ];
            }
            $gerandoTema = GerandoTema::create([
                "tema" => $tema,
                "user_id" => $user->id
            ]);
            GerarTemaJob::dispatch($gerandoTema->id);
            return [
                "success" => true,
                "message" => "Seu tema está sendo tratado, quando estiver pronto, você será notificado"
            ];
        } catch (\Exception $e) {
            if($gerandoTema) {
                $gerandoTema->delete();
            }
            if($tokens) {
                Token::armazenaTokens($tokens, "trata-tema");
            }
            return [
                "success" => false,
                "message" => "Ocorreu um erro ao solicitar o tratamento do tema",
                "errorMessage" => $e->getMessage(),
                "errorLine" => $e->getLine()
            ];
        }
    }
}