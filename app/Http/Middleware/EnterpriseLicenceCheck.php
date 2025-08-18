<?php

namespace App\Http\Middleware;

use App\Logic\LicenceVerification;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnterpriseLicenceCheck
{
    public function __construct(private readonly LicenceVerification $licenceVerification) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $systemId = $request->headers->get('php-auth-user');
        $secret = $request->headers->get('php-auth-pw');

        if ($this->licenceVerification->enterpriseCheck($systemId, $secret)) {
            return $next($request);
        }
        abort(401);
    }
}
