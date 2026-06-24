<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        // جلب اللغة من هيدر Accept-Language مع دعم صيغ المتصفح مثل "en-US,en;q=0.9"
        $rawLocale = $request->header('Accept-Language', 'en');
        $primaryLocale = strtolower(explode(',', explode('-', $rawLocale)[0])[0]);
        $locale = in_array($primaryLocale, ['ar', 'en']) ? $primaryLocale : 'en';


        // ضبط لغة التطبيق بالكامل
        app()->setLocale($locale);

        return $next($request);
    }
}