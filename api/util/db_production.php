<?php
$host = getenv('DB_HOST');
$port = getenv('DB_PORT');
$dbname = getenv('DB_DATABASE');
$username = getenv('DB_USERNAME');
$password = getenv('DB_PASSWORD');

// Gunakan Document Root agar path ke ca.pem absolut dan benar di server Vercel
$ssl_ca = $_SERVER['DOCUMENT_ROOT'] . '/certs/ca.pem';

try {
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // Tambahkan verifikasi server agar lebih stabil
        PDO::MYSQL_ATTR_SSL_CA => $ssl_ca,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ];

    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, $options);

}
catch (PDOException $e) {
    http_response_code(500);
    // Kita tampilkan error-nya sementara agar kita bisa tahu apa yang salah
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit();
}