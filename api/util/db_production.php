<?php
// Ambil variabel
$host = getenv('DB_HOST');
$port = getenv('DB_PORT') ?: '18910'; // Gunakan port dari hasil cek_env tadi
$dbname = getenv('DB_DATABASE') ?: 'defaultdb';
$username = getenv('DB_USERNAME');
$password = getenv('DB_PASSWORD');

// Path SSL
$ssl_ca = $_SERVER['DOCUMENT_ROOT'] . '/certs/ca.pem';

try {
    // PAKSA ke TCP dengan menambahkan host=... (tanpa socket)
    // Gunakan port langsung di dalam DSN
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_SSL_CA => $ssl_ca,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        // PAKSA PDO untuk tidak menggunakan local socket
        PDO::ATTR_PERSISTENT => false
    ];

    $pdo = new PDO($dsn, $username, $password, $options);

}
catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Koneksi Gagal: ' . $e->getMessage(),
        'info' => 'Pastikan IP Vercel tidak diblokir oleh Aiven (Aiven biasanya mengizinkan semua IP 0.0.0.0/0 secara default)'
    ]);
    exit();
}