<?php
session_start();
require_once "../config/database.php";
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Get filter values
$filter_cabang = isset($_GET['cabang_id']) ? $_GET['cabang_id'] : null;
$filter_role = isset($_GET['role_id']) ? $_GET['role_id'] : null;
$filter_jabatan = isset($_GET['jabatan_id']) ? $_GET['jabatan_id'] : null;

// Build query
$userQuery = "
    SELECT users.id, users.id_pegawai, users.username, role.nama AS role, cabang.nama AS cabang, 
           users.role_id, users.cabang_id, p.id_jabatan, j.jabatan
    FROM users
    JOIN role ON users.role_id = role.role_id
    JOIN cabang ON users.cabang_id = cabang.id
    LEFT JOIN bprsukab_eis.pegawai p ON users.id_pegawai = p.id_pegawai
    LEFT JOIN bprsukab_eis.jabatan j ON p.id_jabatan = j.id_jabatan
    WHERE 1=1
";

$params = [];
$types = '';

if (!empty($filter_cabang)) {
    $userQuery .= " AND users.cabang_id = ?";
    $params[] = $filter_cabang;
    $types .= 'i';
}

if (!empty($filter_role)) {
    $userQuery .= " AND users.role_id = ?";
    $params[] = $filter_role;
    $types .= 'i';
}

if (!empty($filter_jabatan)) {
    $userQuery .= " AND p.id_jabatan = ?";
    $params[] = $filter_jabatan;
    $types .= 'i';
}

$userQuery .= " ORDER BY users.id ASC";

if (!empty($params)) {
    $stmt = $mysqli->prepare($userQuery);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $mysqli->query($userQuery);
}

// Build filter description
$filter_desc = [];
if (!empty($filter_cabang)) {
    $cabang_query = $mysqli->prepare("SELECT nama FROM cabang WHERE id = ?");
    $cabang_query->bind_param("i", $filter_cabang);
    $cabang_query->execute();
    $cabang_result = $cabang_query->get_result();
    if ($cabang_row = $cabang_result->fetch_assoc()) {
        $filter_desc[] = "Cabang: " . $cabang_row['nama'];
    }
}
if (!empty($filter_role)) {
    $role_query = $mysqli->prepare("SELECT nama FROM role WHERE role_id = ?");
    $role_query->bind_param("i", $filter_role);
    $role_query->execute();
    $role_result = $role_query->get_result();
    if ($role_row = $role_result->fetch_assoc()) {
        $filter_desc[] = "Role: " . $role_row['nama'];
    }
}
if (!empty($filter_jabatan)) {
    $jabatan_query = $mysqli_eis->prepare("SELECT jabatan FROM bprsukab_eis.jabatan WHERE id_jabatan = ?");
    $jabatan_query->bind_param("i", $filter_jabatan);
    $jabatan_query->execute();
    $jabatan_result = $jabatan_query->get_result();
    if ($jabatan_row = $jabatan_result->fetch_assoc()) {
        $filter_desc[] = "Jabatan: " . $jabatan_row['jabatan'];
    }
}

$filter_text = !empty($filter_desc) ? implode(", ", $filter_desc) : "Semua Data";

// Create spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set title
$sheet->setCellValue('A1', 'Laporan Manajemen User');
$sheet->mergeCells('A1:F1');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Set subtitle
$sheet->setCellValue('A2', 'BPR Sukabumi');
$sheet->mergeCells('A2:F2');
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Set filter info
$sheet->setCellValue('A3', 'Filter: ' . $filter_text);
$sheet->mergeCells('A3:F3');
$sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A3')->getFont()->setItalic(true);

// Set headers
$headers = ['No', 'ID Pegawai', 'Username', 'Role', 'Cabang', 'Jabatan'];
$column = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($column . '5', $header);
    $sheet->getStyle($column . '5')->getFont()->setBold(true);
    $sheet->getStyle($column . '5')->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FFE0E0E0');
    $sheet->getStyle($column . '5')->getBorders()->getAllBorders()
        ->setBorderStyle(Border::BORDER_THIN);
    $column++;
}

// Add data
$row = 6;
$nomor = 1;
while ($data = $result->fetch_assoc()) {
    $sheet->setCellValue('A' . $row, $nomor);
    $sheet->setCellValue('B' . $row, $data['id_pegawai'] ?? '-');
    $sheet->setCellValue('C' . $row, $data['username']);
    $sheet->setCellValue('D' . $row, $data['role']);
    $sheet->setCellValue('E' . $row, $data['cabang']);
    $sheet->setCellValue('F' . $row, $data['jabatan'] ?? '-');
    
    // Add borders
    $sheet->getStyle('A' . $row . ':F' . $row)->getBorders()->getAllBorders()
        ->setBorderStyle(Border::BORDER_THIN);
    
    $row++;
    $nomor++;
}

// Auto-size columns
foreach (range('A', 'F') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Add footer
$sheet->setCellValue('A' . ($row + 1), 'Dicetak pada: ' . date('d/m/Y H:i:s'));
$sheet->mergeCells('A' . ($row + 1) . ':F' . ($row + 1));
$sheet->getStyle('A' . ($row + 1))->getFont()->setSize(9)->setItalic(true);

// Output file
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Laporan_User_' . date('Ymd_His') . '.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
