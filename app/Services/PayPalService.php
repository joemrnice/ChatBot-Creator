<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Application;
use App\Core\Database;
use RuntimeException;

class PayPalService
{
    private string $clientId;
    private string $clientSecret;
    private string $baseUrl;
    private string $webhookId;
    private string $currency;

    public function __construct()
    {
        $config = Application::config('paypal', []);
        $mode = $config['mode'] ?? 'sandbox';
        $this->clientId = $config['client_id'] ?? '';
        $this->clientSecret = $config['client_secret'] ?? '';
        $this->webhookId = $config['webhook_id'] ?? '';
        $this->currency = $config['currency'] ?? 'USD';
        $this->baseUrl = $config['endpoints'][$mode] ?? 'https://api-m.sandbox.paypal.com';
    }

    public function isConfigured(): bool
    {
        return !empty($this->clientId) && !empty($this->clientSecret);
    }

    /**
     * Get OAuth2 Access Token from PayPal
     */
    public function getAccessToken(): string
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('PayPal credentials are not configured in .env or settings.');
        }

        $ch = curl_init($this->baseUrl . '/v1/oauth2/token');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
            CURLOPT_USERPWD => "{$this->clientId}:{$this->clientSecret}",
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Accept-Language: en_US',
                'Content-Type: application/x-www-form-urlencoded',
            ],
            CURLOPT_TIMEOUT => 20,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            throw new RuntimeException("PayPal cURL Error: {$curlErr}");
        }

        $data = json_decode((string)$response, true);
        if ($httpCode !== 200 || !isset($data['access_token'])) {
            $msg = $data['error_description'] ?? $data['message'] ?? 'Failed to authenticate with PayPal';
            throw new RuntimeException("PayPal Auth Error ({$httpCode}): {$msg}");
        }

        return $data['access_token'];
    }

    /**
     * Create a PayPal Subscription for a given PayPal Plan ID
     */
    public function createSubscription(string $paypalPlanId, string $returnUrl, string $cancelUrl, ?string $customId = null): array
    {
        $token = $this->getAccessToken();

        $payload = [
            'plan_id' => $paypalPlanId,
            'application_context' => [
                'brand_name' => Application::config('app.name', 'ChatBot Creator Studio'),
                'locale' => 'en-US',
                'shipping_preference' => 'NO_SHIPPING',
                'user_action' => 'SUBSCRIBE_NOW',
                'return_url' => $returnUrl,
                'cancel_url' => $cancelUrl,
            ],
        ];

        if ($customId) {
            $payload['custom_id'] = $customId;
        }

        $ch = curl_init($this->baseUrl . '/v1/billing/subscriptions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$token}",
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_TIMEOUT => 20,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode((string)$response, true);
        if ($httpCode !== 201 || !isset($data['id'])) {
            $msg = $data['message'] ?? 'Failed to create PayPal subscription.';
            throw new RuntimeException("PayPal Subscription Creation Error ({$httpCode}): {$msg}");
        }

        // Find approval link
        $approvalUrl = null;
        if (isset($data['links']) && is_array($data['links'])) {
            foreach ($data['links'] as $link) {
                if ($link['rel'] === 'approve') {
                    $approvalUrl = $link['href'];
                    break;
                }
            }
        }

        return [
            'subscription_id' => $data['id'],
            'status' => $data['status'] ?? 'APPROVAL_PENDING',
            'approval_url' => $approvalUrl,
            'raw' => $data,
        ];
    }

    /**
     * Retrieve Subscription details from PayPal
     */
    public function getSubscription(string $subscriptionId): array
    {
        $token = $this->getAccessToken();

        $ch = curl_init($this->baseUrl . "/v1/billing/subscriptions/{$subscriptionId}");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$token}",
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_TIMEOUT => 20,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode((string)$response, true);
        if ($httpCode !== 200 || !isset($data['id'])) {
            $msg = $data['message'] ?? "Could not verify subscription {$subscriptionId}";
            throw new RuntimeException("PayPal Verification Error ({$httpCode}): {$msg}");
        }

        return $data;
    }

    /**
     * Cancel an active PayPal subscription
     */
    public function cancelPayPalSubscription(string $subscriptionId, string $reason = 'Customer cancelled subscription'): bool
    {
        $token = $this->getAccessToken();

        $ch = curl_init($this->baseUrl . "/v1/billing/subscriptions/{$subscriptionId}/cancel");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode(['reason' => $reason]),
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$token}",
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_TIMEOUT => 20,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 204;
    }

    /**
     * Verify Webhook Signature via PayPal REST API
     */
    public function verifyWebhookSignature(array $headers, string $rawBody): bool
    {
        if (empty($this->webhookId)) {
            error_log('PAYPAL_WEBHOOK_ID is empty. Cannot verify webhook signature.');
            return false;
        }

        try {
            $token = $this->getAccessToken();

            $authAlgo = $headers['paypal-auth-algo'] ?? $headers['PAYPAL-AUTH-ALGO'] ?? '';
            $certUrl = $headers['paypal-cert-url'] ?? $headers['PAYPAL-CERT-URL'] ?? '';
            $transmissionId = $headers['paypal-transmission-id'] ?? $headers['PAYPAL-TRANSMISSION-ID'] ?? '';
            $transmissionSig = $headers['paypal-transmission-sig'] ?? $headers['PAYPAL-TRANSMISSION-SIG'] ?? '';
            $transmissionTime = $headers['paypal-transmission-time'] ?? $headers['PAYPAL-TRANSMISSION-TIME'] ?? '';

            $verificationPayload = [
                'auth_algo' => $authAlgo,
                'cert_url' => $certUrl,
                'transmission_id' => $transmissionId,
                'transmission_sig' => $transmissionSig,
                'transmission_time' => $transmissionTime,
                'webhook_id' => $this->webhookId,
                'webhook_event' => json_decode($rawBody, true),
            ];

            $ch = curl_init($this->baseUrl . '/v1/notifications/verify-webhook-signature');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($verificationPayload),
                CURLOPT_HTTPHEADER => [
                    "Authorization: Bearer {$token}",
                    'Content-Type: application/json',
                ],
                CURLOPT_TIMEOUT => 15,
                CURLOPT_SSL_VERIFYPEER => true,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $data = json_decode((string)$response, true);
            return $httpCode === 200 && isset($data['verification_status']) && $data['verification_status'] === 'SUCCESS';
        } catch (\Throwable $e) {
            error_log('Webhook signature verification error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Process Webhook Event with Idempotency
     */
    public function handleWebhookEvent(string $rawBody, array $headers): array
    {
        $event = json_decode($rawBody, true);
        if (!$event || !isset($event['id']) || !isset($event['event_type'])) {
            return ['success' => false, 'message' => 'Malformed webhook payload'];
        }

        $eventId = $event['id'];
        $eventType = $event['event_type'];

        // Check if event already processed (idempotency)
        $existing = Database::fetchOne(
            "SELECT id, status FROM paypal_webhook_events WHERE event_id = :id LIMIT 1",
            ['id' => $eventId]
        );

        if ($existing) {
            return ['success' => true, 'message' => 'Event already received / processed (idempotent skip)'];
        }

        // Record incoming event
        $recordId = Database::insert('paypal_webhook_events', [
            'event_id' => $eventId,
            'event_type' => $eventType,
            'summary' => $event['summary'] ?? '',
            'resource_type' => $event['resource_type'] ?? '',
            'status' => 'received',
            'raw_payload' => $rawBody,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Process known subscription events
        try {
            $resource = $event['resource'] ?? [];
            switch ($eventType) {
                case 'BILLING.SUBSCRIPTION.ACTIVATED':
                case 'BILLING.SUBSCRIPTION.UPDATED':
                    $subId = $resource['id'] ?? null;
                    if ($subId) {
                        $this->syncSubscriptionState($subId, 'active', $resource);
                    }
                    break;

                case 'BILLING.SUBSCRIPTION.CANCELLED':
                    $subId = $resource['id'] ?? null;
                    if ($subId) {
                        $this->syncSubscriptionState($subId, 'cancelled', $resource);
                    }
                    break;

                case 'BILLING.SUBSCRIPTION.EXPIRED':
                    $subId = $resource['id'] ?? null;
                    if ($subId) {
                        $this->syncSubscriptionState($subId, 'expired', $resource);
                    }
                    break;

                case 'BILLING.SUBSCRIPTION.SUSPENDED':
                    $subId = $resource['id'] ?? null;
                    if ($subId) {
                        $this->syncSubscriptionState($subId, 'suspended', $resource);
                    }
                    break;

                case 'PAYMENT.SALE.COMPLETED':
                    $this->recordSalePayment($resource);
                    break;
            }

            Database::update(
                'paypal_webhook_events',
                ['status' => 'processed', 'processed_at' => date('Y-m-d H:i:s')],
                'id = :id',
                ['id' => $recordId]
            );

            return ['success' => true, 'message' => "Event {$eventType} processed."];
        } catch (\Throwable $e) {
            Database::update(
                'paypal_webhook_events',
                ['status' => 'failed'],
                'id = :id',
                ['id' => $recordId]
            );
            error_log("Failed to process webhook event {$eventId}: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function syncSubscriptionState(string $paypalSubId, string $status, array $resource): void
    {
        $sub = Database::fetchOne(
            "SELECT * FROM subscriptions WHERE paypal_subscription_id = :id LIMIT 1",
            ['id' => $paypalSubId]
        );

        if (!$sub) {
            return;
        }

        $nextBillingTime = $resource['billing_info']['next_billing_time'] ?? null;
        $periodEnd = $nextBillingTime ? date('Y-m-d H:i:s', strtotime($nextBillingTime)) : null;

        $updateData = ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')];
        if ($periodEnd) {
            $updateData['current_period_end'] = $periodEnd;
        }
        if ($status === 'cancelled') {
            $updateData['cancelled_at'] = date('Y-m-d H:i:s');
        }

        Database::update('subscriptions', $updateData, 'id = :id', ['id' => $sub['id']]);

        // If cancelled or expired, downgrade user plan to free
        if (in_array($status, ['cancelled', 'expired'], true)) {
            $freePlan = Database::fetchOne("SELECT id FROM plans WHERE slug = 'free' LIMIT 1");
            if ($freePlan) {
                Database::update('users', ['plan_id' => $freePlan['id']], 'id = :id', ['id' => $sub['user_id']]);
            }
        }
    }

    private function recordSalePayment(array $resource): void
    {
        $txId = $resource['id'] ?? null;
        $billingAgreementId = $resource['billing_agreement_id'] ?? null; // paypal_subscription_id
        $amount = (float)($resource['amount']['total'] ?? 0.00);
        $currency = $resource['amount']['currency'] ?? 'USD';

        if (!$txId) {
            return;
        }

        $sub = null;
        $userId = null;
        if ($billingAgreementId) {
            $sub = Database::fetchOne(
                "SELECT id, user_id FROM subscriptions WHERE paypal_subscription_id = :sub_id LIMIT 1",
                ['sub_id' => $billingAgreementId]
            );
            if ($sub) {
                $userId = (int)$sub['user_id'];
            }
        }

        if ($userId) {
            Database::insert('payment_transactions', [
                'subscription_id' => $sub ? $sub['id'] : null,
                'user_id' => $userId,
                'transaction_id' => $txId,
                'payment_provider' => 'paypal',
                'amount' => $amount,
                'currency' => $currency,
                'status' => 'completed',
                'raw_payload' => json_encode($resource),
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            Database::insert('billing_history', [
                'user_id' => $userId,
                'subscription_id' => $sub ? $sub['id'] : null,
                'invoice_number' => 'INV-' . strtoupper(substr(md5($txId), 0, 8)),
                'amount' => $amount,
                'currency' => $currency,
                'description' => 'Subscription renewal payment via PayPal',
                'billing_date' => date('Y-m-d H:i:s'),
                'status' => 'paid',
            ]);
        }
    }
}
