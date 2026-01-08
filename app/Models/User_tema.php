<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User_tema extends Model
{
    protected $table = "user_tema";

    protected $fillable = [
        'user_id',
        'tema_id',
        'conclusao'
    ];

}
