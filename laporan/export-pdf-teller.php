<?php
require_once "../config/database.php"; // Koneksi ke database
require_once "../vendor/autoload.php"; // Autoload Dompdf

use Dompdf\Dompdf;
use Dompdf\Options;

session_start();

// Pastikan user sudah login
if (!isset($_SESSION['role_id']) || !isset($_SESSION['cabang_id'])) {
    header("Location: ../login.php");
    exit;
}

$role_id = $_SESSION['role_id'];
$cabang_id = $_SESSION['cabang_id'];

// Inisialisasi variabel untuk filter
$tanggal_awal = isset($_GET['tanggal_awal']) ? $_GET['tanggal_awal'] : null;
$tanggal_akhir = isset($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : null;
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : null;
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : null;
$filter_bagian = isset($_GET['bagian']) ? $_GET['bagian'] : null;
$filter_cabang = isset($_GET['cabang_id']) ? $_GET['cabang_id'] : ($role_id != 1 ? $cabang_id : null);

// Query dasar untuk mendapatkan data antrian teller
$query = "SELECT * FROM tbl_antrian_teller WHERE waktu_mulai IS NOT NULL AND waktu_selesai IS NOT NULL";

// Tambahkan filter cabang jika role_id bukan 1 atau jika superadmin menggunakan filter cabang
if (!empty($filter_cabang)) {
    $query .= " AND cabang_id = ?";
}

// Tambahkan filter tanggal awal dan akhir
if (!empty($tanggal_awal) && !empty($tanggal_akhir)) {
    $query .= " AND tanggal_teller BETWEEN ? AND ?";
}

// Tambahkan filter bulan
if (!empty($bulan)) {
    $query .= " AND MONTH(tanggal_teller) = ?";
}

// Tambahkan filter tahun
if (!empty($tahun)) {
    $query .= " AND YEAR(tanggal_teller) = ?";
}

// Tambahkan filter bagian (hanya untuk cabang 312)
if ($cabang_id == 312 && !empty($filter_bagian)) {
    $query .= " AND bagian = ?";
}

$query .= " ORDER BY tanggal_teller ASC, no_antrian_teller ASC";

$stmt = $mysqli->prepare($query);

// Bind parameter ke query
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
if ($cabang_id == 312 && !empty($filter_bagian)) {
    $bind_types .= 's';
    $params[] = $filter_bagian;
}

if (!empty($params)) {
    $stmt->bind_param($bind_types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Mulai membangun konten HTML untuk PDF
$html = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Teller</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h2 style="text-align: center;">Laporan Teller</h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Cabang ID</th>
                <th>Tanggal</th>
                <th>No Antrian</th>';

// Kolom Bagian hanya untuk cabang 312
if ($cabang_id == 312) {
    $html .= '<th>Bagian</th>';
}

$html .= '
                <th>Status</th>
                <th>Durasi</th>
            </tr>
        </thead>
        <tbody>';

// Isi tabel dengan data
$nomor = 1;
$previous_date = null;

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $html .= "<tr>";
        $html .= "<td>{$nomor}</td>";
        $html .= "<td>{$row['cabang_id']}</td>";
        $html .= "<td>" . date('d/m/Y', strtotime($row['tanggal_teller'])) . "</td>";
        $html .= "<td>{$row['no_antrian_teller']}</td>";
        if ($cabang_id == 312) {
            $html .= "<td>" . ($row['bagian'] ?: '-') . "</td>";
        }
        $html .= "<td>" . ($row['status_teller'] == '2' ? 'Selesai' : 'Menunggu') . "</td>";
        // Hitung durasi
        if (!empty($row['durasi'])) {
            $d = (int)$row['durasi'];
            $formatted_duration = sprintf("%02d:%02d:%02d", floor($d / 3600), floor(($d % 3600) / 60), $d % 60);
        } else if (!empty($row['waktu_mulai']) && !empty($row['waktu_selesai'])) {
            $mulai = strtotime($row['waktu_mulai']);
            $selesai = strtotime($row['waktu_selesai']);
            $d = $selesai - $mulai;
            $formatted_duration = sprintf("%02d:%02d:%02d", floor($d / 3600), floor(($d % 3600) / 60), $d % 60);
        } else {
            $formatted_duration = "-";
        }
        $html .= "<td>{$formatted_duration}</td>";
        $html .= "</tr>";
        $nomor++;
    }
} else {
    $html .= '<tr><td colspan="7" style="text-align: center;">Tidak ada data tersedia</td></tr>';
}

$html .= '
        </tbody>
    </table>
</body>
</html>';

// Inisialisasi Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

// Load konten HTML ke Dompdf
$dompdf->loadHtml($html);

// Set orientasi dan ukuran kertas
$dompdf->setPaper('A4', 'portrait');

// Render PDF
$dompdf->render();

// Kirim file PDF ke browser untuk diunduh
$dompdf->stream("laporan_teller.pdf", ["Attachment" => true]);
