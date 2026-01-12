<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Temas\TemasService;
use Illuminate\Support\Facades\Validator;

class TemasController extends Controller
{
    protected $temasService;

    public function __construct (TemasService $temasService) {
        $this->temasService = $temasService;
    }

    public function temas() {
        return $this->temasService->listarTemas();
    }

    public function questoes(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer'
        ], [
            'id.required' => 'Id é obrigatório',
            'id.integer'  => 'Id deve ser um número inteiro'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'   => 'error',
                'mensagem' => $validator->errors()->first()
            ], 422);
        }
        $id = $request->query("id");
        return $this->temasService->questoesTema($id);
    }

    public function conclusao(Request $request) {
        $id = $request->query("id");

        return json_encode($this->temasService->concluirConjunto($id));
    }
}
