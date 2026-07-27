<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Questoes extends Model
{
    protected $table = "questoes";
    protected $fillable = [
        'questao',
        'alternativa1',
        'alternativa2',
        'alternativa3',
        'alternativa4',
    ];
}
