<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class VerificaToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
public function handle(Request $request, Closure $next): Response
{
    try {
        // ISSO usa o token enviado pelo Flutter
        $user = $request->user('sanctum');

        if ($user) {
            return $next($request);
        }

        return response()->json([
            'error' => 'Token inválido ou ausente'
        ], 401);

    } catch (\Throwable $th) {
        return response()->json([
            'error' => 'Erro ao validar token'
        ], 500);
    }
}

}
