<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $origin = $request->header('Origin');
        $allowedOrigins = [
            'https://language-learning-xi.vercel.app',
            'http://localhost:3000'
        ];

        // Check if origin matches one of the allowed origins or wildcard patterns
        $isAllowed = false;
        if ($origin) {
            if (in_array($origin, $allowedOrigins)) {
                $isAllowed = true;
            } elseif (preg_match('/^https:\/\/.*\.vercel\.app$/i', $origin)) {
                $isAllowed = true;
            }
        }

        // Handle preflight OPTIONS request
        if ($request->isMethod('OPTIONS')) {
            $headers = [
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
                'Access-Control-Allow-Headers' => $request->header('Access-Control-Request-Headers', '*'),
                'Access-Control-Allow-Credentials' => 'true',
                'Access-Control-Max-Age' => '3600',
                'Access-Control-Expose-Headers' => 'Location, Authorization, Content-Type',
            ];
            
            if ($isAllowed) {
                $headers['Access-Control-Allow-Origin'] = $origin;
            } else {
                $headers['Access-Control-Allow-Origin'] = 'http://localhost:3000';
            }

            return response('', 200, $headers);
        }

        $response = $next($request);

        if ($isAllowed) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', '*');
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Max-Age', '3600');
            $response->headers->set('Access-Control-Expose-Headers', 'Location, Authorization, Content-Type');
        }

        return $response;
    }
}
