<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Only trusted servers (the student site) may call this API.
 * They must send the shared secret in the X-Service-Token header.
 */
class RequireServiceToken
{
    public function handle(Request $request, Closure $next)
    {
        $expected = (string) env('BACKEND_SERVICE_TOKEN', '');

        if ($expected === '') {
            Log::error('BACKEND_SERVICE_TOKEN is not set; refusing all API requests.');

            return response()->json(['message' => 'Service is not configured.'], 503);
        }

        $provided = (string) $request->header('X-Service-Token', '');

        if ($provided === '' || !hash_equals($expected, $provided)) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        return $next($request);
    }
}
