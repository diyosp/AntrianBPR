<?php
session_start();
require_once "../config/database.php";
require_once '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

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

// Generate HTML
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Manajemen User</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h2 { text-align: center; margin-bottom: 5px; }
        h3 { text-align: center; margin-top: 5px; font-weight: normal; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .filter-info { text-align: center; margin: 10px 0; font-style: italic; }
    </style>
</head>
<body>
    <h2>Laporan Manajemen User</h2>
    <h3>BPR Sukabumi</h3>
    <div class="filter-info">Filter: ' . htmlspecialchars($filter_text) . '</div>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>ID Pegawai</th>
                <th>Username</th>
                <th>Role</th>
                <th>Cabang</th>
                <th>Jabatan</th>
            </tr>
        </thead>
        <tbody>';

$nomor = 1;
while ($row = $result->fetch_assoc()) {
    $html .= '<tr>';
    $html .= '<td>' . $nomor . '</td>';
    $html .= '<td>' . htmlspecialchars($row['id_pegawai'] ?? '-') . '</td>';
    $html .= '<td>' . htmlspecialchars($row['username']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['role']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['cabang']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['jabatan'] ?? '-') . '</td>';
    $html .= '</tr>';
    $nomor++;
}

$html .= '</tbody></table>';
$html .= '<p style="margin-top: 20px; font-size: 10px;">Dicetak pada: ' . date('d/m/Y H:i:s') . '</p>';
$html .= '</body></html>';

// Generate PDF
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

$dompdf->stream("Laporan_User_" . date('Ymd_His') . ".pdf", array("Attachment" => true));
