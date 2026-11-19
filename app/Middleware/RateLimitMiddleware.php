<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Security;

class RateLimitMiddleware
{
    private int $maxAttempts;
    private int $decaySeconds;

    public function __construct(int $maxAttempts = 60, int $decaySeconds = 60)
    {
        $this->maxAttempts = $maxAttempts;
        $this->decaySeconds = $decaySeconds;
    }

    public function handle(Request $request, callable $next): Response
    {
        $key = 'rate_limit:' . $request->ip() . ':' . $request->path();

        if (!Security::checkRateLimit($key, $this->maxAttempts, $this->decaySeconds)) {
            if ($request->isAjax() || $request->isJson()) {
                return Response::json([
                    'success' => false,
                    'message' => 'Too many requests. Please slow down and try again later.',
                ], 429);
            }

            return Response::make('429 Too Many Requests', 429, [
                'Retry-After' => (string)$this->decaySeconds,
            ]);
        }

        return $next($request);
    }
}
