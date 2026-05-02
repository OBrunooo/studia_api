<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserConclusaoConjunto extends Model
{
    protected $table = "User_conclusao_conjunto";

    protected $fillable = [
        'conjunto_id',
        'user_id',
        'conclusao'
    ];    
}
