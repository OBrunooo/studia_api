<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Http\Controllers\TemasController;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Services\LogService;

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
            LogService::info(action: "processar-buscar-gerar-questoes-tema", user: $this->user, message: "Questões geradas com sucesso para o tema $this->tema", data: [
                "user_id" => $this->user->id,
                    "tema" => $this->tema,
                    "resultado" => $resultado["message"]
                ]);
            } else {
                LogService::error(action: "processar-buscar-gerar-questoes-tema", user: $this->user, error: null, data: [
                    "user_id" => $this->user->id,
                    "tema" => $this->tema,
                    "resultado" => $resultado["message"]
                ]);
            }
        } catch (\Throwable $th) {
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
