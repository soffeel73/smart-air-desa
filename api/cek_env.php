<?php
header('Content-Type: application/json');
echo json_encode([
    'DB_HOST_ENV' => getenv('DB_HOST') ?: 'TIDAK TERBACA',
    'DB_PORT_ENV' => getenv('DB_PORT') ?: 'TIDAK TERBACA',
    'DOCUMENT_ROOT' => $_SERVER['DOCUMENT_ROOT'],
    'PHP_VERSION' => phpversion()
]);