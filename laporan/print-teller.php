<?php
session_start();
require_once "../config/database.php"; // Koneksi ke database

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

// Tambahkan filter bagian (khusus cabang 312)
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Laporan Teller</title>
    <style>
        @media print {
            @page {
                size: A4 portrait;
                margin: 1.5cm 1cm;
            }
            
            .no-print {
                display: none;
            }
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #333;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #2c3e50;
            padding-bottom: 15px;
        }

        .header h1 {
            font-size: 20pt;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .header .subtitle {
            font-size: 11pt;
            color: #7f8c8d;
            margin-top: 5px;
        }

        .filter-info {
            background-color: #f8f9fa;
            padding: 10px 15px;
            border-left: 4px solid #3498db;
            margin-bottom: 20px;
            font-size: 10pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table thead {
            background-color: #34495e;
            color: white;
        }

        table th {
            padding: 12px 8px;
            text-align: left;
            font-weight: 600;
            font-size: 10pt;
            border: 1px solid #2c3e50;
        }

        table td {
            padding: 10px 8px;
            border: 1px solid #ddd;
            font-size: 10pt;
        }

        table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        table tbody tr:hover {
            background-color: #e9ecef;
        }

        .text-center {
            text-align: center;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #ecf0f1;
            font-size: 9pt;
            color: #7f8c8d;
        }

        .print-date {
            text-align: right;
            font-style: italic;
        }
    </style>
</head>

<body onload="window.print();">
    <div class="header">
        <h1>Laporan Teller</h1>
        <div class="subtitle">Sistem Antrian BPR</div>
    </div>

    <?php
    $filter_text = [];
    if (!empty($filter_cabang)) $filter_text[] = "Cabang: " . htmlspecialchars($filter_cabang);
    if (!empty($tanggal_awal) && !empty($tanggal_akhir)) {
        $filter_text[] = "Periode: " . date('d/m/Y', strtotime($tanggal_awal)) . " - " . date('d/m/Y', strtotime($tanggal_akhir));
    }
    if (!empty($bulan)) {
        $bulan_nama = date('F', mktime(0, 0, 0, $bulan, 1));
        $filter_text[] = "Bulan: " . $bulan_nama;
    }
    if (!empty($tahun)) $filter_text[] = "Tahun: " . $tahun;
    if (!empty($filter_bagian)) $filter_text[] = "Bagian: " . htmlspecialchars($filter_bagian);
    
    if (!empty($filter_text)):
    ?>
    <div class="filter-info">
        <strong>Filter:</strong> <?= implode(' | ', $filter_text) ?>
    </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 10%;">Cabang</th>
                <th style="width: 11%;">Tanggal</th>
                <th style="width: 11%;">No Antrian</th>
                <?php if ($cabang_id == 312): ?>
                    <th style="width: 10%;">Bagian</th>
                <?php endif; ?>
                <th style="width: 11%;">Waktu Mulai</th>
                <th style="width: 11%;">Waktu Selesai</th>
                <th style="width: 10%;">Status</th>
                <th style="width: 10%;">Durasi</th>
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
                    echo "<td>" . date('d/m/Y', strtotime($row['tanggal_teller'])) . "</td>";
                    echo "<td>{$row['no_antrian_teller']}</td>";

                    // Kolom Bagian khusus untuk cabang 312
                    if ($cabang_id == 312) {
                        echo "<td>" . ($row['bagian'] ?: '-') . "</td>";
                    }

                    // Waktu Mulai
                    echo "<td>" . (!empty($row['waktu_mulai']) ? date('H:i:s', strtotime($row['waktu_mulai'])) : '-') . "</td>";
                    // Waktu Selesai
                    echo "<td>" . (!empty($row['waktu_selesai']) ? date('H:i:s', strtotime($row['waktu_selesai'])) : '-') . "</td>";

                    echo "<td>" . ($row['status_teller'] == '2' ? 'Selesai' : 'Menunggu') . "</td>";

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
                    echo "<td>{$formatted_duration}</td>";
                    echo "</tr>";
                    $nomor++;
                }
            } else {
                // Jumlah kolom tergantung cabang_id
                $colspan = ($cabang_id == 312) ? 9 : 8;
                echo "<tr><td colspan='{$colspan}' class='text-center'>Tidak ada data tersedia</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <div class="footer">
        <div class="print-date">
            Dicetak pada: <?= date('d/m/Y H:i:s') ?>
        </div>
    </div>
</body>

</html>