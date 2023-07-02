<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->isLocal()) {
            return $next($request);
        }

        $apiKey = $request->query('api_key', $request->get('api_key'));

        if (!$apiKey || $apiKey !== config('auth.api_key')) {
            abort(403, 'Invalid or missing `api_key`');
        }

        return $next($request);
    }
}
