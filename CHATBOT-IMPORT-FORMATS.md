# ChatBot Creator Studio — Chatbot Knowledge & Configuration Import Formats

ChatBot Creator Studio provides a high-throughput, transactional data ingestion engine allowing users to customize chatbots via **JSON configuration files**, **CSV knowledge tables**, or **interactive studio form fields**.

All three input mechanisms can be used independently or combined together without data conflict.

---

## 1. JSON Configuration Format (Schema Version 1.0)

The JSON format allows updating a chatbot's basic identity, personality, appearance, behavior, and knowledge entries in a single file.

### Specification
```json
{
  "schema_version": "1.0",
  "bot": {
    "name": "Acme Business Assistant",
    "description": "24/7 customer and sales inquiry bot",
    "category": "business",
    "language": "en",
    "welcome_message": "Welcome to Acme Corp! How may I assist you today?",
    "fallback_message": "I do not have sufficient information to answer that question. Would you like to speak to a specialist?"
  },
  "behavior": {
    "personality": "professional",
    "tone": "helpful",
    "response_style": "concise",
    "contact_capture_enabled": true,
    "handoff_enabled": false,
    "suggested_questions": [
      "What are your business hours?",
      "Where are you located?",
      "How do I request a product quote?"
    ]
  },
  "appearance": {
    "theme": "light",
    "primary_color": "#2563eb",
    "secondary_color": "#1e40af",
    "header_title": "Acme Support",
    "header_subtitle": "Always active",
    "widget_position": "bottom-right",
    "widget_border_radius": 16
  },
  "knowledge": [
    {
      "type": "faq",
      "question": "What are your business hours?",
      "answer": "Our sales and support teams are available Monday through Friday from 8:00 AM to 6:00 PM EST.",
      "category": "General",
      "tags": ["hours", "schedule", "availability"]
    },
    {
      "type": "faq",
      "question": "Do you offer international shipping?",
      "answer": "Yes, we ship to over 140 countries via tracked express delivery.",
      "category": "Shipping",
      "tags": ["shipping", "international", "delivery"]
    },
    {
      "type": "document",
      "title": "Company Overview & Quality Policy",
      "content": "Acme Corp is an ISO-9001 certified provider of enterprise automation and robotics software founded in 2018.",
      "category": "About",
      "tags": ["about", "company", "certification"]
    }
  ]
}
```

### Knowledge Item Types
- `faq`: Requires both `question` and `answer`. Used for direct semantic matching and instant FAQ responses.
- `document`: Requires `title` and `content`. Used as contextual background data for conversational queries and AI synthesis.

---

## 2. CSV Knowledge Formats

CSV files are ideal for bulk uploading FAQ questions, answers, and structured documentation from spreadsheets (Excel, Google Sheets, LibreOffice).

### Format A: Standard Question-Answer FAQ (Recommended)
Headers must include `question` and `answer`. Optional headers include `category` and `tags`:

```csv
question,answer,category,tags
"What are your opening hours?","We open Monday to Friday from 8:00 AM to 5:00 PM.","General","hours,schedule"
"How can I contact billing?","You can email billing@example.com or call our direct line.","Billing","billing,invoice,support"
"Do you support PayPal?","Yes, we accept PayPal Subscriptions and all major credit cards.","Billing","paypal,payment,plans"
"What is your return policy?","We provide a 30-day money-back guarantee on all standard purchases.","Policies","returns,guarantee,refund"
```

### Format B: Document Content Format
For importing longer text articles or knowledge sections:

```csv
title,content,category,tags
"Refund Policy Terms","All subscribers may cancel within 30 days of initial registration for a full refund.","Policies","refunds,cancel"
"Enterprise SLA Agreement","Enterprise tier includes a 99.9% uptime SLA with 1-hour priority response.","Enterprise","sla,support"
```

---

## 3. Import Workflow & Data Safety

Every upload through the Studio (`/studio/bots/{botId}/import`) follows a secure multi-stage pipeline:

1. **Upload Validation**: File size is checked against plan quotas (e.g. 5MB–50MB), and file extension/MIME type is verified (`application/json`, `text/csv`, `text/plain`).
2. **Schema & Header Verification**: Headers or JSON keys are checked for mandatory fields.
3. **Record Parsing & Sanitization**:
   - Strip malicious script tags, onerror hooks, and javascript URIs.
   - Encode special characters with UTF-8 normalization.
4. **Interactive Preview**: Displays valid records, invalid lines, and error descriptions to the user before committing changes.
5. **Safe Commit Modes**:
   - `add`: Appends new records while preserving existing entries.
   - `replace`: Replaces the existing knowledge base with the uploaded file after explicit user confirmation.
   - `merge`: Updates existing questions with identical text and inserts new questions.
6. **Atomic Transaction**: The entire commit runs inside a database transaction (`Database::transaction()`), ensuring that any error rolls back cleanly without leaving corrupted or partial states.
