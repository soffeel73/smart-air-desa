<?php
// DB Connection for InfinityFree (Production)
// UPDATE THESE VALUES WITH YOUR INFINITYFREE DATABASE DETAILS
$host = 'sqlXXX.infinityfree.com'; // Change this
$dbname = 'if0_38246473_XXX';      // Change this
$username = 'if0_38246473';        // Change this
$password = 'YOUR_PASSWORD';       // Change this

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // In production, don't show detailed error messages to users
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed. Check config.']);
    exit();
}
?>
