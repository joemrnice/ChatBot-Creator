<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Security;
use App\Core\Session;

class CsrfMiddleware
{
    // Routes exempted from CSRF check (e.g. PayPal webhooks, public embed chat message submissions)
    private array $except = [
        '/api/v1/paypal/webhook',
        '/api/v1/chat/*',
    ];

    public function handle(Request $request, callable $next): Response
    {
        $method = $request->method();

        // Safe methods do not require CSRF tokens
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $next($request);
        }

        $path = $request->path();
        foreach ($this->except as $pattern) {
            $regex = '#^' . str_replace('\*', '.*', preg_quote($pattern, '#')) . '$#i';
            if (preg_match($regex, $path)) {
                return $next($request);
            }
        }

        $token = $request->input('csrf_token')
            ?? $request->header('X-CSRF-Token')
            ?? $request->header('X-XSRF-Token');

        if (!Security::validateCsrfToken($token)) {
            if ($request->isAjax() || $request->isJson()) {
                return Response::json(['success' => false, 'message' => 'CSRF token mismatch. Please refresh and try again.'], 419);
            }
            Session::flash('error', 'Your session expired or token mismatch. Please try again.');
            return Response::back();
        }

        return $next($request);
    }
}
