<?php
/**
 * Cetak Struk API - HIPPAMS TIRTO JOYO
 * Handles receipt printing data for billing
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Database connection
require_once 'util/db.php';

$action = isset($_GET['action']) ? $_GET['action'] : 'search';

switch ($action) {
    case 'search':
        searchPelanggan($pdo);
        break;
    case 'detail':
        getStrukDetail($pdo);
        break;
    case 'periods':
        getPeriods($pdo);
        break;
    default:
        searchPelanggan($pdo);
}

/**
 * Search pelanggan by name or customer_id
 */
function searchPelanggan($pdo) {
    $query = isset($_GET['q']) ? trim($_GET['q']) : '';
    
    try {
        $sql = "SELECT id, customer_id, name, address, phone, type 
                FROM pelanggans 
                WHERE (customer_id LIKE :q OR name LIKE :q2 OR LOWER(name) LIKE :q3)
                ORDER BY customer_id ASC
                LIMIT 20";
        
        $stmt = $pdo->prepare($sql);
        $searchTerm = "%{$query}%";
        $stmt->execute([
            ':q' => $searchTerm,
            ':q2' => $searchTerm,
            ':q3' => strtolower($searchTerm)
        ]);
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $results
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Search failed']);
    }
}

/**
 * Get available periods for a pelanggan
 */
function getPeriods($pdo) {
    $pelangganId = isset($_GET['pelanggan_id']) ? intval($_GET['pelanggan_id']) : 0;
    
    if (!$pelangganId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Pelanggan ID required']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT id, period_year, period_month, meter_awal, meter_akhir, jumlah_pakai, total_biaya
            FROM input_meters
            WHERE pelanggan_id = :id
            ORDER BY period_year DESC, period_month DESC
        ");
        $stmt->execute([':id' => $pelangganId]);
        $periods = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $periods]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to get periods']);
    }
}

/**
 * Get full struk detail for printing
 */
function getStrukDetail($pdo) {
    $inputMeterId = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if (!$inputMeterId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Input meter ID required']);
        return;
    }
    
    try {
        // Get meter and customer data
        $stmt = $pdo->prepare("
            SELECT 
                m.id as input_meter_id,
                m.meter_awal,
                m.meter_akhir,
                m.jumlah_pakai,
                m.total_biaya,
                m.period_month,
                m.period_year,
                p.id as pelanggan_id,
                p.customer_id,
                p.name,
                p.address,
                p.phone,
                p.type,
                t.tunggakan,
                t.total_tagihan,
                t.status
            FROM input_meters m
            JOIN pelanggans p ON p.id = m.pelanggan_id
            LEFT JOIN tagihans t ON t.input_meter_id = m.id
            WHERE m.id = :id
        ");
        $stmt->execute([':id' => $inputMeterId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$data) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan']);
            return;
        }
        
        // Calculate tariff breakdown
        $pemakaian = intval($data['jumlah_pakai']);
        $tarif = calculateTarifBreakdown($pemakaian);
        
        // Add breakdown to response
        $data['tarif_breakdown'] = $tarif;
        $data['biaya_admin'] = 2000;
        $data['subtotal_pemakaian'] = $tarif['total'];
        $data['total_biaya_calculated'] = $tarif['total'] + 2000; // pemakaian + admin
        $data['tunggakan'] = floatval($data['tunggakan'] ?? 0);
        $data['total_tagihan'] = floatval($data['total_tagihan'] ?? ($data['total_biaya_calculated'] + $data['tunggakan']));
        $data['terbilang'] = terbilang($data['total_tagihan']);
        $data['tgl_cetak'] = date('d F Y H:i');
        
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to get detail: ' . $e->getMessage()]);
    }
}

/**
 * Calculate tariff breakdown based on HIPPAMS TIRTO JOYO rates
 * Pemakaian 1: 1-5 M³ @ Rp 1.500
 * Pemakaian 2: 6-10 M³ @ Rp 2.000
 * Pemakaian 3: >10 M³ @ Rp 2.500
 */
function calculateTarifBreakdown($pemakaian) {
    $breakdown = [
        'pemakaian1' => ['qty' => 0, 'rate' => 1500, 'subtotal' => 0],
        'pemakaian2' => ['qty' => 0, 'rate' => 2000, 'subtotal' => 0],
        'pemakaian3' => ['qty' => 0, 'rate' => 2500, 'subtotal' => 0],
        'total' => 0
    ];
    
    if ($pemakaian <= 0) {
        return $breakdown;
    }
    
    // Pemakaian 1: 1-5 M³
    if ($pemakaian >= 1) {
        $qty1 = min($pemakaian, 5);
        $breakdown['pemakaian1']['qty'] = $qty1;
        $breakdown['pemakaian1']['subtotal'] = $qty1 * 1500;
    }
    
    // Pemakaian 2: 6-10 M³
    if ($pemakaian > 5) {
        $qty2 = min($pemakaian - 5, 5);
        $breakdown['pemakaian2']['qty'] = $qty2;
        $breakdown['pemakaian2']['subtotal'] = $qty2 * 2000;
    }
    
    // Pemakaian 3: >10 M³
    if ($pemakaian > 10) {
        $qty3 = $pemakaian - 10;
        $breakdown['pemakaian3']['qty'] = $qty3;
        $breakdown['pemakaian3']['subtotal'] = $qty3 * 2500;
    }
    
    $breakdown['total'] = $breakdown['pemakaian1']['subtotal'] + 
                          $breakdown['pemakaian2']['subtotal'] + 
                          $breakdown['pemakaian3']['subtotal'];
    
    return $breakdown;
}

/**
 * Convert number to Indonesian words (terbilang)
 */
function terbilang($angka) {
    $angka = abs(floatval($angka));
    $huruf = ["", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"];
    
    if ($angka < 12) {
        return $huruf[$angka];
    } else if ($angka < 20) {
        return terbilang($angka - 10) . " belas";
    } else if ($angka < 100) {
        return terbilang(floor($angka / 10)) . " puluh " . terbilang($angka % 10);
    } else if ($angka < 200) {
        return "seratus " . terbilang($angka - 100);
    } else if ($angka < 1000) {
        return terbilang(floor($angka / 100)) . " ratus " . terbilang($angka % 100);
    } else if ($angka < 2000) {
        return "seribu " . terbilang($angka - 1000);
    } else if ($angka < 1000000) {
        return terbilang(floor($angka / 1000)) . " ribu " . terbilang($angka % 1000);
    } else if ($angka < 1000000000) {
        return terbilang(floor($angka / 1000000)) . " juta " . terbilang($angka % 1000000);
    } else {
        return terbilang(floor($angka / 1000000000)) . " milyar " . terbilang($angka % 1000000000);
    }
}
?>
