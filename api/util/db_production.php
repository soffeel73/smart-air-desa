<?php
// DB Connection for Aiven & Render (Production)
// Kode ini otomatis membaca Environment Variables dari Render

$host = getenv('DB_HOST');
$port = getenv('DB_PORT');
$dbname = getenv('DB_DATABASE');
$username = getenv('DB_USERNAME');
$password = getenv('DB_PASSWORD');
$ssl_ca = getenv('MYSQL_ATTR_SSL_CA'); // Lokasi file ca.pem

try {
    // Menambahkan opsi SSL untuk koneksi ke Aiven
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_SSL_CA => $ssl_ca, // Penting untuk Aiven!
    ];

    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, $options);

}
catch (PDOException $e) {
    // Catatan: Jika error, Render akan mencatatnya di 'Logs'
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed.',
        'error_detail' => (getenv('APP_DEBUG') === 'true') ? $e->getMessage() : 'Security protection active'
    ]);
    exit();
}