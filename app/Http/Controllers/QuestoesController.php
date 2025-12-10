<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Questoes\QuestoesService;

class QuestoesController extends Controller {

    protected $questoesService;

    public function __construct (QuestoesService $questoesService) {
        $this->questoesService = $questoesService;
    }

    public function verificarTema (Request $request) {
        $tema = $request->query("tema");

        $resultado = $this->questoesService->verificarTema($tema);
        return redirect()->route("gerarQuestoes.get", ["tema" => $resultado["tema"], "token" => $resultado["token"]]);
    }


    public function gerarQuestoes(Request $request) {  
        $tema = $request->query("tema");
        $token = $request->query("token");

        if (isset($tema)) {
            $tema = $request->query("tema");
            $resultado = $this->questoesService->gerarQuestoes($tema, $token);
            return redirect()->route("verificarQuestoes.get", ["questoes" => $resultado["questoes"], "token" => $resultado["token"]]);
        }
    }

    public function verificarQuestoes(Request $request) {
        $questoes = $request->query("questoes");
        $token = $request->query("token");

        if(isset($questoes)) {
            $resultado =  $this->questoesService->verificarQuestoes($questoes, $token);
            return redirect()->route("verificarConjunto.get", ["questoes" => $resultado['questoes'], "token" => $resultado['token']]);
        }
        
    }

    public function verificarConjunto(Request $request) {
        $questoes = $request->query("questoes");
        $token = $request->query("token");

        $resultado = $this->questoesService->verificarConjunto($questoes, $token);
        return $resultado;
        return redirect()->route("storageQuestoes.get", ["dados" => $resultado]);
    }

    public function storage(Request $request) {
        //Alterar para inputo ao utilziar api
        $dados = $request->input("dados");
        return $this->questoesService->armazenarQuestoes($dados);
    }

}
