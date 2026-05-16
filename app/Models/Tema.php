<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Tema extends Model
{
    protected $table = "temas";

    protected $fillable = [
        "nome", 
        "conjunto1_id", 
        "conjunto2_id", 
        "conjunto3_id", 
        "conjunto4_id", 
        "conjunto5_id",
        "conjunto6_id",
        "conjunto7_id"
    ];

    public function conjuntosTema() {
        try {   
            $ids = [
                $this->conjunto1_id,
                $this->conjunto2_id,
                $this->conjunto3_id,
                $this->conjunto4_id,
                $this->conjunto5_id,
                $this->conjunto6_id,
                $this->conjunto7_id,
            ];

            return $ids;
        } catch (\Exception $e) {
            return response()->json(["error" => "Erro ao listar conjuntos"], 500);
        }
    }
}
