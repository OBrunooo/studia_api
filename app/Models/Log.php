<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $table = "logs";
    protected $fillable = [
        "action",
        "message",
        "file",
        "line",
        "type",
        "user_id",
        "data"
    ];

    public static function info(string $action, string $message, array $data = []): void
    {
        self::create([
            "action" => $action,
            "message" => $message,
            "data" => json_encode($data),
        ]);
    }

    public static function error(string $action, string $message, array $data = []): void
    {
        self::create([
            "action" => $action,
            "message" => $message,
            "data" => json_encode($data),
        ]);
    }
}
