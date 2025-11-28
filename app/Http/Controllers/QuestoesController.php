<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Questoes\QuestoesService;

class QuestoesController extends Controller {

    protected $questoesService;

    public function __construct (QuestoesService $questoesService) {
        $this->questoesService = $questoesService;
    }

    public function gerarQuestoes(Request $request) {  
        $tema = $request->query("tema");

        if (isset($tema)) {
            $tema = $request->query("tema");
            $questoes = $this->questoesService->gerarQuestoes($tema);
            return redirect()->route("verificarQuestoes.get", ["questoes" => $questoes]);
        }
    }

    public function verificarQuestoes(Request $request) {
        $questoes = $request->query("questoes");
        if($questoes != 0) {
            return $this->questoesService->verificarQuestoes($questoes);
        }
        
    }

}
