<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $allowedOrigins = ['http://localhost:3000', 'http://127.0.0.1:3000'];
        $origin = $request->header('Origin');
        $allowOrigin = in_array($origin, $allowedOrigins) ? $origin : 'http://localhost:3000';

        error_log('CORS Middleware - Method: ' . $request->getMethod());
        error_log('CORS Middleware - Origin: ' . $origin);
        error_log('CORS Middleware - Path: ' . $request->path());

        // Handle preflight requests
        if ($request->getMethod() === 'OPTIONS') {
            error_log('CORS Middleware - Handling OPTIONS request');
            return response('', 200)
                ->header('Access-Control-Allow-Origin', $allowOrigin)
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Accept, Authorization, Content-Type, X-Requested-With, X-CSRF-TOKEN')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Max-Age', '86400');
        }

        $response = $next($request);

        error_log('CORS Middleware - Adding headers to response');
        error_log('CORS Middleware - Response status: ' . $response->getStatusCode());

        // Add CORS headers using headers->set() for better compatibility with php artisan serve
        $response->headers->set('Access-Control-Allow-Origin', $allowOrigin);
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Accept, Authorization, Content-Type, X-Requested-With, X-CSRF-TOKEN');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');

        error_log('CORS Middleware - CORS headers set: ' . $allowOrigin);

        return $response;
    }
}
