<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Questoes\QuestoesService;
use Illuminate\Support\Facades\Auth;


class QuestoesController extends Controller {

    protected $questoesService;

    public function __construct (QuestoesService $questoesService) {
        $this->questoesService = $questoesService;
    }

    public function verificarTema (Request $request) {
        $tema = $request->query("tema");

        $resultado = $this->questoesService->verificarTema($tema);
        
        if($resultado["tema"] == "INVALIDO") {
            return json_encode([
                "status" => "error",
                "message" => "Digite um tema válido"
            ]);
        }
        return redirect()->route("gerarQuestoes.get", ["tema" => $resultado["tema"], "token" => $resultado["token"]]);
    }

    public function gerarQuestoes(Request $request) {  
        $tema = $request->query("tema");
        $token = $request->query("token");

        if (isset($tema)) {
            $resultado = $this->questoesService->gerarQuestoes($tema, $token);
            return redirect()->route("verificarQuestoes.get", ["tema" => $tema, "questoes" => $resultado["questoes"], "token" => $resultado["token"]]);
        }
    }

    public function verificarQuestoes(Request $request) {
        $questoes = $request->query("questoes");
        $token = $request->query("token");
        $tema = $request->query("tema");

        if(isset($questoes)) {
            $resultado =  $this->questoesService->verificarQuestoes($questoes, $token);
            return redirect()->route("verificarConjunto.get", ["tema"=> $tema, "questoes" => $resultado['questoes'], "token" => $resultado['token']]);
        }
    }

    public function verificarConjunto(Request $request) {
        $questoes = $request->query("questoes");
        $token = $request->query("token");
        $tema = $request->query("tema");


        $resultado = $this->questoesService->verificarConjunto($questoes, $token);
        return redirect()->route("storageQuestoes.get", ["tema" => $tema, "dados" => $resultado]);
    }

    public function storage(Request $request) {
        $dados = $request->input("dados");
        return $this->questoesService->armazenarQuestoes($dados);
    }


}
