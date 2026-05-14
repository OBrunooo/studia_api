<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tema;

class TemasController extends Controller {
    public function listarConjuntosTema(Request $request) {
        try {
            $request->validate([
                "tema_id" => "required|exists:temas,id"
            ], [
                "tema_id.required" => "O campo tema_id é obrigatório",
                "tema_id.exists" => "O tema_id informado não existe"
            ]);
            $tema = Tema::find($request->tema_id);
            $conjuntos = $tema->listarConjuntos();
            return response()->json(["conjuntos" => $conjuntos]);
        } catch (\Exception $e) {
            return response()->json(["error" => "Erro ao listar conjuntos"], 500);
        }
    }
}