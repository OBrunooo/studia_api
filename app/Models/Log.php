<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $table = "logs";
    CONST TABLE = "logs";
    protected $fillable = [
        "action",
        "message",
        "file",
        "line",
        "type",
        "user_id",
        "data"
    ];
}
