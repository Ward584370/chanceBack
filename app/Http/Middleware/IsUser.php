<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class IsUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {   $user = Auth::user(); 
        if ($user && $user->role === 0) { // 0 تعني user
            return $next($request);
        }

        return response()->json(['error' => 'Unauthorized'], 403);
       
    }
}
