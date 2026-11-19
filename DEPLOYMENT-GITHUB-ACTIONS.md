# ChatBot Creator Studio — GitHub Actions Deployment & Secrets Guide

This guide details how to securely store production credentials in **GitHub Repository Secrets** and deploy ChatBot Creator Studio to your shared host automatically upon pushing to GitHub, with zero build tools and complete credential protection.

---

## 1. How the Architecture Works

```text
[ Developer Machine ]
        |
        | git push (No .env file is pushed; .env is in .gitignore)
        v
[ GitHub Repository ]
        |
        | Triggers GitHub Action (.github/workflows/deploy.yml)
        v
[ GitHub Actions Runner ]
  ├── 1. Sets up PHP 8.3 & Composer.
  ├── 2. Runs `composer install --no-dev --optimize-autoloader`.
  ├── 3. Securely constructs production `.env` from GitHub Repository Secrets.
  └── 4. Transfers production files & .env to Shared Hosting via FTPS/FTP.
        |
        v
[ Shared Host Web Server (cPanel / ezyro / LAMP) ]
  ├── Executes PHP 8.3 native backend.
  ├── Connects to MySQL using injected secrets.
  └── Serves application via custom domain (CNAME).
```

> **Important Architecture Note on GitHub Pages vs Shared Hosting:**  
> GitHub Pages is strictly a static file web host. It does **not** execute PHP scripts or connect directly to MySQL databases. Therefore, the dynamic PHP application runs on your **Shared Host**, while GitHub Actions acts as the continuous deployment engine that pushes the tested PHP code and securely injected `.env` secrets to the shared host.  
> If you wish to serve the embeddable JavaScript widget (`chatbot-widget.js`) from a dedicated CDN subdomain powered by GitHub Pages with a custom `CNAME`, the workflow provides a secondary job (`deploy-widget-cdn-pages`) specifically for that purpose.

---

## 2. Required GitHub Repository Secrets

In your GitHub repository, navigate to **Settings > Secrets and variables > Actions** and click **New repository secret** to add the following:

### A. Shared Hosting FTP/FTPS Connection Secrets
| Secret Name | Description | Example Value |
|---|---|---|
| `FTP_SERVER` | Shared host FTP hostname | `ftp.yourdomain.com` or `ftpupload.net` |
| `FTP_USERNAME` | Shared host FTP account username | `cpanel_user` or `ezyro_12345678` |
| `FTP_PASSWORD` | Shared host FTP account password | `YourFtpSecurePassword` |
| `FTP_SERVER_DIR` | Target folder on hosting account | `htdocs/` or `public_html/` |
| `FTP_PROTOCOL` | Connection protocol (`ftps` or `ftp`) | `ftps` (recommended) or `ftp` |
| `FTP_PORT` | Port number | `21` |

### B. Production Application & Database Secrets
| Secret Name | Description | Example Value |
|---|---|---|
| `APP_URL` | Canonical domain where the app is hosted | `https://chatbot-creator.com` |
| `APP_SECRET` | 32-character random key for encryption | `a7f9b8c2d1e4f6a8b0c2d4e6f8a0b2c4` |
| `DB_HOST` | MySQL hostname (from hosting cPanel) | `localhost` or `sql123.yourhost.com` |
| `DB_PORT` | MySQL port | `3306` |
| `DB_DATABASE` | Production MySQL database name | `cpanel_chatbot` |
| `DB_USERNAME` | Production MySQL database user | `cpanel_dbuser` |
| `DB_PASSWORD` | Production MySQL database user password | `StrongDbPass123!` |

### C. PayPal Subscriptions REST API Secrets
| Secret Name | Description | Example Value |
|---|---|---|
| `PAYPAL_MODE` | PayPal mode | `live` (or `sandbox`) |
| `PAYPAL_CLIENT_ID` | PayPal REST Client ID | `AX...` |
| `PAYPAL_CLIENT_SECRET` | PayPal REST Client Secret | `EP...` |
| `PAYPAL_WEBHOOK_ID` | PayPal Webhook Identifier | `4JH987654321` |
| `PAYPAL_CURRENCY` | Billing currency | `USD` |

### D. Social Login Secrets (Optional)
| Secret Name | Description |
|---|---|
| `GITHUB_CLIENT_ID` / `GITHUB_CLIENT_SECRET` | GitHub OAuth Credentials |
| `GOOGLE_CLIENT_ID` / `GOOGLE_CLIENT_SECRET` | Google Cloud OAuth Credentials |
| `FACEBOOK_CLIENT_ID` / `FACEBOOK_CLIENT_SECRET` | Meta Developer OAuth Credentials |

---

## 3. GitHub Pages Widget CDN & CNAME Configuration (Optional)

If you wish to distribute static chatbot widget files through GitHub Pages using a custom domain (e.g., `cdn.yourdomain.com`):

1. Go to **Settings > Secrets and variables > Actions > Variables** tab.
2. Add the repository variables:
   - `ENABLE_PAGES_WIDGET_CDN` = `true`
   - `PAGES_CUSTOM_DOMAIN` = `cdn.yourdomain.com`
3. In your DNS provider (Cloudflare, Namecheap, GoDaddy), add a `CNAME` record:
   - Type: `CNAME`
   - Name: `cdn`
   - Target: `yourusername.github.io`

The workflow will publish `public-assets/widget` to GitHub Pages with the generated `CNAME` file.

---

## 4. Triggering Deployment

Every push to `main` (or `master`) triggers the workflow automatically:

```bash
git add .
git commit -m "Deploy production updates"
git push origin main
```

You can also trigger a manual deployment at any time by visiting **Actions > Deploy ChatBot Creator Studio to Shared Hosting > Run workflow**.
