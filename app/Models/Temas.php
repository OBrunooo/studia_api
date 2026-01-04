<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Temas extends Model
{
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
                 
}
