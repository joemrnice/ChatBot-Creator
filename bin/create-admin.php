<?php
declare(strict_types=1);

/**
 * ChatBot Creator Studio - Create Administrator Account
 */

require_once dirname(__DIR__) . '/bootstrap/app.php';

use App\Core\Database;
use App\Core\Security;

echo "=======================================================\n";
echo "ChatBot Creator Studio - Create Administrator\n";
echo "=======================================================\n";

$options = getopt('', ['name:', 'email:', 'password:']);

$name = $options['name'] ?? null;
$email = $options['email'] ?? null;
$password = $options['password'] ?? null;

if (!$name || !$email || !$password) {
    if (PHP_SAPI !== 'cli') {
        echo "This script must be run from CLI.\n";
        exit(1);
    }

    echo "Please provide administrator details:\n";
    if (!$name) {
        echo "Full Name: ";
        $name = trim(fgets(STDIN));
    }
    if (!$email) {
        echo "Email Address: ";
        $email = trim(fgets(STDIN));
    }
    if (!$password) {
        echo "Password (min 8 chars): ";
        $password = trim(fgets(STDIN));
    }
}

if (strlen($password) < 8) {
    echo "[ERROR] Password must be at least 8 characters.\n";
    exit(1);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "[ERROR] Invalid email address format.\n";
    exit(1);
}

try {
    $existing = Database::fetchOne("SELECT id FROM users WHERE email = :email", ['email' => $email]);
    $hash = Security::hashPassword($password);

    if ($existing) {
        $userId = (int)$existing['id'];
        Database::update('users', [
            'name' => $name,
            'password_hash' => $hash,
            'status' => 'active',
            'email_verified_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = :id', ['id' => $userId]);
        echo "Existing account updated for: {$email}\n";
    } else {
        $uuid = Security::generateUuid();
        $proPlan = Database::fetchOne("SELECT id FROM plans WHERE slug = 'pro' LIMIT 1");
        $planId = $proPlan ? $proPlan['id'] : 1;

        $userId = Database::insert('users', [
            'uuid' => $uuid,
            'name' => $name,
            'email' => $email,
            'password_hash' => $hash,
            'plan_id' => $planId,
            'status' => 'active',
            'email_verified_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        echo "Created new user record (ID: {$userId})\n";
    }

    // Assign roles: superadmin and user
    $roles = Database::fetchAll("SELECT id, slug FROM roles WHERE slug IN ('superadmin', 'user')");
    foreach ($roles as $r) {
        Database::query(
            "INSERT IGNORE INTO user_roles (user_id, role_id, created_at) VALUES (:uid, :rid, NOW())",
            ['uid' => $userId, 'rid' => $r['id']]
        );
    }

    echo "Assigned Super Administrator and User roles successfully.\n";
    echo "=======================================================\n";
    echo "Admin Account Ready!\n";
    echo "Email: {$email}\n";
    echo "=======================================================\n\n";

} catch (Exception $e) {
    echo "[ERROR] Failed to create admin: " . $e->getMessage() . "\n";
    exit(1);
}
