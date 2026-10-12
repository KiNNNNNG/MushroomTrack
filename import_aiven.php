<?php

/**
 * MushroomTrack - Aiven Cloud Database Importer
 * Run with: php import_aiven.php
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$host = env('DB_HOST');
$port = env('DB_PORT', 3306);
$db   = env('DB_DATABASE', 'defaultdb');
$user = env('DB_USERNAME', 'avnadmin');
$pass = env('DB_PASSWORD');

echo "=========================================\n";
echo " MushFungAI -> Aiven Database Importer  \n";
echo "=========================================\n";
echo "Target Host:     $host\n";
echo "Target Port:     $port\n";
echo "Target Database: $db\n";
echo "Target User:     $user\n";
echo "-----------------------------------------\n";

if ($host === '127.0.0.1' || $host === 'localhost') {
    die("ERROR: DB_HOST in your .env is still set to localhost. Please paste your Aiven Host and Password into .env first!\n");
}

try {
    echo "Connecting to Aiven MySQL with SSL...\n";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ];
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4", $user, $pass, $options);
    echo "Connected successfully to Aiven!\n\n";

    $backupFile = __DIR__ . '/database_backup_before_improvements.sql';
    if (!file_exists($backupFile)) {
        die("ERROR: $backupFile not found.\n");
    }

    echo "Reading database_backup_before_improvements.sql...\n";
    $raw = file_get_contents($backupFile);
    if (substr($raw, 0, 2) === "\xFF\xFE") {
        echo "Detected UTF-16LE encoding; converting to UTF-8...\n";
        $sql = mb_convert_encoding(substr($raw, 2), 'UTF-8', 'UTF-16LE');
    } else {
        $sql = $raw;
    }

    echo "Importing all tables and data into Aiven...\n";
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, 1);
    $pdo->exec($sql);
    echo "SUCCESS! Database imported completely into Aiven!\n\n";

    echo "Imported Tables:\n";
    $stmt = $pdo->query("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        echo "  - " . $row[0] . "\n";
    }

    echo "\nAll done! You are ready to deploy to Vercel.\n";

} catch (PDOException $e) {
    echo "\nCONNECTION FAILED:\n";
    echo $e->getMessage() . "\n\n";
    echo "Tip: Please double-check your DB_PASSWORD and DB_HOST in .env from the Aiven Console.\n";
}
