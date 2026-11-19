<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\OAuthService;

class OAuthController
{
    private OAuthService $oauthService;

    public function __construct()
    {
        $this->oauthService = new OAuthService();
    }

    public function redirect(Request $request): Response
    {
        $provider = (string)$request->param('provider');
        if (!in_array($provider, ['github', 'google', 'facebook'], true)) {
            Session::flash('error', 'Unsupported social login provider.');
            return Response::redirect('/login');
        }

        if (!$this->oauthService->isProviderConfigured($provider)) {
            Session::flash('error', ucfirst($provider) . ' login is not configured yet. Please configure credentials in settings or .env.');
            return Response::redirect('/login');
        }

        try {
            $url = $this->oauthService->getAuthorizationUrl($provider);
            return Response::redirect($url);
        } catch (\Throwable $e) {
            Session::flash('error', 'OAuth Redirect Error: ' . $e->getMessage());
            return Response::redirect('/login');
        }
    }

    public function callback(Request $request): Response
    {
        $provider = (string)$request->param('provider');
        $code = (string)$request->query('code');
        $state = (string)$request->query('state');

        if (empty($code) || empty($state)) {
            Session::flash('error', 'Social login was cancelled or failed.');
            return Response::redirect('/login');
        }

        try {
            $user = $this->oauthService->handleCallback($provider, $code, $state);
            Auth::login($user);

            Session::flash('success', "Logged in successfully via " . ucfirst($provider) . "!");
            return Response::redirect('/studio');
        } catch (\Throwable $e) {
            Session::flash('error', 'Social Authentication Failed: ' . $e->getMessage());
            return Response::redirect('/login');
        }
    }
}
