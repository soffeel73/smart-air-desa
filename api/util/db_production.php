<?php
// Pastikan tidak ada spasi sebelum <?php

$host = trim(getenv('DB_HOST'));
$port = trim(getenv('DB_PORT')) ?: '3306';
$dbname = trim(getenv('DB_DATABASE'));
$username = trim(getenv('DB_USERNAME'));
$password = trim(getenv('DB_PASSWORD'));

// DIAGNOSA: Jika host kosong, tampilkan pesan khusus
if (!$host) {
    header('Content-Type: application/json');
    die(json_encode([
        'error' => true,
        'message' => 'Variabel DB_HOST tidak terbaca oleh Vercel. Pastikan sudah input di Environment Variables dan sudah di-Redeploy.'
    ]));
}

try {
    // Gunakan dsn tanpa embel-embel, tapi pastikan host adalah IP
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 5, // Timeout cepat jika gagal
    ];

    $pdo = new PDO($dsn, $username, $password, $options);

}
catch (Throwable $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage(),
        'debug_info' => [
            'host_used' => $host,
            'port_used' => $port,
            'db_name' => $dbname
        ]
    ]);
    exit();
}