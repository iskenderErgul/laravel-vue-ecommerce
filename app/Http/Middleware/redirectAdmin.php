<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class redirectAdmin
{

    public function handle(Request $request, Closure $next,$guard=null): Response
    {
        if (Auth::guard($guard)->check() && Auth::user()->isAdmin == 1) {
            return redirect('admin.dashboard');
        }
        return $next($request);
    }
}
