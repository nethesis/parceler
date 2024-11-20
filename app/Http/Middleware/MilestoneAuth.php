<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class MilestoneAuth
{
    /**
     * Using the Authorization header to authenticate the user, the token is passed as a Bearer token.
     * Check against the configuration milestone_token to allow the request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->header('Authorization') !== 'Bearer '.config('repositories.milestone_token')) {
            throw new UnauthorizedHttpException('Bearer', 'Unauthenticated');
        }

        return $next($request);
    }
}
