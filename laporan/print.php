<?php
session_start();
require_once "../config/database.php"; // Pastikan path ke file database.php benar

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
$filter_cabang = isset($_GET['cabang_id']) ? $_GET['cabang_id'] : ($role_id != 1 ? $cabang_id : null);

// Query dasar untuk mendapatkan data antrian
$query = "SELECT * FROM tbl_antrian WHERE waktu_mulai IS NOT NULL AND waktu_selesai IS NOT NULL";

// Tambahkan filter cabang jika role_id bukan 1 atau jika superadmin menggunakan filter cabang
if (!empty($filter_cabang)) {
    $query .= " AND cabang_id = ?";
}

// Tambahkan filter tanggal awal dan akhir
if (!empty($tanggal_awal) && !empty($tanggal_akhir)) {
    $query .= " AND tanggal BETWEEN ? AND ?";
}

// Tambahkan filter bulan
if (!empty($bulan)) {
    $query .= " AND MONTH(tanggal) = ?";
}

// Tambahkan filter tahun
if (!empty($tahun)) {
    $query .= " AND YEAR(tanggal) = ?";
}

$query .= " ORDER BY tanggal ASC, no_antrian ASC";

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

if (!empty($params)) {
    $stmt->bind_param($bind_types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Laporan Customer Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            @page {
                margin: 1cm;
                /* Margin halaman saat dicetak */
            }
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body onload="window.print();">
    <h2>Laporan Customer Service</h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Cabang ID</th>
                <th>Tanggal</th>
                <th>No Antrian</th>
                <th>Waktu Mulai</th>
                <th>Waktu Selesai</th>
                <th>Status</th>
                <th>Durasi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                $nomor = 1;
                $previous_date = null;

                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>{$nomor}</td>";
                    echo "<td>{$row['cabang_id']}</td>";
                    echo "<td>" . date('d/m/Y', strtotime($row['tanggal'])) . "</td>";
                    echo "<td>{$row['no_antrian']}</td>";
                    // Waktu Mulai
                    echo "<td>" . (!empty($row['waktu_mulai']) ? date('H:i:s', strtotime($row['waktu_mulai'])) : '-') . "</td>";
                    // Waktu Selesai
                    echo "<td>" . (!empty($row['waktu_selesai']) ? date('H:i:s', strtotime($row['waktu_selesai'])) : '-') . "</td>";
                    echo "<td>" . ($row['status'] == '2' ? 'Selesai' : 'Menunggu') . "</td>";

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
                    echo "<td>{$formatted_duration}</td>";
                    $nomor++;
                }
            } else {
                echo "<tr><td colspan='8' class='text-center'>Tidak ada data tersedia</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>

</html>