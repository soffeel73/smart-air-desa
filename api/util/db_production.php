<?php
// Ambil variabel dari Vercel
$host = getenv('DB_HOST');
$port = getenv('DB_PORT');
$dbname = getenv('DB_DATABASE');
$username = getenv('DB_USERNAME');
$password = getenv('DB_PASSWORD');

// Jika variabel kosong, tampilkan pesan khusus agar kita tahu mana yang meleset
if (!$host) {
    die(json_encode(['success' => false, 'message' => 'Environment Variable DB_HOST tidak terbaca']));
}

// Coba beberapa alternatif path sertifikat untuk Vercel
$paths = [
    $_SERVER['DOCUMENT_ROOT'] . '/certs/ca.pem',
    __DIR__ . '/../../certs/ca.pem',
    '/var/task/user/certs/ca.pem'
];

$ssl_ca = null;
foreach ($paths as $path) {
    if (file_exists($path)) {
        $ssl_ca = $path;
        break;
    }
}

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_SSL_CA => $ssl_ca,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ];

    $pdo = new PDO($dsn, $username, $password, $options);

}
catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Koneksi Gagal: ' . $e->getMessage(),
        'debug' => [
            'host_terbaca' => $host ? 'Ya' : 'Tidak',
            'ssl_file_ditemukan' => $ssl_ca ? 'Ya' : 'Tidak'
        ]
    ]);
    exit();
}