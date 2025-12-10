<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConjuntoQuestoes extends Model
{
    protected $table = "conjunto_questoes";


        protected $fillable = [
        'user_id',
        'questao1_id',
        'questao2_id',
        'questao3_id',
        'questao4_id',
        'questao5_id',
        'questao6_id',
        'questao7_id',
    ];
}
