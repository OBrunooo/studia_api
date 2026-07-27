<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GerandoTema extends Model
{
    protected $table = 'gerando_temas';
    protected $fillable = [
        'tema',
        'user_id',
    ];

}
