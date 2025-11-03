<?php
session_start();
require_once "../config/database.php";
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
?><!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Print Laporan Kredit</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"><style>@media print {@page {size: A4;margin: 1cm;}}</style></head><body><div class="container mt-4"><h2>Print Laporan Kredit</h2><table class="table table-bordered table-striped"><thead><tr><th>No</th><th>Cabang ID</th><th>Tanggal</th><th>No Antrian</th><th>Waktu Mulai</th><th>Waktu Selesai</th><th>Status</th><th>Durasi</th></tr></thead><tbody><?php if ($result->num_rows > 0) { $nomor = 1; while ($row = $result->fetch_assoc()) { echo "<tr>"; echo "<td>{$nomor}</td>"; echo "<td>{$row['cabang_id']}</td>"; echo "<td>" . date('d/m/Y', strtotime($row['tanggal_kredit'])) . "</td>"; echo "<td>{$row['no_antrian_kredit']}</td>"; echo "<td>" . (!empty($row['waktu_mulai']) ? date('H:i:s', strtotime($row['waktu_mulai'])) : '-') . "</td>"; echo "<td>" . (!empty($row['waktu_selesai']) ? date('H:i:s', strtotime($row['waktu_selesai'])) : '-') . "</td>"; echo "<td>" . ($row['status_kredit'] == '2' ? 'Selesai' : 'Menunggu') . "</td>"; echo "<td>{$row['durasi']}</td>"; echo "</tr>"; $nomor++; } } ?></tbody></table></div></body></html>
