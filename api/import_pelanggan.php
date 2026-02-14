<?php
/**
 * API Import Pelanggan Excel
 * Menggunakan PhpSpreadsheet
 */

require '../vendor/autoload.php';
require 'util/db.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'File upload error']);
    exit();
}

$file = $_FILES['file']['tmp_name'];

try {
    // Load Excel File
    $spreadsheet = IOFactory::load($file);
    $worksheet = $spreadsheet->getActiveSheet();
    $rows = $worksheet->toArray();
    
    // Header Verification
    $header = $rows[0] ?? [];
    if (count($header) < 5 || $header[1] !== 'ID Pelanggan (HPM)' || $header[2] !== 'Nama Lengkap') {
        echo json_encode(['success' => false, 'message' => 'Format template Excel tidak valid. Silakan unduh template resmi.']);
        exit();
    }
    
    // Begin Transaction
    $pdo->beginTransaction();
    
    $successCount = 0;
    $errors = [];
    
    // Loop starting from row 2 (index 1)
    for ($i = 1; $i < count($rows); $i++) {
        $row = $rows[$i];
        
        // Skip empty rows
        if (empty($row[1]) && empty($row[2])) continue;
        
        $id_pelanggan = trim($row[1]); // ID HPM (No Sambungan)
        $nama = trim($row[2]);         // Nama
        $no_telp = trim($row[3]);      // No Telp
        $alamat = trim($row[4]);       // Alamat
        
        // Validation: Empty Fields
        if (empty($id_pelanggan) || empty($nama)) {
            $errors[] = "Baris " . ($i+1) . ": ID atau Nama kosong";
            continue;
        }
        
        // Validation: Duplicate ID in DB
        $stmt = $pdo->prepare("SELECT id FROM pelanggans WHERE customer_id = ?");
        $stmt->execute([$id_pelanggan]);
        if ($stmt->fetch()) {
            $errors[] = "Baris " . ($i+1) . ": ID ($id_pelanggan) sudah ada";
            continue;
        }
        
        // Format Phone (08 -> 628)
        $no_telp = preg_replace('/^0/', '62', $no_telp);
        $no_telp = preg_replace('/[^0-9]/', '', $no_telp);
        
        // Insert DB
        // Defaulting Type to 'R2' (Rumah Tangga) as it is required but not in Excel template
        $stmt = $pdo->prepare("
            INSERT INTO pelanggans (customer_id, name, phone, address, type, created_at, updated_at)
            VALUES (?, ?, ?, ?, 'R2', NOW(), NOW())
        ");
        
        if ($stmt->execute([$id_pelanggan, $nama, $no_telp, $alamat])) {
            $successCount++;
        } else {
            $errors[] = "Baris " . ($i+1) . ": Gagal simpan ke database";
        }
    }
    
    if ($successCount > 0 || empty($errors)) {
        $pdo->commit();
        $message = "Berhasil mengimport $successCount data pelanggan.";
        if (count($errors) > 0) {
            $message .= " Namun ada " . count($errors) . " baris gagal.";
        }
        
        echo json_encode([
            'success' => true, 
            'message' => $message,
            'details' => $errors
        ]);
    } else {
        $pdo->rollback();
        echo json_encode([
            'success' => false, 
            'message' => 'Tidak ada data yang berhasil diimport.',
            'details' => $errors
        ]);
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollback();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Import Error: ' . $e->getMessage()]);
}
?>
