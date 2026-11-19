<?php
declare(strict_types=1);

/**
 * ChatBot Creator Studio - Web-based Shared Hosting Setup Installer
 * Protected installation wizard for shared hosting without terminal/CLI access.
 */

define('CHATBOT_CREATOR_START', microtime(true));

$lockFile = __DIR__ . '/storage/installed.lock';
if (file_exists($lockFile)) {
    http_response_code(403);
    echo '<!DOCTYPE html><html><head><title>Installer Locked</title><style>body{font-family:-apple-system,BlinkMacSystemFont,sans-serif;max-width:600px;margin:80px auto;padding:30px;background:#f8fafc;color:#1e293b;}h1{color:#ef4444;font-size:1.5rem;}.card{background:#fff;padding:24px;border-radius:12px;border:1px solid #e2e8f0;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);}</style></head><body>';
    echo '<div class="card"><h1>🔒 Installation Locked</h1><p>ChatBot Creator Studio has already been installed on this server.</p><p>For security, the web installer is automatically locked. To run the installer again, delete the file <code>storage/installed.lock</code> via your hosting file manager.</p><p style="margin-top:20px;"><a href="login" style="color:#2563eb;text-decoration:none;font-weight:600;">&larr; Go to Sign In</a></p></div>';
    echo '</body></html>';
    exit;
}

// Load Application Bootstrap
require_once __DIR__ . '/bootstrap/app.php';

use App\Core\Application;
use App\Core\Database;
use App\Core\Security;

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbHost = trim($_POST['db_host'] ?? 'localhost');
    $dbPort = (int)($_POST['db_port'] ?? 3306);
    $dbName = trim($_POST['db_database'] ?? 'chatbot_creator');
    $dbUser = trim($_POST['db_username'] ?? 'root');
    $dbPass = (string)($_POST['db_password'] ?? '');

    $adminName = trim($_POST['admin_name'] ?? '');
    $adminEmail = strtolower(trim($_POST['admin_email'] ?? ''));
    $adminPass = (string)($_POST['admin_password'] ?? '');

    if ($adminName === '' || $adminEmail === '' || strlen($adminPass) < 8) {
        $error = "Admin name, valid email, and password (minimum 8 characters) are required.";
    } elseif (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
        $error = "Please provide a valid administrator email address.";
    } else {
        try {
            // 1. Connect to MySQL server
            $dsn = "mysql:host={$dbHost};port={$dbPort};charset=utf8mb4";
            $pdo = new PDO($dsn, $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            // 2. Create database if not exists
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `{$dbName}`");

            // 3. Run schema.sql
            $schemaFile = __DIR__ . '/database/schema.sql';
            if (!file_exists($schemaFile)) {
                throw new RuntimeException("Schema file missing at {$schemaFile}");
            }
            $cleanSchema = preg_replace('/--[^\r\n]*/', '', file_get_contents($schemaFile));
            $cleanSchema = preg_replace('/\/\*.*?\*\//s', '', $cleanSchema);
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
            foreach (preg_split('/;\s*[\r\n]+/', $cleanSchema) as $stmt) {
                $stmt = trim($stmt);
                if ($stmt !== '') $pdo->exec($stmt);
            }

            // 4. Run seed.sql
            $seedFile = __DIR__ . '/database/seed.sql';
            if (file_exists($seedFile)) {
                $cleanSeed = preg_replace('/--[^\r\n]*/', '', file_get_contents($seedFile));
                $cleanSeed = preg_replace('/\/\*.*?\*\//s', '', $cleanSeed);
                foreach (preg_split('/;\s*[\r\n]+/', $cleanSeed) as $stmt) {
                    $stmt = trim($stmt);
                    if ($stmt !== '') $pdo->exec($stmt);
                }
            }
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

            // 5. Create Super Administrator Account
            $uuid = Security::generateUuid();
            $hash = Security::hashPassword($adminPass);
            $proPlan = $pdo->query("SELECT id FROM plans WHERE slug = 'pro' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            $planId = $proPlan ? (int)$proPlan['id'] : 1;

            $stmt = $pdo->prepare("INSERT INTO users (uuid, name, email, password_hash, plan_id, status, email_verified_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 'active', NOW(), NOW(), NOW())");
            $stmt->execute([$uuid, $adminName, $adminEmail, $hash, $planId]);
            $adminId = (int)$pdo->lastInsertId();

            // Assign Superadmin and User roles
            $roleStmt = $pdo->prepare("INSERT IGNORE INTO user_roles (user_id, role_id, created_at) SELECT ?, id, NOW() FROM roles WHERE slug IN ('superadmin', 'user')");
            $roleStmt->execute([$adminId]);

            // 6. Update .env if present
            $envPath = __DIR__ . '/.env';
            if (file_exists($envPath)) {
                $envContent = file_get_contents($envPath);
                $envContent = preg_replace('/DB_HOST=.*/', "DB_HOST={$dbHost}", $envContent);
                $envContent = preg_replace('/DB_PORT=.*/', "DB_PORT={$dbPort}", $envContent);
                $envContent = preg_replace('/DB_DATABASE=.*/', "DB_DATABASE={$dbName}", $envContent);
                $envContent = preg_replace('/DB_USERNAME=.*/', "DB_USERNAME={$dbUser}", $envContent);
                $envContent = preg_replace('/DB_PASSWORD=.*/', "DB_PASSWORD=\"{$dbPass}\"", $envContent);
                @file_put_contents($envPath, $envContent);
            }

            // 7. Lock Installer
            file_put_contents($lockFile, json_encode([
                'installed_at' => date('c'),
                'installer' => 'web',
                'admin_email' => $adminEmail,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            ], JSON_PRETTY_PRINT));

            $success = true;
        } catch (\Throwable $e) {
            $error = "Installation failed: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Installer - ChatBot Creator Studio</title>
    <style>
        :root { --primary: #2563eb; --primary-hover: #1d4ed8; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #0f172a; color: #f8fafc; margin: 0; padding: 40px 20px; line-height: 1.5; }
        .container { max-width: 680px; margin: 0 auto; }
        .card { background: #1e293b; border-radius: 16px; border: 1px solid #334155; padding: 36px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.3); }
        h1 { margin-top: 0; font-size: 1.75rem; font-weight: 800; color: #fff; }
        .lead { color: #94a3b8; font-size: 0.95rem; margin-bottom: 24px; }
        .section-title { font-size: 1.1rem; font-weight: 700; color: #38bdf8; margin: 24px 0 12px; border-bottom: 1px solid #334155; padding-bottom: 8px; }
        .form-group { margin-bottom: 16px; }
        label { display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 6px; }
        input[type="text"], input[type="email"], input[type="password"], input[type="number"] { width: 100%; box-sizing: border-box; padding: 10px 14px; background: #0f172a; border: 1px solid #334155; border-radius: 8px; color: #fff; font-size: 0.95rem; }
        input:focus { outline: none; border-color: var(--primary); }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .btn { display: inline-block; width: 100%; text-align: center; padding: 12px 20px; background: var(--primary); color: #fff; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: background 0.2s; box-sizing: border-box; text-decoration: none; }
        .btn:hover { background: var(--primary-hover); }
        .alert-error { background: #ef444422; border: 1px solid #ef4444; color: #fca5a5; padding: 14px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; }
        .alert-success { background: #10b98122; border: 1px solid #10b981; color: #6ee7b7; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <?php if ($success): ?>
                <div class="alert-success">
                    <h2 style="margin-top: 0; color: #10b981;">🎉 Installation Complete!</h2>
                    <p>Database migrations and initial seeds executed successfully. The Super Administrator account has been provisioned.</p>
                    <p>For security, the installer has been automatically locked (<code>storage/installed.lock</code> created).</p>
                    <div style="margin-top: 24px; display: flex; gap: 12px;">
                        <a href="login" class="btn">Go to Sign In &rarr;</a>
                    </div>
                </div>
            <?php else: ?>
                <h1>ChatBot Creator Studio</h1>
                <p class="lead">One-Click Web Setup & Database Installer for Shared Hosting.</p>

                <?php if ($error): ?>
                    <div class="alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="section-title">1. MySQL Database Connection</div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label>Database Host</label>
                            <input type="text" name="db_host" value="<?= htmlspecialchars($_POST['db_host'] ?? 'localhost') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Port</label>
                            <input type="number" name="db_port" value="<?= htmlspecialchars($_POST['db_port'] ?? '3306') ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Database Name</label>
                        <input type="text" name="db_database" value="<?= htmlspecialchars($_POST['db_database'] ?? 'chatbot_creator') ?>" required>
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label>Database Username</label>
                            <input type="text" name="db_username" value="<?= htmlspecialchars($_POST['db_username'] ?? 'root') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Database Password</label>
                            <input type="password" name="db_password" value="<?= htmlspecialchars($_POST['db_password'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="section-title">2. Initial Super Administrator Account</div>
                    <div class="form-group">
                        <label>Administrator Full Name</label>
                        <input type="text" name="admin_name" value="<?= htmlspecialchars($_POST['admin_name'] ?? 'Super Admin') ?>" required>
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label>Administrator Email Address</label>
                            <input type="email" name="admin_email" value="<?= htmlspecialchars($_POST['admin_email'] ?? 'admin@chatbot-creator.com') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Password (min 8 chars)</label>
                            <input type="password" name="admin_password" placeholder="Create strong password" required>
                        </div>
                    </div>

                    <div style="margin-top: 30px;">
                        <button type="submit" class="btn">Execute Migration & Complete Installation &rarr;</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
