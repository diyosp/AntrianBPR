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
</head>

<body>
    <div class="container mt-4">
        <h2>Laporan Teller</h2>

        <!-- Form Filter -->
        <form method="GET" class="row mb-4">
            <?php if ($role_id == 1): // Filter cabang hanya untuk super_admin 
            ?>
                <div class="col-md-3">
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

            <div class="col-md-3">
                <label for="tanggal_awal" class="form-label">Tanggal Awal</label>
                <input type="date" id="tanggal_awal" name="tanggal_awal" class="form-control" value="<?= $tanggal_awal ?>">
            </div>
            <div class="col-md-3">
                <label for="tanggal_akhir" class="form-label">Tanggal Akhir</label>
                <input type="date" id="tanggal_akhir" name="tanggal_akhir" class="form-control" value="<?= $tanggal_akhir ?>">
            </div>
            <div class="col-md-2">
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
            <div class="col-md-2">
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
                <div class="col-md-2">
                    <label for="bagian" class="form-label">Bagian</label>
                    <select id="bagian" name="bagian" class="form-select">
                        <option value="">Semua Bagian</option>
                        <option value="1" <?= $filter_bagian == '1' ? 'selected' : '' ?>>1</option>
                        <option value="2" <?= $filter_bagian == '2' ? 'selected' : '' ?>>2</option>
                    </select>
                </div>
            <?php endif; ?>

            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </form>
        <!-- Tombol Export PDF dan Print -->
        <div class="mb-4">
            <a href="export-pdf-teller.php?<?= http_build_query($_GET) ?>" class="btn btn-danger">Download PDF</a>
            <a href="export-excel-teller.php?<?= http_build_query($_GET) ?>" class="btn btn-warning">Download Excel</a>
            <a href="print-teller.php?<?= http_build_query($_GET) ?>" target="_blank" class="btn btn-success">Print</a>
        </div>

        <!-- Tabel Laporan -->
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Cabang ID</th>
                    <th>Tanggal</th>
                    <th>No Antrian</th>
                    <?php if ($cabang_id == 312): ?>
                        <th>Bagian</th>
                    <?php endif; ?>
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
                    echo "<tr><td colspan='7' class='text-center'>Tidak ada data tersedia</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>