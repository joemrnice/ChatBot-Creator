<?php
declare(strict_types=1);

/**
 * ChatBot Creator Studio - Database Seeder Runner
 */

require_once dirname(__DIR__) . '/bootstrap/app.php';

use App\Core\Database;

echo "=======================================================\n";
echo "ChatBot Creator Studio - Database Seeding\n";
echo "=======================================================\n";

try {
    $seedFile = dirname(__DIR__) . '/database/seed.sql';
    if (!file_exists($seedFile)) {
        throw new RuntimeException("Seed file not found at: {$seedFile}");
    }

    echo "Reading and parsing seed.sql...\n";
    $sql = file_get_contents($seedFile);

    // Strip SQL comments cleanly
    $cleanSql = preg_replace('/--[^\r\n]*/', '', $sql);
    $cleanSql = preg_replace('/\/\*.*?\*\//s', '', $cleanSql);

    $pdo = Database::pdo();
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

    $statements = preg_split('/;\s*[\r\n]+/', $cleanSql);
    $count = 0;
    foreach ($statements as $stmt) {
        $stmt = trim($stmt);
        if ($stmt === '') {
            continue;
        }
        $pdo->exec($stmt);
        $count++;
    }

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    echo "Successfully executed {$count} seed statements.\n";
    echo "Database seeding complete!\n\n";

} catch (Exception $e) {
    echo "\n[ERROR] Seeding failed: " . $e->getMessage() . "\n";
    exit(1);
}
