<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Return null so Laravel never tries to resolve a 'login' named route.
     * The AuthenticationException is then caught by our exception handler,
     * which returns a JSON 401 for all api/* routes.
     */
    protected function redirectTo(Request $request): ?string
    {
        return null;
    }
}
