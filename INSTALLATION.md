# ChatBot Creator Studio — Installation & Setup Guide

This guide details the step-by-step setup procedure for ChatBot Creator Studio on local development environments (XAMPP, WampServer, Valet), Linux VPS (Ubuntu/Debian with Apache/Nginx), and shared hosting environments.

---

## 1. System Requirements

- **PHP 8.3** or higher
- **Required PHP Extensions**:
  - `pdo`
  - `pdo_mysql`
  - `curl`
  - `json`
  - `openssl`
  - `zip`
  - `mbstring`
- **Database**: MySQL 8.0+ or MariaDB 10.4+ (InnoDB engine required)
- **Web Server**: Apache 2.4+ (with `mod_rewrite` enabled) or Nginx
- **Dependency Manager**: Composer 2.x

---

## 2. Local & VPS Installation (CLI Method)

### Step 1: Clone or Extract the Project
Clone the repository or extract the archive into your web server directory:
```bash
cd /var/www/html/chatbot-creator
# or in XAMPP on Windows:
cd C:\xampp\htdocs\ChatBot-Creator
```

### Step 2: Configure Environment Variables
Copy `.env.example` to `.env`:
```bash
cp .env.example .env
```
Open `.env` and set your database connection and application URL:
```env
APP_NAME="ChatBot Creator Studio"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost/ChatBot-Creator
APP_SECRET=your-random-32-character-secret-key-here

DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=chatbot_creator
DB_USERNAME=root
DB_PASSWORD=your_db_password
DB_CHARSET=utf8mb4
```

### Step 3: Run Database Migrations
Execute the migration script to create the normalized database schema:
```bash
php bin/migrate.php
```
This automatically verifies the database, creates all 20+ tables with foreign key constraints, indexes, and UTF8MB4 collation.

### Step 4: Seed Platform Defaults
Populate initial plans (Free, Standard, Pro, Enterprise), features, RBAC roles, permissions, and chatbot templates:
```bash
php bin/seed.php
```

### Step 5: Create Super Administrator Account
Run the interactive administrator generator:
```bash
php bin/create-admin.php
```
You will be prompted to enter:
- Full Name
- Email Address
- Password (minimum 8 characters)

Alternatively, specify parameters via CLI flags:
```bash
php bin/create-admin.php --name="Super Admin" --email="admin@example.com" --password="StrongPassword123!"
```

### Step 6: Verify Permissions
Ensure that the `storage/` directory and its subdirectories are writable by the web server process:
```bash
chmod -R 775 storage/
# For Apache / Nginx:
chown -R www-data:www-data storage/
```

---

## 3. Shared Hosting Installation (No CLI / Terminal Access)

If you are deploying to shared hosting (cPanel, DirectAdmin, Plesk) without SSH terminal access:

1. **Upload Files**:
   Upload the entire project to your hosting account.
2. **Create MySQL Database**:
   Log into your cPanel or hosting control panel, open **MySQL Databases**, create a new database and user, and grant all privileges.
3. **Launch Web Installer**:
   In your browser, visit:
   ```text
   http://your-domain.com/install.php
   ```
   *(or `http://your-domain.com/htdocs/install.php` depending on your directory setup)*
4. **Enter Database & Admin Details**:
   Fill in the database name, user, password, and your desired Super Administrator login credentials.
5. **Execute Installation**:
   Click **Execute Migration & Complete Installation**. The installer will:
   - Create all database tables.
   - Seed all plans, templates, and permissions.
   - Provision your Super Administrator account.
   - Automatically write `storage/installed.lock` to permanently disable further web installer access.
6. **Sign In**:
   Visit `http://your-domain.com/login` and log in with your administrator account.

---

## 4. Verifying Acceptance Criteria

Run the automated test suite to ensure that your environment meets all SaaS requirements:
```bash
php tests/run-tests.php
# or using Composer:
composer test
```
All tests should return `[PASS]`.
