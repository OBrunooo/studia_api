<?php

namespace App\Services;
use App\Models\Log;
use App\Notifications\ApplicationErrorNotification;
use Illuminate\Support\Facades\Notification;

class LogService
{
    private static function normalizeData($data): ?string
    {
        if ($data === null) {
            return null;
        }

        if (is_string($data)) {
            return $data;
        }

        $encoded = json_encode($data, JSON_UNESCAPED_UNICODE);

        return $encoded === false ? null : $encoded;
    }

    public static function info(string $action, $user = null, string $message = null, $data = null)
    {
        try {
            Log::create([
                "action" => $action,
                "message" => $message ?? null,
                "type" => "info",
                "user_id" => $user ? $user->id : null,
                "data" => self::normalizeData($data),
            ]);
        } catch (\Throwable $th) {
            // Ignora o erro de log
        }
    }   
    public static function error(string $action, $user = null, $error, $data = null)
    {
        try {
            Log::create([
                "action" => $action,
                "message" => $error?->getMessage() ?? null,
                "file" => $error?->getFile() ?? null,
                "line" => $error?->getLine() ?? null,
                "type" => "error",
                "user_id" => $user?->id ?? null,
                "data" => self::normalizeData($data),
            ]);
        } catch (\Throwable $th) {
            // Ignora o erro de log
        }

        self::notificarErroPorEmail($action, $error);
    }

    private static function notificarErroPorEmail(string $action, $error): void
    {
        $to = config('mail.error_to');

        if (! $to || ! $error instanceof \Throwable) {
            return;
        }

        try {
            Notification::route('mail', $to)->notify(
                new ApplicationErrorNotification($error, $action, request()?->fullUrl())
            );
        } catch (\Throwable $th) {
            // Ignora falha ao enviar o e-mail de erro
        }
    }
}