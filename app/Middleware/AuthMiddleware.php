<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class AuthMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        if (!Auth::check()) {
            if ($request->isAjax() || $request->isJson()) {
                return Response::json(['success' => false, 'message' => 'Unauthenticated.'], 401);
            }
            Session::flash('error', 'Please log in to access this page.');
            Session::set('intended_url', $request->uri());
            return Response::redirect('/login');
        }

        $user = Auth::user();
        if ($user && $user['status'] === 'suspended') {
            Auth::logout();
            Session::flash('error', 'Your account has been suspended. Please contact support.');
            return Response::redirect('/login');
        }

        return $next($request);
    }
}
