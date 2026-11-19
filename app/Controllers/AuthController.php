<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Security;
use App\Core\Session;
use App\Core\Validator;

class AuthController
{
    public function showRegister(Request $request): Response
    {
        return Response::view('auth/register', [
            'title' => 'Create Account - ChatBot Creator Studio',
        ], 'layouts/auth');
    }

    public function register(Request $request): Response
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:2|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'password_confirmation' => 'required|matches:password',
        ]);

        if ($validator->fails()) {
            $validator->flash();
            return Response::back();
        }

        $email = strtolower(trim((string)$request->input('email')));
        $name = trim((string)$request->input('name'));
        $password = (string)$request->input('password');

        $freePlan = Database::fetchOne("SELECT id FROM plans WHERE slug = 'free' LIMIT 1");
        $planId = $freePlan ? (int)$freePlan['id'] : 1;

        $userId = Database::transaction(function () use ($name, $email, $password, $planId) {
            $id = Database::insert('users', [
                'uuid' => Security::generateUuid(),
                'name' => $name,
                'email' => $email,
                'password_hash' => Security::hashPassword($password),
                'plan_id' => $planId,
                'status' => 'active',
                'email_verified_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            // Assign standard user role
            $userRole = Database::fetchOne("SELECT id FROM roles WHERE slug = 'user' LIMIT 1");
            if ($userRole) {
                Database::insert('user_roles', [
                    'user_id' => $id,
                    'role_id' => $userRole['id'],
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            // Create initial audit log
            Database::insert('audit_logs', [
                'user_id' => $id,
                'action' => 'user_registered',
                'entity_type' => 'user',
                'entity_id' => $id,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            return $id;
        });

        // Log the user in
        $user = Database::fetchOne("SELECT * FROM users WHERE id = :id", ['id' => $userId]);
        Auth::login($user);

        Session::flash('success', 'Welcome to ChatBot Creator Studio! Your account is active and Free plan is provisioned.');
        return Response::redirect('/studio');
    }

    public function showLogin(Request $request): Response
    {
        return Response::view('auth/login', [
            'title' => 'Sign In - ChatBot Creator Studio',
        ], 'layouts/auth');
    }

    public function login(Request $request): Response
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            $validator->flash();
            return Response::back();
        }

        $email = strtolower(trim((string)$request->input('email')));
        $password = (string)$request->input('password');

        // Throttling: max 5 attempts per 15 minutes per IP
        $rateLimitKey = 'login_attempt:' . $request->ip() . ':' . md5($email);
        if (!Security::checkRateLimit($rateLimitKey, 5, 900)) {
            Session::flash('error', 'Too many failed login attempts. Please wait 15 minutes before trying again.');
            return Response::back();
        }

        if (!Auth::attempt($email, $password)) {
            // Log failed attempt
            Database::insert('login_attempts', [
                'ip_address' => $request->ip(),
                'email' => $email,
                'attempted_at' => date('Y-m-d H:i:s'),
                'success' => 0,
            ]);

            Session::flash('error', 'Invalid email address or password.');
            return Response::back();
        }

        // Record successful login
        $user = Auth::user();
        Database::insert('login_attempts', [
            'ip_address' => $request->ip(),
            'email' => $email,
            'attempted_at' => date('Y-m-d H:i:s'),
            'success' => 1,
        ]);

        Database::insert('audit_logs', [
            'user_id' => $user['id'],
            'action' => 'user_login',
            'entity_type' => 'user',
            'entity_id' => $user['id'],
            'ip_address' => $request->ip(),
            'user_agent' => substr($request->userAgent(), 0, 500),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $intended = Session::get('intended_url', '/studio');
        Session::remove('intended_url');

        Session::flash('success', "Welcome back, {$user['name']}!");
        return Response::redirect($intended);
    }

    public function logout(Request $request): Response
    {
        Auth::logout();
        Session::flash('info', 'You have been signed out successfully.');
        return Response::redirect('/login');
    }

    public function showForgotPassword(Request $request): Response
    {
        return Response::view('auth/forgot-password', [
            'title' => 'Reset Password - ChatBot Creator Studio',
        ], 'layouts/auth');
    }

    public function sendResetLink(Request $request): Response
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            $validator->flash();
            return Response::back();
        }

        $email = strtolower(trim((string)$request->input('email')));
        $user = Database::fetchOne("SELECT id, name FROM users WHERE email = :email", ['email' => $email]);

        if ($user) {
            $token = Security::generateToken(32);
            $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour

            Database::insert('password_reset_tokens', [
                'email' => $email,
                'token' => $token,
                'expires_at' => $expiresAt,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            // Save reset link flash for testing / development
            $resetUrl = url("/reset-password?token={$token}&email=" . urlencode($email));
            Session::flash('demo_reset_link', $resetUrl);
        }

        Session::flash('success', 'If an account exists with that email address, a password reset link has been generated.');
        return Response::back();
    }

    public function showResetPassword(Request $request): Response
    {
        $token = (string)$request->query('token', '');
        $email = (string)$request->query('email', '');

        return Response::view('auth/reset-password', [
            'title' => 'Set New Password - ChatBot Creator Studio',
            'token' => $token,
            'email' => $email,
        ], 'layouts/auth');
    }

    public function resetPassword(Request $request): Response
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:8',
            'password_confirmation' => 'required|matches:password',
        ]);

        if ($validator->fails()) {
            $validator->flash();
            return Response::back();
        }

        $email = strtolower(trim((string)$request->input('email')));
        $token = (string)$request->input('token');
        $password = (string)$request->input('password');

        $reset = Database::fetchOne(
            "SELECT * FROM password_reset_tokens WHERE email = :email AND token = :token AND expires_at > NOW() ORDER BY id DESC LIMIT 1",
            ['email' => $email, 'token' => $token]
        );

        if (!$reset) {
            Session::flash('error', 'This password reset link is invalid or has expired.');
            return Response::back();
        }

        Database::transaction(function () use ($email, $password) {
            Database::update(
                'users',
                ['password_hash' => Security::hashPassword($password), 'updated_at' => date('Y-m-d H:i:s')],
                'email = :email',
                ['email' => $email]
            );

            // Invalidate tokens
            Database::delete('password_reset_tokens', 'email = :email', ['email' => $email]);
        });

        Session::flash('success', 'Password reset successfully. You can now log in with your new password.');
        return Response::redirect('/login');
    }

    public function verifyEmail(Request $request): Response
    {
        $token = trim((string)$request->query('token', ''));

        if ($token === '') {
            Session::flash('error', 'Verification token is missing.');
            return Response::redirect('/login');
        }

        $record = Database::fetchOne(
            "SELECT * FROM email_verification_tokens WHERE token = :token AND expires_at > NOW() LIMIT 1",
            ['token' => $token]
        );

        if (!$record) {
            Session::flash('error', 'This verification link is invalid or has expired.');
            return Response::redirect('/login');
        }

        $userId = (int)$record['user_id'];

        Database::transaction(function () use ($userId, $token) {
            Database::update('users', [
                'email_verified_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ], 'id = :id', ['id' => $userId]);

            Database::delete('email_verification_tokens', 'token = :token', ['token' => $token]);

            Database::insert('audit_logs', [
                'user_id' => $userId,
                'action' => 'email_verified',
                'entity_type' => 'user',
                'entity_id' => $userId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        });

        Session::flash('success', 'Your email address has been verified successfully!');
        return Response::redirect(Auth::check() ? '/studio' : '/login');
    }
}
