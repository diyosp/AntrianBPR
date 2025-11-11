<?php
session_start();
require_once "../config/database.php"; // Koneksi ke database

// Pastikan user sudah login dan ambil role_id serta cabang_id dari sesi
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
    <title>Laporan Teller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
</head>

<body class="d-flex flex-column h-100" style="background-color: #081941;">
    <style>
        /* Page color scheme */
        .page-header {
            background-color: #11224E;
            color: #fff;
            padding: 1rem 1rem;
            border-radius: .5rem;
        }
        .page-header i { color: #fff; }
        .report-container .card {
            background-color: #11224E;
            color: #fff;
            border: 0;
        }
        .report-container table {
            color: #fff;
        }
        /* Ensure all table text (headers, body rows and cells) is white */
        .report-container table,
        .report-container table thead th,
        .report-container table tbody tr,
        .report-container table tbody td,
        .report-container table tbody th {
            color: #fff !important;
        }
        /* Keep striped backgrounds but force text white on odd/even rows */
        .report-container table.table-striped tbody tr:nth-of-type(odd),
        .report-container table.table-striped tbody tr:nth-of-type(even) {
            color: #fff !important;
        }
        .form-label { color: #fff; }
        .form-control, .form-select {
            background-color: #11224E !important;
            color: #fff !important;
            border: 1px solid rgba(255,255,255,0.15) !important;
        }
        .form-control:focus, .form-select:focus {
            background-color: #11224E !important;
            color: #fff !important;
            border: 1px solid rgba(255,255,255,0.3) !important;
            box-shadow: 0 0 0 0.25rem rgba(255,255,255,0.1) !important;
            outline: none !important;
        }
        .form-control:hover, .form-select:hover {
            background-color: #11224E !important;
            color: #fff !important;
            border: 1px solid rgba(255,255,255,0.3) !important;
        }
        /* Style the select dropdown when opened */
        select {
            background-color: #11224E !important;
            color: #fff !important;
            border: 1px solid rgba(255,255,255,0.15) !important;
        }
        select:hover {
            border: 1px solid rgba(255,255,255,0.3) !important;
        }
        select:focus {
            border: 1px solid rgba(255,255,255,0.3) !important;
            background-color: #11224E !important;
        }
        /* Dropdown options styling */
        .form-select option, select option {
            background-color: #11224E !important;
            color: #fff !important;
            border: none !important;
        }
        .btn-theme {
            background-color: #F87B1B;
            border-color: #F87B1B;
            color: #fff;
        }
        /* Remove hover/focus/active color change */
        .btn-theme:hover,
        .btn-theme:focus,
        .btn-theme:active {
            background-color: #F87B1B !important;
            border-color: #F87B1B !important;
            color: #fff !important;
            box-shadow: none !important;
        }
        .dropdown-menu { background-color: #11224E; color: #fff; }
        .dropdown-menu a { color: #fff; }
        /* Custom arrow (optional) */
        .report-container .form-select {
          background-image:
            linear-gradient(45deg, transparent 50%, #fff 50%),
            linear-gradient(135deg, #fff 50%, transparent 50%),
            linear-gradient(to right, #11224E, #11224E);
          background-position:
            calc(100% - 18px) calc(50% + 2px),
            calc(100% - 12px) calc(50% + 2px),
            100% 0;
          background-size: 6px 6px, 6px 6px, 2.5em 100%;
          background-repeat: no-repeat;
          padding-right: 2.8em;
        }
    </style>

    <div class="container pt-5 report-container">
        <div class="page-header d-flex align-items-center mb-4">
            <i class="bi-file-earmark-text me-3 fs-3"></i>
            <h1 class="h5 pt-2 mb-0">Laporan Teller</h1>
            <div class="text-end ms-auto">
                <a href="../" class="btn btn-outline-light btn-sm">Kembali</a>
            </div>
        </div>

        <!-- Form Filter -->
        <?php
            $isSuperAdmin = ($role_id == 1);
            $hasBagian = ($cabang_id == 312);
            if ($isSuperAdmin && $hasBagian) {
                $colCabang = 'col-md-2';
                $colTglAwal = 'col-md-2';
                $colTglAkhir = 'col-md-2';
                $colBulan = 'col-md-2';
                $colTahun = 'col-md-1';
                $colBagian = 'col-md-1';
                $colActions = 'col-md-2';
            } else {
                $colCabang = 'col-md-2';
                $colTglAwal = 'col-md-2';
                $colTglAkhir = 'col-md-2';
                $colBulan = 'col-md-2';
                $colTahun = 'col-md-2';
                $colBagian = 'col-md-2';
                $colActions = 'col-md-2';
            }
        ?>
        <form method="GET" class="row mb-4">
            <?php if ($role_id == 1): // Filter cabang hanya untuk super_admin 
            ?>
                <div class="<?= $colCabang ?>">
                    <label for="cabang_id" class="form-label">Cabang</label>
                    <select id="cabang_id" name="cabang_id" class="form-select">
                        <option value="">Semua Cabang</option>
                        <?php
                        $cabang_query = $mysqli->query("SELECT DISTINCT cabang_id FROM tbl_antrian_teller ORDER BY cabang_id ASC");
                        while ($cabang_row = $cabang_query->fetch_assoc()) {
                            $selected = ($filter_cabang == $cabang_row['cabang_id']) ? "selected" : "";
                            echo "<option value='{$cabang_row['cabang_id']}' {$selected}>{$cabang_row['cabang_id']}</option>";
                        }
                        ?>
                    </select>
                </div>
            <?php endif; ?>

            <div class="<?= $colTglAwal ?>">
                <label for="tanggal_awal" class="form-label">Tanggal Awal</label>
                <input type="date" id="tanggal_awal" name="tanggal_awal" class="form-control" value="<?= $tanggal_awal ?>">
            </div>
            <div class="<?= $colTglAkhir ?>">
                <label for="tanggal_akhir" class="form-label">Tanggal Akhir</label>
                <input type="date" id="tanggal_akhir" name="tanggal_akhir" class="form-control" value="<?= $tanggal_akhir ?>">
            </div>
            <div class="<?= $colBulan ?>">
                <label for="bulan" class="form-label">Bulan</label>
                <select id="bulan" name="bulan" class="form-select">
                    <option value="">Semua Bulan</option>
                    <?php
                    for ($i = 1; $i <= 12; $i++) {
                        $selected = ($bulan == $i) ? "selected" : "";
                        echo "<option value='{$i}' {$selected}>" . date('F', mktime(0, 0, 0, $i, 1)) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="<?= $colTahun ?>">
                <label for="tahun" class="form-label">Tahun</label>
                <select id="tahun" name="tahun" class="form-select">
                    <option value="">Semua Tahun</option>
                    <?php
                    $current_year = date('Y');
                    for ($i = $current_year; $i >= 2000; $i--) {
                        $selected = ($tahun == $i) ? "selected" : "";
                        echo "<option value='{$i}' {$selected}>{$i}</option>";
                    }
                    ?>
                </select>
            </div>

            <?php if ($cabang_id == 312): // Filter bagian hanya untuk cabang 312 
            ?>
                <div class="<?= $colBagian ?>">
                    <label for="bagian" class="form-label">Bagian</label>
                    <select id="bagian" name="bagian" class="form-select">
                        <option value="">Semua Bagian</option>
                        <option value="1" <?= $filter_bagian == '1' ? 'selected' : '' ?>>1</option>
                        <option value="2" <?= $filter_bagian == '2' ? 'selected' : '' ?>>2</option>
                    </select>
                </div>
            <?php endif; ?>

            <div class="<?= $colActions ?> d-flex align-items-end justify-content-end">
                <div class="btn-group w-100">
                    <button type="submit" class="btn btn-theme">Filter</button>
                    <button type="button" class="btn btn-theme dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="visually-hidden">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="export-pdf-teller.php?<?= http_build_query($_GET) ?>">Download PDF</a></li>
                        <li><a class="dropdown-item" href="export-excel-teller.php?<?= http_build_query($_GET) ?>">Download Excel</a></li>
                        <li><hr class="dropdown-divider" style="background-color: #dee2e6;"></li>
                        <li><a class="dropdown-item" href="print-teller.php?<?= http_build_query($_GET) ?>" target="_blank">Print</a></li>
                    </ul>
                </div>
            </div>
        </form>
        <!-- Actions moved into split dropdown next to Filter -->

        <!-- Tabel Laporan -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped mb-0">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Cabang ID</th>
                    <th>Tanggal</th>
                    <th>No Antrian</th>
                    <?php if ($cabang_id == 312): ?>
                        <th>Bagian</th>
                    <?php endif; ?>
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
                        echo "<td>" . date('d/m/Y', strtotime($row['tanggal_teller'])) . "</td>";
                        echo "<td>{$row['no_antrian_teller']}</td>";

                        if ($cabang_id == 312) {
                            echo "<td>" . ($row['bagian'] ?: '-') . "</td>";
                        }

                        // Waktu Mulai
                        echo "<td>" . (!empty($row['waktu_mulai']) ? date('H:i:s', strtotime($row['waktu_mulai'])) : '-') . "</td>";
                        // Waktu Selesai
                        echo "<td>" . (!empty($row['waktu_selesai']) ? date('H:i:s', strtotime($row['waktu_selesai'])) : '-') . "</td>";

                        echo "<td>" . ($row['status_teller'] == '2' ? 'Selesai' : 'Menunggu') . "</td>";

                        // Hitung durasi dari waktu_mulai dan waktu_selesai jika tersedia
                        if (!empty($row['waktu_mulai']) && !empty($row['waktu_selesai'])) {
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
                    // Jumlah kolom tergantung cabang_id
                    $colspan = ($cabang_id == 312) ? 9 : 8;
                    echo "<tr><td colspan='{$colspan}' class='text-center'>Tidak ada data tersedia</td></tr>";
                }
                ?>
            </tbody>
        </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>