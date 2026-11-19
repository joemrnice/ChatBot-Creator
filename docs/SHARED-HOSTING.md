# ChatBot Creator Studio — Shared Hosting Deployment Guide

Shared hosting environments frequently impose specific file structure conventions and security constraints. This guide explains how to deploy ChatBot Creator Studio to shared hosting platforms (such as cPanel, DirectAdmin, InfinityFree, ezyro, and conventional LAMP hosts).

---

## 1. Directory Layout Architecture

There are two primary shared hosting directory models.

### Layout Model A: Application Outside Document Root (Recommended)

When your shared hosting account allows storing files one level above the public web root (`htdocs` or `public_html`), use this preferred layout:

```text
/home/account/
│
├── chatbot-creator/            <-- Secure Private Application Files
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   ├── .env
│   └── composer.json
│
└── htdocs/ (or public_html/)   <-- Public Web Document Root
    ├── index.php               <-- Public entry point
    ├── .htaccess               <-- Apache front-controller rewrites
    ├── install.php             <-- Web installer (locked after run)
    ├── assets/                 <-- CSS, JS, widget assets
    ├── public-assets/          <-- Static mirrors
    ├── favicon.ico
    └── robots.txt
```

#### Entry Point Behavior
The supplied `htdocs/index.php` automatically resolves the core bootstrap file:
```php
$possiblePaths = [
    dirname(__DIR__) . '/bootstrap/app.php',
    dirname(__DIR__) . '/chatbot-creator/bootstrap/app.php',
];
```
This isolates your configuration files, database credentials, logs, and business logic outside the publicly accessible web folder.

---

### Layout Model B: Self-Contained Deployment (Inside `htdocs`)

If your shared hosting provider restricts all files strictly inside `htdocs` or `public_html`:

```text
/home/account/htdocs/
├── app/
├── bootstrap/
├── config/
├── database/
├── public-assets/
├── routes/
├── storage/
├── vendor/
├── .env
├── .htaccess                   <-- Multi-layer access protection
├── index.php
├── install.php
├── favicon.ico
└── robots.txt
```

In this model, private directories are secured by the root `.htaccess` file, which blocks direct access to internal folders and sensitive files.

---

## 2. Apache `.htaccess` Directives

The included `.htaccess` file contains defensive rules tested across shared hosting environments:

1. **Disable Directory Browsing**:
   ```apache
   <IfModule mod_autoindex.c>
       Options -Indexes
   </IfModule>
   ```
2. **Block Sensitive Files**:
   ```apache
   <FilesMatch "^\.(env|git|htaccess|htpasswd|ini|log|sh|sql)$">
       Require all denied
   </FilesMatch>
   <FilesMatch "^(composer\.(json|lock)|package\.json|phpunit\.xml)$">
       Require all denied
   </FilesMatch>
   ```
3. **Block Internal Directories**:
   ```apache
   <IfModule mod_rewrite.c>
       RewriteEngine On
       RewriteRule ^(app|bootstrap|config|database|routes|storage/private|storage/logs|tests|bin)/ - [F,L,NC]
       
       # Serve static assets directly if they exist
       RewriteCond %{REQUEST_FILENAME} -f [OR]
       RewriteCond %{REQUEST_FILENAME} -d
       RewriteRule ^ - [L]

       # Front controller rewrite
       RewriteRule ^ index.php [QSA,L]
   </IfModule>
   ```
4. **Security Headers**:
   ```apache
   <IfModule mod_headers.c>
       Header always set X-Content-Type-Options "nosniff"
       Header always set X-XSS-Protection "1; mode=block"
       Header always set X-Frame-Options "SAMEORIGIN"
       Header always set Referrer-Policy "strict-origin-when-cross-origin"
   </IfModule>
   ```

---

## 3. Shared Hosting Configuration Steps

### 1. Select PHP Version
In your cPanel or hosting control panel, open **Select PHP Version** or **MultiPHP Manager** and select **PHP 8.3** or higher.
Verify that the following extensions are enabled:
- `pdo`
- `pdo_mysql`
- `curl`
- `openssl`
- `mbstring`
- `zip`
- `json`

### 2. Configure MySQL Database
1. Go to **MySQL Databases** in your control panel.
2. Create a new database (e.g., `user_chatbot`).
3. Create a MySQL user and assign a strong password.
4. Add the user to the database with **ALL PRIVILEGES**.

### 3. File Permissions
Ensure that the `storage` directory and its subdirectories have write permissions:
- `storage/` -> `0755` or `0775`
- `storage/logs/` -> `0755` or `0775`
- `storage/cache/` -> `0755` or `0775`
- `storage/uploads/` -> `0755` or `0775`
- `storage/exports/` -> `0755` or `0775`
- `storage/private/` -> `0755` or `0775`

### 4. Running the Web Installer
If you do not have SSH terminal access:
1. Open your browser and navigate to:
   ```text
   http://your-domain.com/install.php
   ```
2. Enter your MySQL database credentials and create your initial administrator account.
3. Once completed, the installer will create `storage/installed.lock` to prevent unauthorized execution.

---

## 4. Troubleshooting Shared Hosting Issues

| Issue | Cause | Solution |
|---|---|---|
| **500 Internal Server Error** | Unsupported Apache directive or missing PHP extension | Check hosting error logs in cPanel; ensure PHP 8.3 is active and `mod_rewrite` is enabled. |
| **404 Not Found on Sub-pages** | `mod_rewrite` not processing `.htaccess` | Ensure `.htaccess` is uploaded (dotfiles may be hidden in FTP clients) and `AllowOverride All` is permitted by host. |
| **Access Forbidden (403)** | Directory listing prevented or storage directory permission error | Verify folder permissions (`0755`) and that files are owned by the account user. |
| **Database Connection Refused** | Hostname mismatch | Some shared hosts require `localhost`, while others specify a remote host like `sql123.yourhost.com`. Check your cPanel MySQL details. |
