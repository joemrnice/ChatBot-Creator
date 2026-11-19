<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class AdminMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        if (!Auth::check()) {
            if ($request->isAjax() || $request->isJson()) {
                return Response::json(['success' => false, 'message' => 'Unauthenticated.'], 401);
            }
            Session::flash('error', 'Administrator login required.');
            return Response::redirect('/login');
        }

        if (!Auth::isAdmin()) {
            if ($request->isAjax() || $request->isJson()) {
                return Response::json(['success' => false, 'message' => 'Unauthorized. Admin privileges required.'], 403);
            }
            return Response::view('public/403', ['title' => '403 Forbidden - Admin Access Required'], 'layouts/main')
                ->setStatusCode(403);
        }

        return $next($request);
    }
}
