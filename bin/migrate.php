<?php
declare(strict_types=1);

/**
 * ChatBot Creator Studio - Database Migration Runner
 */

require_once dirname(__DIR__) . '/bootstrap/app.php';

use App\Core\Application;

echo "=======================================================\n";
echo "ChatBot Creator Studio - Database Migration\n";
echo "=======================================================\n";

$config = Application::config('database');
$host = $config['host'];
$port = $config['port'];
$dbName = $config['database'];
$user = $config['username'];
$pass = $config['password'];
$charset = $config['charset'];

try {
    echo "Connecting to MySQL server at {$host}:{$port}...\n";
    $rootPdo = new PDO("mysql:host={$host};port={$port};charset={$charset}", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database `{$dbName}` verified/created successfully.\n";

    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$dbName};charset={$charset}", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $schemaFile = dirname(__DIR__) . '/database/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new RuntimeException("Schema file not found at: {$schemaFile}");
    }

    echo "Reading and parsing schema.sql...\n";
    $sql = file_get_contents($schemaFile);

    // Strip SQL comments cleanly
    $cleanSql = preg_replace('/--[^\r\n]*/', '', $sql);
    $cleanSql = preg_replace('/\/\*.*?\*\//s', '', $cleanSql);

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
    echo "Successfully executed {$count} migration statements.\n";
    echo "Database migration complete!\n\n";

} catch (Exception $e) {
    echo "\n[ERROR] Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
