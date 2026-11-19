<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\PayPalService;
use App\Services\SubscriptionService;

class BillingController
{
    private SubscriptionService $subscriptionService;
    private PayPalService $payPalService;

    public function __construct()
    {
        $this->subscriptionService = new SubscriptionService();
        $this->payPalService = new PayPalService();
    }

    public function index(Request $request): Response
    {
        $userId = (int)Auth::id();
        $user = Auth::user();
        $currentPlan = $this->subscriptionService->getUserPlan($userId);
        $allPlans = $this->subscriptionService->getAllActivePlans();

        $activeSubscription = Database::fetchOne(
            "SELECT * FROM subscriptions WHERE user_id = :uid AND status = 'active' LIMIT 1",
            ['uid' => $userId]
        );

        $invoices = Database::fetchAll(
            "SELECT * FROM billing_history WHERE user_id = :uid ORDER BY billing_date DESC LIMIT 20",
            ['uid' => $userId]
        );

        $transactions = Database::fetchAll(
            "SELECT * FROM payment_transactions WHERE user_id = :uid ORDER BY created_at DESC LIMIT 10",
            ['uid' => $userId]
        );

        $isPayPalConfigured = $this->payPalService->isConfigured();

        return Response::view('studio/billing/index', [
            'title' => 'Billing & Subscription - Personal Studio',
            'user' => $user,
            'currentPlan' => $currentPlan,
            'plans' => $allPlans,
            'activeSubscription' => $activeSubscription,
            'invoices' => $invoices,
            'transactions' => $transactions,
            'isPayPalConfigured' => $isPayPalConfigured,
        ], 'layouts/studio');
    }

    public function subscribe(Request $request): Response
    {
        $userId = (int)Auth::id();
        $planSlug = (string)$request->param('planSlug');
        $interval = (string)$request->input('interval', 'month');

        $targetPlan = Database::fetchOne("SELECT * FROM plans WHERE slug = :slug AND is_active = 1 LIMIT 1", ['slug' => $planSlug]);
        if (!$targetPlan) {
            Session::flash('error', 'Selected subscription plan not found.');
            return Response::redirect('/studio/billing');
        }

        // If switching to Free plan
        if ($targetPlan['slug'] === 'free') {
            $this->subscriptionService->assignPlan($userId, (int)$targetPlan['id'], null, 'month');
            Session::flash('info', 'Your account has been switched to the Free plan.');
            return Response::redirect('/studio/billing');
        }

        // Check if PayPal is configured
        if (!$this->payPalService->isConfigured()) {
            // For testing/development environment without live credentials:
            // Allow activating plan in demo mode if requested or instruct admin
            if ($request->input('demo_activate') === '1') {
                $this->subscriptionService->assignPlan($userId, (int)$targetPlan['id'], 'DEMO-SUB-' . strtoupper(bin2hex(random_bytes(6))), $interval);
                Database::insert('billing_history', [
                    'user_id' => $userId,
                    'invoice_number' => 'INV-DEMO-' . strtoupper(bin2hex(random_bytes(4))),
                    'amount' => $interval === 'year' ? $targetPlan['price_yearly'] : $targetPlan['price_monthly'],
                    'currency' => 'USD',
                    'description' => "Subscription to {$targetPlan['name']} ({$interval}ly)",
                    'billing_date' => date('Y-m-d H:i:s'),
                    'status' => 'paid',
                ]);
                Session::flash('success', "Activated {$targetPlan['name']} plan in demonstration mode.");
                return Response::redirect('/studio/billing');
            }

            Session::flash('error', 'PayPal credentials are not configured yet. An administrator must set PAYPAL_CLIENT_ID and PAYPAL_CLIENT_SECRET in settings.');
            return Response::redirect('/studio/billing');
        }

        // Look up mapped PayPal plan ID
        $billingIntervalEnum = strtoupper($interval) === 'YEAR' ? 'YEAR' : 'MONTH';
        $paypalPlan = Database::fetchOne(
            "SELECT paypal_plan_id FROM paypal_plans WHERE plan_id = :pid AND billing_interval = :bi AND status = 'ACTIVE' LIMIT 1",
            ['pid' => $targetPlan['id'], 'bi' => $billingIntervalEnum]
        );

        $paypalPlanId = $paypalPlan['paypal_plan_id'] ?? null;
        if (!$paypalPlanId) {
            Session::flash('error', "No PayPal plan mapping configured for {$targetPlan['name']} ({$interval}). Please configure PayPal plan ID in admin panel.");
            return Response::redirect('/studio/billing');
        }

        try {
            $returnUrl = url('/studio/billing/return');
            $cancelUrl = url('/studio/billing?cancelled=1');
            $customId = "user_{$userId}_plan_{$targetPlan['id']}_{$interval}";

            $result = $this->payPalService->createSubscription($paypalPlanId, $returnUrl, $cancelUrl, $customId);

            if (!empty($result['approval_url'])) {
                return Response::redirect($result['approval_url']);
            }

            throw new \RuntimeException('No PayPal approval URL returned by PayPal API.');
        } catch (\Throwable $e) {
            Session::flash('error', 'PayPal Subscription Error: ' . $e->getMessage());
            return Response::redirect('/studio/billing');
        }
    }

    public function return(Request $request): Response
    {
        $userId = (int)Auth::id();
        $subId = (string)$request->query('subscription_id');

        if (empty($subId)) {
            Session::flash('error', 'No subscription ID returned by PayPal.');
            return Response::redirect('/studio/billing');
        }

        try {
            // Server-side verification with PayPal REST API
            $subData = $this->payPalService->getSubscription($subId);

            if (in_array($subData['status'] ?? '', ['ACTIVE', 'APPROVED'], true)) {
                $paypalPlanId = $subData['plan_id'] ?? '';
                $paypalPlan = Database::fetchOne(
                    "SELECT plan_id, billing_interval FROM paypal_plans WHERE paypal_plan_id = :id LIMIT 1",
                    ['id' => $paypalPlanId]
                );

                $planId = $paypalPlan ? (int)$paypalPlan['plan_id'] : 3; // Default to Pro
                $interval = $paypalPlan && strtolower($paypalPlan['billing_interval']) === 'year' ? 'year' : 'month';

                $this->subscriptionService->assignPlan($userId, $planId, $subId, $interval);

                // Create invoice record
                $plan = Database::fetchOne("SELECT name, price_monthly, price_yearly FROM plans WHERE id = :id", ['id' => $planId]);
                $amount = $interval === 'year' ? $plan['price_yearly'] : $plan['price_monthly'];

                Database::insert('billing_history', [
                    'user_id' => $userId,
                    'invoice_number' => 'INV-' . strtoupper(substr(md5($subId), 0, 8)),
                    'amount' => $amount,
                    'currency' => 'USD',
                    'description' => "Subscription to {$plan['name']} ({$interval}ly)",
                    'billing_date' => date('Y-m-d H:i:s'),
                    'status' => 'paid',
                ]);

                Database::insert('audit_logs', [
                    'user_id' => $userId,
                    'action' => 'subscription_activated',
                    'entity_type' => 'subscription',
                    'details' => json_encode(['paypal_subscription_id' => $subId, 'plan_id' => $planId]),
                    'ip_address' => $request->ip(),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);

                Session::flash('success', "Congratulations! Your {$plan['name']} subscription has been verified and activated!");
            } else {
                Session::flash('error', "Subscription status is {$subData['status']}. Features will be unlocked upon payment confirmation.");
            }
        } catch (\Throwable $e) {
            Session::flash('error', 'Verification Failed: ' . $e->getMessage());
        }

        return Response::redirect('/studio/billing');
    }

    public function cancel(Request $request): Response
    {
        $userId = (int)Auth::id();
        $activeSub = Database::fetchOne(
            "SELECT * FROM subscriptions WHERE user_id = :uid AND status = 'active' LIMIT 1",
            ['uid' => $userId]
        );

        if (!$activeSub) {
            Session::flash('info', 'You do not have an active paid subscription to cancel.');
            return Response::redirect('/studio/billing');
        }

        if (!empty($activeSub['paypal_subscription_id'])) {
            try {
                $this->payPalService->cancelPayPalSubscription($activeSub['paypal_subscription_id']);
            } catch (\Throwable $e) {
                error_log('Failed to cancel on PayPal: ' . $e->getMessage());
            }
        }

        $this->subscriptionService->cancelSubscription($userId);

        Session::flash('success', 'Your subscription has been cancelled. Your access remains active until the end of the billing period.');
        return Response::redirect('/studio/billing');
    }
}
