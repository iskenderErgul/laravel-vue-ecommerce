<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{

    public function handle(Request $request, Closure $next): Response
    {
        if(auth()->check() && auth()->user()->isAdmin == 1) {

            return $next($request);
        }

        return redirect()->route('home')->with('error','Access denided.You are not admin.');
    }
}

