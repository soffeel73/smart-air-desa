<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$host = getenv('DB_HOST');
// Port standar Hostinger adalah 3306, bukan 18910
$port = getenv('DB_PORT') ?: '3306';

$dbname = getenv('DB_DATABASE');
$username = getenv('DB_USERNAME');
$password = getenv('DB_PASSWORD');

try {
    // Kita hapus ';protocol=tcp' karena pada beberapa versi PHP PDO ini bisa menyebabkan error string
    // Hostinger sudah otomatis menggunakan TCP jika kita memasukkan IP Address
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // Kita hapus pengaturan SSL karena Hostinger menggunakan koneksi standar (Non-SSL)
        // Ini akan membuat koneksi jauh lebih cepat dan stabil
    ];

    $pdo = new PDO($dsn, $username, $password, $options);

}
catch (Throwable $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage(),
        'debug' => [
            'host' => $host,
            'port' => $port,
            'db' => $dbname
        ]
    ]);
    exit();
}