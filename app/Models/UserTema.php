<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTema extends Model
{
    protected $table = "user_tema";

    protected $fillable = [
        'user_id',
        'tema_id',
        'conclusao'
    ];

}
