# ChatBot Creator Studio — Cybersecurity & Multi-Tenant Data Isolation Policy

Security is a primary architectural mandate in ChatBot Creator Studio. The application incorporates multi-layered defensive controls designed to protect subscriber data, authentication sessions, payment flows, and public widget endpoints.

---

## 1. Multi-Tenant Data Isolation

In ChatBot Creator Studio, every authenticated user operates within an isolated workspace:
- **Database Ownership Enforcement**: Every SQL query for reading, modifying, or deleting chatbots, knowledge bases, configurations, and conversations enforces `user_id = :authenticated_user_id`.
- **Zero Client-Side Trust**: Subscription tiers, quotas, bot ownership, and permissions are **never** trusted from form posts, URL parameters, or client JavaScript. All authorization is resolved server-side from session tokens and database records.
- **IDOR Defense**: Insecure Direct Object References are prevented. Accessing `/studio/bots/42` when bot #42 belongs to another tenant returns an immediate 404/403 with an audit log entry.
- **Opaque Resource Identifiers**: Chatbots and conversations use UUIDs and cryptographically secure random tokens (`bin2hex(random_bytes(16))`) for public endpoints rather than auto-incrementing sequential database IDs.

---

## 2. Authentication & Session Security

- **Password Storage**: Encrypted with `password_hash()` utilizing **Argon2id** (`PASSWORD_ARGON2ID`) when supported by PHP, with Bcrypt fallback (`PASSWORD_BCRYPT`, cost factor 12).
- **Session Management**:
  - `session.cookie_httponly = 1` (blocks JavaScript XSS access to session cookies).
  - `session.cookie_samesite = Lax` (defends against Cross-Site Request Forgery).
  - Session regeneration (`session_regenerate_id(true)`) after login to defeat session fixation.
  - Active session destruction and cache invalidation on logout.
- **Brute Force Defense**: Login attempts are throttled by client IP and email hash (5 failed attempts maximum per 15-minute window). Every attempt is logged in `login_attempts`.
- **Password Reset Security**: Expiring single-use cryptographic tokens (1-hour validity) stored in `password_reset_tokens`. Invalidated immediately upon password change.

---

## 3. Web & API Security Defenses

### Cross-Site Request Forgery (CSRF)
All state-modifying requests (`POST`, `PUT`, `DELETE`) require a cryptographically verified anti-CSRF token verified via `CsrfMiddleware`:
```html
<input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
```
AJAX and Fetch requests pass the token via the `X-CSRF-Token` HTTP header.

### Cross-Site Scripting (XSS)
- All user-controlled text rendered in HTML is sanitized through `htmlspecialchars($val, ENT_QUOTES, 'UTF-8')`.
- Rich HTML answers are scrubbed to strip `<script>`, `<iframe>`, `onload`, and harmful attributes before storage.

### SQL Injection Protection
- The entire application uses **PHP PDO prepared statements** with parameterized queries (`:param`).
- Zero direct string concatenation is permitted in SQL queries.

### Encrypted Secrets Storage
- Third-party API keys (e.g., custom OpenAI keys) are stored encrypted at rest using **AES-256-CBC** with HMAC-SHA256 authenticated encryption via `App\Core\Security::encrypt()`.

---

## 4. Public Chatbot Widget & Embed Security

- **Shadow DOM Isolation**: The embeddable widget (`/embed/{token}.js`) mounts inside an isolated Shadow DOM container to prevent host website CSS from breaking the widget and to prevent malicious host scripts from altering widget UI.
- **Domain Whitelisting**: If a chatbot has domain restriction enabled (`visibility = 'domain_restricted'`), the API verifies the `Origin` and `Referer` headers against allowed domains in `chatbot_allowed_domains`.
- **Public API Rate Limiting**: Visitor chat messages are throttled at 30 requests per minute per IP address (`ApiController::sendMessage`).
- **Prompt Injection Defense**: When an external AI model is configured, user messages and retrieved knowledge are formatted with strict system boundaries, preventing arbitrary visitor prompts from modifying system instructions.

---

## 5. PayPal Payment & Webhook Security

- **Server-Side Validation**: Paid subscription status is granted **strictly** after server-side verification with PayPal REST API endpoints or signed webhook events.
- **Webhook Signature Verification**: Webhook payloads are verified using PayPal certificates (`App\Services\PayPalService::verifyWebhookSignature()`).
- **Idempotency**: Webhook events are tracked in `paypal_webhook_events` by event ID. Duplicate events are acknowledged gracefully without re-processing payments.
- **PCI Compliance**: Zero credit card details are ever accepted, processed, or stored on ChatBot Creator Studio servers. All payments occur within PayPal's PCI-DSS compliant vault.

---

## 6. Audit Logging & Security Trail

All administrative operations, login attempts, user updates, and plan adjustments are recorded immutably in `audit_logs` with timestamps, IP addresses, and user agents. Administrators can inspect these records in the **Security & Audit Logs** section of the Admin Center (`/admin/audit-logs`).
