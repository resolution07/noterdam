<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Symfony\Component\HttpFoundation\Response;

class StoreInertiaPreviousUrl extends Middleware
{
    /**
     * Laravel запоминает предыдущий URL только для не-AJAX GET-запросов,
     * поэтому в Inertia-приложении back() уводит на последнюю полную
     * загрузку страницы. Запоминаем переходы Inertia вручную.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (
            $request->isMethod('GET')
            && $request->header('X-Inertia')
            && ! $request->header('X-Inertia-Prefetch')
        ) {
            $request->session()->setPreviousUrl($request->fullUrl());
        }

        return $next($request);
    }
}
