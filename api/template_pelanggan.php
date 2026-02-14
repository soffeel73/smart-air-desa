<?php
/**
 * API Download Template Excel Pelanggan
 * Menggunakan PhpSpreadsheet
 */

require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="template_pelanggan.xlsx"');
header('Cache-Control: max-age=0');

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set Header
$headers = ['No', 'ID Pelanggan (HPM)', 'Nama Lengkap', 'No. Telepon', 'Alamat'];
$sheet->fromArray($headers, NULL, 'A1');

// Style Header
$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '2EC4B6'] // Sea Green theme
    ],
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN]
    ]
];
$sheet->getStyle('A1:E1')->applyFromArray($headerStyle);

// Set Column Width
$sheet->getColumnDimension('A')->setWidth(5);
$sheet->getColumnDimension('B')->setWidth(20);
$sheet->getColumnDimension('C')->setWidth(30);
$sheet->getColumnDimension('D')->setWidth(15);
$sheet->getColumnDimension('E')->setWidth(40);

// Add Example Data
$examples = [
    [1, 'HPM001', 'BUDI SANTOSO', '081234567890', 'Dusun Gempolpayung RT 01 RW 01'],
    [2, 'HPM002', 'SITI AMINAH', '6285712345678', 'Desa Sarirejo']
];
$sheet->fromArray($examples, NULL, 'A2');

// Add Data Validation info (optional comments)
$sheet->getComment('B1')->getText()->createTextRun('ID Pelanggan harus unik (No Sambungan)');
$sheet->getComment('D1')->getText()->createTextRun('Format: 08xx atau 628xx');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
?>
