<?php
require 'PhpSpreadsheet-master/vendor/autoload.php'; // adjust path if needed
include("db.php");

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

if ($_SESSION['role'] != 'supervisor') {
    die("Access denied.");
}

// Fetch all IPCRF entries
$sql = "SELECT u.name, e.* FROM ipcrf_entries e 
        JOIN users u ON e.user_id = u.id";
$result = $conn->query($sql);

// Create spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Header row
$headers = ['Teacher', 'Objective', 'Performance Indicator', 'Rating', 'Remarks'];
$col = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($col.'1', $header);
    $col++;
}

// Style header
$sheet->getStyle('A1:E1')->applyFromArray([
    'font' => ['bold' => true],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FFD700'] // gold background
    ],
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN]
    ]
]);

// Fill data
$row = 2;
while ($data = $result->fetch_assoc()) {
    $sheet->setCellValue("A$row", $data['name']);
    $sheet->setCellValue("B$row", $data['objective']);
    $sheet->setCellValue("C$row", $data['performance_indicator']);
    $sheet->setCellValue("D$row", $data['rating']);
    $sheet->setCellValue("E$row", $data['remarks']);
    $row++;
}

// Auto column width
foreach (range('A','E') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Export to Excel
$writer = new Xlsx($spreadsheet);
$filename = "IPCRF_Report.xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
$writer->save("php://output");
exit;
?>