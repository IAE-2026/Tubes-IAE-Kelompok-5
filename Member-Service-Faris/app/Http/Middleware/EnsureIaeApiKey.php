<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIaeApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->header('X-IAE-KEY') !== config('services.iae.api_key')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or missing API key',
                'errors' => null,
            ], 401);
        }

        return $next($request);
    }
}
