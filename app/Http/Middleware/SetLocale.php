<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        // جلب اللغة من هيدر Accept-Language (ar أو en)
        $locale = $request->header('Accept-Language', 'en');

        // التأكد أن اللغة مدعومة فقط (ar أو en) لمنع أي قيم غريبة
        if (!in_array($locale, ['ar', 'en'])) {
            $locale = 'en';
        }

        // ضبط لغة التطبيق بالكامل
        app()->setLocale($locale);

        return $next($request);
    }
}