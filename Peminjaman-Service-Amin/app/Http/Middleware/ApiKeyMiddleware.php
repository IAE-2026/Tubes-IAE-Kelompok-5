<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-IAE-KEY');

        if (!$apiKey || $apiKey !== (string) env('API_KEY')) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized: Invalid or missing API Key',
                'errors'  => null,
            ], 401);
        }

        return $next($request);
    }
}