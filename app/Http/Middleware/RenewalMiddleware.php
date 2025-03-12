<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RenewalMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (myHotel()->status != 'active') {
            // If the OTP doesn't exist in the session,
            // redirect the user to the OTP page.
            return redirect()->route('renewal', myHotel()->code);
        }
        return $next($request);
    }
}
