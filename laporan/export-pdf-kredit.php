<?php
require_once "../config/database.php";
require_once "../vendor/autoload.php";

use Dompdf\Dompdf;
use Dompdf\Options;

session_start();
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
$html = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Laporan Kredit</title><style>body{font-family:Arial,sans-serif;}table{border-collapse:collapse;width:100%;}th,td{border:1px solid #000;padding:6px;text-align:center;}th{background:#eee;}</style></head><body><h2>Laporan Kredit</h2><table><thead><tr><th>No</th><th>Cabang ID</th><th>Tanggal</th><th>No Antrian</th><th>Waktu Mulai</th><th>Waktu Selesai</th><th>Status</th><th>Durasi</th></tr></thead><tbody>';
$nomor = 1;
while ($row = $result->fetch_assoc()) {
    $html .= '<tr>';
    $html .= '<td>' . $nomor++ . '</td>';
    $html .= '<td>' . htmlspecialchars($row['cabang_id']) . '</td>';
    // Tanggal dd/mm/yy
    $tgl_fmt = !empty($row['tanggal_kredit']) ? date('d/m/y', strtotime($row['tanggal_kredit'])) : '-';
    $html .= '<td>' . $tgl_fmt . '</td>';
    $html .= '<td>' . htmlspecialchars($row['no_antrian_kredit']) . '</td>';
    // Format waktu ke H:i:s jika tersedia
    $wm = !empty($row['waktu_mulai']) ? date('H:i:s', strtotime($row['waktu_mulai'])) : '-';
    $ws = !empty($row['waktu_selesai']) ? date('H:i:s', strtotime($row['waktu_selesai'])) : '-';
    $html .= '<td>' . $wm . '</td>';
    $html .= '<td>' . $ws . '</td>';
    $html .= '<td>' . ($row['status_kredit'] == '2' ? 'Selesai' : 'Menunggu') . '</td>';
    // Format durasi ke HH:MM:SS (atau hitung jika perlu)
    if (!empty($row['durasi'])) {
        $d = (int)$row['durasi'];
        $formatted_duration = sprintf('%02d:%02d:%02d', floor($d / 3600), floor(($d % 3600) / 60), $d % 60);
    } elseif (!empty($row['waktu_mulai']) && !empty($row['waktu_selesai'])) {
        $mulai = strtotime($row['waktu_mulai']);
        $selesai = strtotime($row['waktu_selesai']);
        $d = max(0, $selesai - $mulai);
        $formatted_duration = sprintf('%02d:%02d:%02d', floor($d / 3600), floor(($d % 3600) / 60), $d % 60);
    } else {
        $formatted_duration = '-';
    }
    $html .= '<td>' . $formatted_duration . '</td>';
    $html .= '</tr>';
}
$html .= '</tbody></table></body></html>';
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream('laporan_kredit.pdf', ['Attachment' => 1]);
