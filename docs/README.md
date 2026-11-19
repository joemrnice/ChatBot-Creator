# ChatBot Creator Studio

> **Multi-Tenant AI Chatbot Creation SaaS Platform**  
> Architected in native PHP 8.3+, zero frameworks, zero frontend build tools, native MySQL InnoDB with complete multi-tenant data isolation.

---

## 🚀 Overview

**ChatBot Creator Studio** empowers individuals, businesses, educators, creators, and developers to build, customize, train, embed, and deploy conversational chatbots across their websites and applications without writing code.

Built strictly according to production SaaS engineering principles:
- **Zero Frameworks**: No Laravel, Symfony, React, Vue, Bootstrap, or Tailwind. Native PHP 8.3+, clean PSR-4 modular MVC architecture, vanilla ES6+ JavaScript, responsive CSS3 design tokens.
- **Shared Hosting First**: Seamlessly compatible with standard PHP shared hosting (cPanel, DirectAdmin, Apache `htdocs`) as well as modern VPS and cloud environments.
- **Multi-Tenant Architecture**: Strict database-level isolation of user bots, knowledge items, conversations, and usage metrics.
- **PayPal Subscriptions REST API**: Complete recurring billing workflow with webhooks, idempotency, signature verification, and plan entitlements.
- **Dual Deployment**: Ready-to-use CDN widget (`/embed/{token}.js` with Shadow DOM CSS isolation) and downloadable standalone ZIP integration packages.

---

## 📦 Key Platform Modules

1. **Public Marketing Website**:
   - High-converting landing page (`/`), feature showcase (`/features`), interactive workflow explanation (`/how-it-works`), database-driven subscription pricing (`/pricing`), `/about`, `/contact`, `/faq`, `/privacy`, and `/terms`.
2. **User Authentication & Social Login**:
   - Secure email/password authentication, Argon2id/bcrypt password hashing, rate-limited login attempts, email verification (`/verify-email`), CSRF token defense, and GitHub / Google / Facebook OAuth integrations (`/auth/{provider}`).
3. **SaaS Subscription Engine**:
   - 4-Tier Plan model (**Free**, **Standard**, **Pro** [Most Popular / Best Value], and **Enterprise**).
   - Centralized server-side entitlement enforcement for chatbots, monthly messages, knowledge items, custom branding, and AI credentials.
   - PayPal Subscriptions REST API integration with automated webhooks and status synchronization.
4. **Personal Studio & Chatbot Creation Wizard**:
   - Multi-step builder: Basic Info & Categories, Personality & Response Tone, Knowledge Management, Appearance & Custom Styling, Behavior & Contact Capture, Live Split-Screen Testing, and 1-Click Publishing.
5. **Knowledge Base & Import/Export System**:
   - Versioned JSON configuration imports (`schema_version: "1.0"`).
   - Structured CSV knowledge uploads (FAQ questions, answers, categories, tags).
   - Data validation, duplicate detection, interactive preview, and safe commit modes (`add`, `merge`, `replace`).
   - One-click exports of bot JSON configurations, knowledge CSV, conversation transcripts, and deployment ZIP packages.
6. **Chatbot Conversation Engine**:
   - Knowledge retrieval with MySQL full-text / relevance indexing.
   - Provider abstraction: Knowledge Base retrieval, OpenAI-compatible REST API integration, and fallback handlers.
   - Prompt injection defense, sanitize HTML, and automated usage accounting.
7. **Embeddable Widget & Downloadable Packages**:
   - Shadow DOM encapsulated CDN widget (`<script src=".../embed/{token}.js"></script>`).
   - Standalone ZIP generator bundling `README.md`, `config.json`, `embed.html`, `widget.js`, `widget.css`, and HTML/PHP integration examples.
8. **Administrative Control Center**:
   - Centralized SaaS data center: Monthly Recurring Revenue (MRR), active users, bot moderation, plan entitlements, payment transactions, PayPal webhook inspection, platform usage metrics, analytics, RBAC permissions, security audit logs, customer support tickets, and system health diagnostics.

---

## 🛠 Technology Stack

| Layer | Technologies |
|---|---|
| **Backend** | PHP 8.3+, Native PSR-4 Autoloading, Composer, PDO, cURL, OpenSSL, ZipArchive |
| **Frontend** | Semantic HTML5, Vanilla Modern JavaScript (ES6+), CSS3 Variables / Tokens, Shadow DOM |
| **Database** | MySQL 8.0+ (InnoDB, UTF8mb4, Foreign Key Constraints, Transactions, Prepared Statements) |
| **Payments** | PayPal Subscriptions REST API (Sandbox & Production, Webhook Signatures) |
| **Security** | Argon2id/Bcrypt, HttpOnly/SameSite Session Cookies, CSRF Middleware, IP Rate Limiting, Input Sanitization |

---

## 📁 Project Directory Structure

```text
ChatBot-Creator/
├── app/
│   ├── Controllers/       # HTTP Request Handlers (Home, Auth, Studio, Bot, Import, Export, Billing, Admin, Api)
│   ├── Core/              # Native Application Framework (App, Router, Request, Response, Database, Auth, Security)
│   ├── Helpers/           # Global Utility Functions (url, asset, csrf_token, auth_user, e)
│   ├── Middleware/        # Pipeline Middlewares (Auth, Guest, Admin, Subscription, Csrf, RateLimit)
│   ├── Providers/         # Extensible AI Providers (AIProviderInterface, OpenAICompatible, Fallback)
│   ├── Services/          # SaaS Business Logic (Chatbot, Conversation, Subscription, PayPal, Import, Export, Usage)
│   └── Views/             # Modular PHP View Templates (Public, Auth, Studio, Chatbot, Admin, Layouts)
├── bin/
│   ├── migrate.php        # CLI Database Schema Migration Runner
│   ├── seed.php           # CLI Seed Data Runner
│   └── create-admin.php   # CLI Administrator Account Creation
├── bootstrap/             # Application initialization
├── config/                # Environment-driven configuration files
├── database/
│   ├── schema.sql         # Normalized MySQL 8.0+ schema (InnoDB)
│   └── seed.sql           # Starter data (Plans, Features, Roles, Permissions, Templates)
├── docs/                  # Detailed architectural & setup guides
├── htdocs/                # Shared hosting public web root entry point
├── public-assets/         # Static CSS, JS, Favicon, and Widget files
├── routes/                # Clean URL route definitions (web.php, admin.php, api.php)
├── storage/               # Application storage (logs, cache, uploads, exports, private)
├── tests/                 # Automated End-to-End Test Suite
├── .env.example           # Comprehensive environment variable template
├── composer.json          # Composer dependency & script configurations
├── install.php            # Web-based setup installer for shared hosting without CLI
└── index.php              # Standard root front-controller
```

---

## 🚀 Quick Start Guide

### 1. Requirements
- PHP 8.3 or higher with extensions: `pdo`, `pdo_mysql`, `curl`, `json`, `openssl`, `zip`, `mbstring`.
- MySQL 8.0 or compatible database server.
- Composer.

### 2. Installation
```bash
# Clone or upload the repository
git clone https://github.com/your-org/chatbot-creator.git
cd chatbot-creator

# Copy environment configuration
cp .env.example .env

# Configure your database credentials in .env
# DB_HOST=localhost
# DB_DATABASE=chatbot_creator
# DB_USERNAME=root
# DB_PASSWORD=

# Run database migrations and seed data
php bin/migrate.php
php bin/seed.php

# Create your Super Administrator account
php bin/create-admin.php
```

### 3. Alternative Web Installation (Shared Hosting)
If your shared hosting server does not provide SSH or terminal access:
1. Upload the project files to your server.
2. Navigate to `http://your-domain.com/install.php` in your browser.
3. Fill in your MySQL credentials and administrator details.
4. Click **Execute Migration & Complete Installation**. The installer will run migrations, seed data, create your administrator account, and automatically lock itself (`storage/installed.lock`).

### 4. Running Tests
```bash
composer test
# or:
php tests/run-tests.php
```

---

## 📖 Comprehensive Documentation

- [INSTALLATION.md](INSTALLATION.md) — Step-by-step setup guide for local, VPS, and shared hosting.
- [SHARED-HOSTING.md](SHARED-HOSTING.md) — Apache, `.htaccess`, `htdocs` layout, and cPanel configuration.
- [SECURITY.md](SECURITY.md) — Multi-tenant isolation, CSRF, rate limits, encryption, and audit trails.
- [API.md](API.md) — REST API endpoints for chatbot messaging, leads, feedback, and webhooks.
- [CHATBOT-IMPORT-FORMATS.md](CHATBOT-IMPORT-FORMATS.md) — Complete specifications for JSON and CSV knowledge imports.
- [PAYPAL-SETUP.md](PAYPAL-SETUP.md) — PayPal Subscriptions REST API setup, webhook IDs, and plan mappings.
- [OAUTH-SETUP.md](OAUTH-SETUP.md) — Configuration guide for GitHub, Google, and Facebook OAuth login.

---

## 📄 License
This software is licensed under the [MIT License](LICENSE).
