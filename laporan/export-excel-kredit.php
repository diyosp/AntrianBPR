<?php
session_start();
require_once "../config/database.php";
require_once "../vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!isset($_SESSION['role_id']) || !isset($_SESSION['cabang_id'])) {
    header("Location: ../login.php");
    exit;
}

$role_id = $_SESSION['role_id'];
$cabang_id = $_SESSION['cabang_id'];

$tanggal_awal = isset($_GET['tanggal_awal']) ? $_GET['tanggal_awal'] : null;
$tanggal_akhir = isset($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : null;
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : null;
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : null;
$filter_cabang = isset($_GET['cabang_id']) ? $_GET['cabang_id'] : ($role_id != 1 ? $cabang_id : null);

$query = "SELECT * FROM tbl_antrian_kredit WHERE waktu_mulai IS NOT NULL AND waktu_selesai IS NOT NULL";
if (!empty($filter_cabang)) {
    $query .= " AND cabang_id = ?";
}
if (!empty($tanggal_awal) && !empty($tanggal_akhir)) {
    $query .= " AND tanggal_kredit BETWEEN ? AND ?";
}
if (!empty($bulan)) {
    $query .= " AND MONTH(tanggal_kredit) = ?";
}
if (!empty($tahun)) {
    $query .= " AND YEAR(tanggal_kredit) = ?";
}
$query .= " ORDER BY tanggal_kredit ASC, no_antrian_kredit ASC";

$stmt = $mysqli->prepare($query);
$bind_types = '';
$params = [];
if (!empty($filter_cabang)) {
    $bind_types .= 'i';
    $params[] = $filter_cabang;
}
if (!empty($tanggal_awal) && !empty($tanggal_akhir)) {
    $bind_types .= 'ss';
    $params[] = $tanggal_awal;
    $params[] = $tanggal_akhir;
}
if (!empty($bulan)) {
    $bind_types .= 'i';
    $params[] = $bulan;
}
if (!empty($tahun)) {
    $bind_types .= 'i';
    $params[] = $tahun;
}
if (!empty($params)) {
    $stmt->bind_param($bind_types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A1', 'No');
$sheet->setCellValue('B1', 'Cabang ID');
$sheet->setCellValue('C1', 'Tanggal');
$sheet->setCellValue('D1', 'No Antrian');
$sheet->setCellValue('E1', 'Waktu Mulai');
$sheet->setCellValue('F1', 'Waktu Selesai');
$sheet->setCellValue('G1', 'Status');
$sheet->setCellValue('H1', 'Durasi');
$rowNum = 2;
$nomor = 1;
while ($row = $result->fetch_assoc()) {
    $sheet->setCellValue('A' . $rowNum, $nomor++);
    $sheet->setCellValue('B' . $rowNum, $row['cabang_id']);
    $sheet->setCellValue('C' . $rowNum, $row['tanggal_kredit']);
    $sheet->setCellValue('D' . $rowNum, $row['no_antrian_kredit']);
    $sheet->setCellValue('E' . $rowNum, $row['waktu_mulai']);
    $sheet->setCellValue('F' . $rowNum, $row['waktu_selesai']);
    $sheet->setCellValue('G' . $rowNum, $row['status_kredit'] == '2' ? 'Selesai' : 'Menunggu');
    $sheet->setCellValue('H' . $rowNum, $row['durasi']);
    $rowNum++;
}
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="laporan_kredit.xlsx"');
header('Cache-Control: max-age=0');
$writer->save('php://output');
