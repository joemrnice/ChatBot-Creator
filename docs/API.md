# ChatBot Creator Studio — API Documentation

This document describes the versioned REST API provided by ChatBot Creator Studio.

All responses follow a consistent JSON response schema:

```json
{
  "success": true,
  "message": "Human-readable status description.",
  "data": {},
  "errors": []
}
```

---

## Base URL

```text
https://your-domain.com/api/v1
```

---

## 1. Visitor Chatbot Messaging

### Submit Message to Chatbot
Processes a visitor query, matches knowledge base entries, queries configured AI providers (if enabled), records metrics, and returns the chatbot's response.

- **Endpoint**: `POST /api/v1/chat/{publicBotToken}/messages`
- **Rate Limit**: 30 requests per minute per IP
- **CORS**: `Access-Control-Allow-Origin: *`

#### Request Headers
```http
Content-Type: application/json
```

#### Request Body
```json
{
  "message": "What is your refund policy?",
  "session_id": "sid_usr_a1b2c3d4",
  "referrer": "https://example.com/checkout"
}
```

#### Success Response (`200 OK`)
```json
{
  "success": true,
  "message": "Message processed.",
  "data": {
    "content": "We offer a 30-day money-back guarantee on all subscription plans.",
    "source": "knowledge_faq",
    "suggested_questions": [
      "How do I cancel my plan?",
      "Can I switch from Monthly to Yearly?"
    ],
    "message_id": 104
  }
}
```

#### Error Responses
- `404 Not Found`: Chatbot token is invalid, draft, or suspended.
- `403 Forbidden`: Requesting domain is not on the chatbot's whitelisted domain list.
- `422 Unprocessable Content`: Missing or blank `message`.
- `429 Too Many Requests`: Client IP exceeded message rate limit.

---

## 2. Lead Capture

### Submit Visitor Contact Information
Submits lead information (name, email, phone, notes) collected by the chatbot during a visitor conversation.

- **Endpoint**: `POST /api/v1/chat/{publicBotToken}/lead`
- **CORS**: `Access-Control-Allow-Origin: *`

#### Request Body
```json
{
  "conversation_id": 42,
  "visitor_name": "Jane Doe",
  "visitor_email": "jane@example.com",
  "visitor_phone": "+1 (555) 234-5678",
  "notes": "Interested in Enterprise annual plan."
}
```

#### Success Response (`200 OK`)
```json
{
  "success": true,
  "message": "Thank you! Your contact information has been received."
}
```

---

## 3. Message Feedback

### Rate Bot Response
Allows visitors to provide positive (`up`) or negative (`down`) ratings on a specific bot answer.

- **Endpoint**: `POST /api/v1/chat/feedback`
- **CORS**: `Access-Control-Allow-Origin: *`

#### Request Body
```json
{
  "message_id": 104,
  "rating": "up",
  "comment": "Very helpful explanation!"
}
```

#### Success Response (`200 OK`)
```json
{
  "success": true,
  "message": "Feedback received. Thank you!"
}
```

---

## 4. Public Widget Endpoints

### 1. Load Widget Script
Dynamic CDN JavaScript file configured for the target bot.
- **Endpoint**: `GET /embed/{publicBotToken}.js`
- **Response**: `application/javascript; charset=utf-8`
- **Cache-Control**: `public, max-age=3600`

### 2. Fetch Public Bot Configuration
Returns non-sensitive appearance and behavioral settings for rendering the widget.
- **Endpoint**: `GET /embed/{publicBotToken}/config`
- **Response (`200 OK`)**:
```json
{
  "token": "bot_tok_9f82a17c6e04",
  "name": "Customer Support Assistant",
  "category": "support",
  "language": "en",
  "welcome_message": "Hello! How can we help you today?",
  "suggested_questions": ["Opening Hours", "Pricing", "Contact Support"],
  "theme": "light",
  "primary_color": "#2563eb",
  "secondary_color": "#1e40af",
  "header_title": "Support Desk",
  "header_subtitle": "Typically replies in seconds",
  "widget_position": "bottom-right",
  "widget_border_radius": 16,
  "hide_watermark": false,
  "contact_capture_enabled": true,
  "contact_capture_prompt": "Please leave your email so we can follow up."
}
```

---

## 5. PayPal Webhook Endpoint

### Receive PayPal Subscription Events
Webhook receiver for PayPal recurring billing updates.

- **Endpoint**: `POST /api/v1/paypal/webhook`
- **Headers Verified**: `PAYPAL-TRANSMISSION-ID`, `PAYPAL-TRANSMISSION-TIME`, `PAYPAL-CERT-URL`, `PAYPAL-AUTH-ALGO`, `PAYPAL-TRANSMISSION-SIG`
- **Handled Events**:
  - `BILLING.SUBSCRIPTION.ACTIVATED`
  - `BILLING.SUBSCRIPTION.CANCELLED`
  - `BILLING.SUBSCRIPTION.EXPIRED`
  - `BILLING.SUBSCRIPTION.SUSPENDED`
  - `BILLING.SUBSCRIPTION.UPDATED`
  - `PAYMENT.SALE.COMPLETED`

#### Webhook Response (`200 OK`)
```json
{
  "success": true,
  "message": "Webhook processed successfully: BILLING.SUBSCRIPTION.ACTIVATED"
}
```
*Note: Duplicate event IDs automatically return `{"success": true, "message": "Event already processed (idempotent skip)."}`.*
