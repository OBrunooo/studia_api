<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Http\Controllers\TemasController;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Services\LogService;
use App\Models\NotificacaoUsuario;

class BuscarGerarQuestoesTema implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */

    private string $tema;
    private $user;

    public int $timeout = 1800;
    public int $tries = 1;

    public function __construct(string $tema, $user)
    {
        $this->tema = $tema;
        $this->user = $user;
    }

    public function handle(): void
    {
        try {
            $resultado = TemasController::verificarTema($this->tema, $this->user);
            if($resultado["success"]) {
                if(str_starts_with($resultado["message"], "O usuário já está cadastrado no tema")) {
                    LogService::info(action: "processar-buscar-gerar-questoes-tema", user: $this->user, message: "Usuário já cadastrado para o tema $this->tema", data: [
                        "user_id" => $this->user->id,
                        "tema" => $this->tema,
                    ]);                    
                    NotificacaoUsuario::adicionarNotificacao("Você já está cadastrado a um tema referente a: $this->tema", "info", $this->user);
                } else {
                    LogService::info(action: "processar-buscar-gerar-questoes-tema", user: $this->user, message: "Tema gerado com sucesso: $this->tema", data: [
                        "user_id" => $this->user->id,
                        "tema" => $this->tema
                    ]);                    
                    NotificacaoUsuario::adicionarNotificacao("Um novo tema foi gerado com o tema indicado: $this->tema", "info", $this->user);
                }
            } else {
                LogService::error(action: "processar-buscar-gerar-questoes-tema", user: $this->user, error: null, data: [
                    "user_id" => $this->user->id,
                    "tema" => $this->tema,
                    "resultado" => $resultado["message"],
                    "conjuntos" => $resultado["conjuntos"] ?? null
                ]);
                NotificacaoUsuario::adicionarNotificacao("Erro ao gerar o tema indicado: $this->tema", "error", $this->user);
            }
        } catch (\Throwable $th) {
            NotificacaoUsuario::adicionarNotificacao("Erro ao gerar o tema indicado: $this->tema", "error", $this->user);
            LogService::error(action: "processar-buscar-gerar-questoes-tema", user: $this->user, error: $th);
        }
    }

    public function failed(\Throwable $th): void
    {
        Log::error("O job BuscarGerarQuestoesTema falhou para o tema $this->tema", [
            "message" => $th->getMessage(),
            "file" => $th->getFile(),
            "line" => $th->getLine(),
            "trace" => $th->getTraceAsString(),
        ]);
    }
}
