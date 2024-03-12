<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class VendorAuthenticate extends Middleware
{
    protected function authenticate($request, array $guards)
    {
        if ($this->auth->guard('vendor_api')->check()) {
            return $this->auth->shouldUse('vendor_api');
        }
        $this->unauthenticated($request, ['admin']);
    }
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return route('vendor_login');
        }
    }
}
