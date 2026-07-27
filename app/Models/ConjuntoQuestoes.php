<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConjuntoQuestoes extends Model
{
    protected $table = "conjunto_questoes";

    protected $fillable = [
        'questao1_id',
        'questao2_id',
        'questao3_id',
        'questao4_id',
        'questao5_id',
        'questao6_id',
        'questao7_id',
    ];

    public function questoesConjunto() {
        $questoes = [
            $this->questao1_id,
            $this->questao2_id,
            $this->questao3_id,
            $this->questao4_id,
            $this->questao5_id,
            $this->questao6_id,
            $this->questao7_id,
        ];
        return $questoes;
    }
}
