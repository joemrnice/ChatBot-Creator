<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\SubscriptionService;

class SubscriptionMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        $user = Auth::user();
        if (!$user) {
            return Response::redirect('/login');
        }

        // If path is creating a bot, verify quota
        if ($request->isMethod('POST') && str_contains($request->path(), '/studio/bots')) {
            $subscriptionService = new SubscriptionService();
            if (!$subscriptionService->canCreateChatbot((int)$user['id'])) {
                if ($request->isAjax() || $request->isJson()) {
                    return Response::json([
                        'success' => false,
                        'message' => 'You have reached your chatbot limit for your current subscription. Please upgrade to create more chatbots.',
                    ], 403);
                }
                Session::flash('error', 'You have reached your chatbot creation limit. Upgrade your subscription to create more chatbots.');
                return Response::redirect('/studio/billing');
            }
        }

        return $next($request);
    }
}
