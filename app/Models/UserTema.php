<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTema extends Model
{
    public $table = "user_tema";

    CONST TABLE = "user_tema";
    protected $fillable = [
        'user_id',
        'tema_id',
        'conclusao'
    ];

}
