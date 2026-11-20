<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BasicAuth
{
    public function handle(Request $request, Closure $next)
    {
        $user = config('services.basic_auth.user');
        $pass = config('services.basic_auth.password');

        // Fail closed: if no credentials are configured, nobody gets in.
        if (! $user || ! $pass) {
            abort(503, 'Access is not configured.');
        }

        $givenUser = $request->getUser() ?? '';
        $givenPass = $request->getPassword() ?? '';

        if (! hash_equals($user, $givenUser) || ! hash_equals($pass, $givenPass)) {
            return response('Unauthorized', 401, ['WWW-Authenticate' => 'Basic realm="Invoices"']);
        }

        return $next($request);
    }
}