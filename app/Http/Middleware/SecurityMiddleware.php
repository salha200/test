<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\App;

class SecurityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Rate Limiting: حماية API من هجمات DDoS
        if ($this->isRateLimited($request)) {
            return response()->json(['message' => 'Too many requests'], Response::HTTP_TOO_MANY_REQUESTS);
        }

        // CSRF Protection: التحقق من صحة CSRF Token
        if ($this->requiresCsrfProtection($request) && !$request->session()->token()) {
            return response()->json(['message' => 'Invalid CSRF token'], Response::HTTP_FORBIDDEN);
        }

        // تنظيف بيانات الإدخال لمنع XSS و SQL Injection
        $this->sanitizeInput($request);

        $response = $next($request);

        // إزالة Headers التي تكشف عن بصمة الخادم
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');
        $response->headers->remove('x-turbo-charged-by');

        // إضافة Security Headers
        $this->addSecurityHeaders($response);

        return $response;
    }

    /**
     * Rate Limiting: تحقق مما إذا كان المستخدم قد تجاوز الحد المسموح.
     */
    private function isRateLimited(Request $request): bool
    {
        $key = 'rate_limit:' . $request->ip();
        RateLimiter::hit($key, 60); // السماح بـ 100 طلب كل دقيقة

        return RateLimiter::tooManyAttempts($key, 100);
    }

    /**
     * CSRF Protection: التحقق مما إذا كان الطلب يتطلب حماية CSRF.
     */
    private function requiresCsrfProtection(Request $request): bool
    {
        return in_array($request->method(), ['POST', 'PUT', 'DELETE']);
    }

    /**
     * تنظيف بيانات الإدخال لمنع XSS و SQL Injection.
     */
    private function sanitizeInput(Request $request)
    {
        $input = $request->all();

        array_walk_recursive($input, function (&$value) {
            $value = htmlspecialchars(strip_tags($value));
        });

        $request->merge($input);
    }

    /**
     * إضافة Security Headers.
     */
    private function addSecurityHeaders(Response $response)
    {
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        $response->headers->set('Referrer-Policy', 'no-referrer');
        $response->headers->set('Cross-Origin-Embedder-Policy', 'require-corp');
        $response->headers->set('Content-Security-Policy', "default-src 'none'; style-src 'self'; form-action 'self'");
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // إضافة Strict-Transport-Security في بيئة الإنتاج فقط
        if (App::environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }
    }
}
