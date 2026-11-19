# ChatBot Creator Studio — PayPal Subscriptions REST API Setup Guide

ChatBot Creator Studio integrates with the official **PayPal Subscriptions REST API** to support recurring billing across both **Sandbox** and **Production** environments.

---

## 1. PayPal Developer Account Setup

1. Log into the [PayPal Developer Dashboard](https://developer.paypal.com/).
2. Navigate to **Apps & Credentials**.
3. Create a **REST API App** (e.g., `ChatBot Creator Studio`).
4. Note your **Client ID** and **Client Secret**.
5. Switch between **Sandbox** (for testing) and **Live** (for production).

---

## 2. Environment Configuration

Add your PayPal credentials to your `.env` file:

```env
PAYPAL_MODE=sandbox
PAYPAL_CLIENT_ID=your_paypal_client_id_here
PAYPAL_CLIENT_SECRET=your_paypal_client_secret_here
PAYPAL_WEBHOOK_ID=your_paypal_webhook_id_here
PAYPAL_CURRENCY=USD
```

For live production deployment, change:
```env
PAYPAL_MODE=live
```

---

## 3. Plan Configuration & PayPal Plan ID Mapping

In ChatBot Creator Studio, plans (Free, Standard, Pro, Enterprise) have configurable monthly and annual pricing. To link them to PayPal:

1. In the **PayPal Developer Dashboard**, under **Subscriptions > Billing Plans**, create your billing plans or products (e.g., *Standard Monthly*, *Standard Yearly*, *Pro Monthly*, *Pro Yearly*).
2. Copy the resulting PayPal Plan IDs (e.g., `P-3RX0123456789ABCDEF`).
3. Log into the ChatBot Creator Studio **Admin Center**:
   - Navigate to **Billing & Revenue > Plans & Limits** (`/admin/plans`).
   - Locate the target plan (e.g., **Pro**).
   - Enter your **PayPal Monthly Plan ID** and **PayPal Yearly Plan ID**.
   - Click **Save Plan Changes**.

The mapping is stored in the `paypal_plans` database table and used when subscribers initiate subscription checkouts.

---

## 4. Webhook Configuration

PayPal webhooks inform the application when renewals succeed, cards fail, subscriptions are cancelled, or accounts expire.

1. In your PayPal Developer App, scroll to **Webhooks** and click **Add Webhook**.
2. **Webhook URL**: Enter your public application URL:
   ```text
   https://your-domain.com/api/v1/paypal/webhook
   ```
3. **Select Events to Subscribe**:
   - `Billing subscriptions create` (`BILLING.SUBSCRIPTION.CREATED`)
   - `Billing subscriptions activate` (`BILLING.SUBSCRIPTION.ACTIVATED`)
   - `Billing subscriptions update` (`BILLING.SUBSCRIPTION.UPDATED`)
   - `Billing subscriptions cancel` (`BILLING.SUBSCRIPTION.CANCELLED`)
   - `Billing subscriptions suspend` (`BILLING.SUBSCRIPTION.SUSPENDED`)
   - `Billing subscriptions expire` (`BILLING.SUBSCRIPTION.EXPIRED`)
   - `Payment sale completed` (`PAYMENT.SALE.COMPLETED`)
4. Save the webhook and copy the generated **Webhook ID**.
5. Paste this value into your `.env` as `PAYPAL_WEBHOOK_ID=...`.

---

## 5. Webhook Verification & Idempotency

- **Signature Verification**: Incoming webhook requests are verified by inspecting the `PAYPAL-TRANSMISSION-SIG` and authentication headers against PayPal certificate servers.
- **Idempotent Handling**: Each webhook event ID (`event.id`) is recorded in the `paypal_webhook_events` table before processing. Duplicate deliveries return an immediate `200 OK` without duplicating subscription updates or audit entries.
- **Inspection UI**: All received webhook events can be reviewed in real-time under `/admin/webhooks`.
