<?php
$host = trim(getenv('DB_HOST'));
$port = trim(getenv('DB_PORT')) ?: '18910';
$dbname = trim(getenv('DB_DATABASE')) ?: 'defaultdb';
$username = trim(getenv('DB_USERNAME'));
$password = trim(getenv('DB_PASSWORD'));

// Path SSL
$ssl_ca = $_SERVER['DOCUMENT_ROOT'] . '/certs/ca.pem';

try {
    // PERUBAHAN DISINI: Kita tambahkan protokol TCP secara paksa
    // Format: mysql:host=tcp://hostname;...
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_SSL_CA => $ssl_ca,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        // Tambahan opsi untuk memaksa koneksi jaringan
        PDO::ATTR_PERSISTENT => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ];

    $pdo = new PDO($dsn, $username, $password, $options);

}
catch (PDOException $e) {
    header('Content-Type: application/json');
    // Jika masih gagal, kita tampilkan semua info tanpa sensor untuk debug
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'check' => [
            'host' => $host,
            'port' => $port,
            'ssl_path' => $ssl_ca,
            'ssl_file_exists' => file_exists($ssl_ca)
        ]
    ]);
    exit();
}