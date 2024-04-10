<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ForceBasicAuth
{
    /**
     * This middleware forces to have a basic authentication without actually login the user with Laravel.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->headers->has('php-auth-user') || ! $request->headers->has('php-auth-pw')) {
            Log::debug('Missing basic auth headers');
            // wget won't send auth headers unless WWW-Authenticate is present in the 401 response
            // source: https://www.gnu.org/software/wget/manual/wget.html#index-authentication-2
            abort(401, headers: ['WWW-Authenticate' => 'Basic']);
        }

        return $next($request);
    }
}
