<?php
$host = trim(getenv('DB_HOST'));
$port = trim(getenv('DB_PORT')) ?: '18910';
$dbname = trim(getenv('DB_DATABASE')) ?: 'defaultdb';
$username = trim(getenv('DB_USERNAME'));
$password = trim(getenv('DB_PASSWORD'));

// Path sertifikat
$ssl_ca = $_SERVER['DOCUMENT_ROOT'] . '/certs/ca.pem';

try {
    // KUNCINYA: Gunakan IP dilingkari kurung atau definisikan host tanpa embel-embel
    // Dan tambahkan parameter pdo_mysql.default_socket
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname}";

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_SSL_CA => $ssl_ca,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        // PAKSA koneksi melalui jaringan, bukan socket
        PDO::ATTR_PERSISTENT => false,
    ];

    // Cek apakah file CA benar-benar ada sebelum konek
    if (!file_exists($ssl_ca)) {
        throw new Exception("Sertifikat SSL tidak ditemukan di: " . $ssl_ca);
    }

    $pdo = new PDO($dsn, $username, $password, $options);

}
catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'info' => 'Coba gunakan IP Address jika Hostname gagal'
    ]);
    exit();
}