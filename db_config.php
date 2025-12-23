<?php
// PDO Database Configuration
$host = 'mysql-aniya.alwaysdata.net';
$dbname = 'aniya_driving_experience';
$username = 'aniya';
$password = 'Aniya2006.';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch(PDOException $e) {
    die("❌ Database Connection Error: " . $e->getMessage());
}
?>