<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role_id'], [1,2,3])) {
  header("Location: ../login.php");
  exit;
}
include "../header.php";
require_once "../config/database.php";
?>


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
  <script>
    // Set browser tab title for this page without modifying shared header
    document.title = 'Laporan Admin Kredit';
  </script>
  <main class="flex-shrink-0">
    <div class="container pt-5 report-container">
      <div class="page-header d-flex align-items-center mb-4">
        <i class="bi-file-earmark-text me-3 fs-3"></i>
        <h1 class="h5 pt-2 mb-0">Laporan Admin Kredit</h1>
        <div class="text-end ms-auto">
                <a href="../" class="btn btn-outline-light btn-sm">Kembali</a>
        </div>
      </div>
      <!-- Form Filter -->
      <form method="GET" class="row mb-4">
        <?php
          // ambil role & cabang dari session untuk kontrol filter cabang
          $role_id = $_SESSION['role_id'];
          $cabang_id = $_SESSION['cabang_id'];
          $filter_cabang = isset($_GET['cabang_id']) ? $_GET['cabang_id'] : ($role_id != 1 ? $cabang_id : null);
        ?>
        <?php if ($role_id == 1): ?>
        <div class="col-md-2">
          <label for="cabang_id" class="form-label">Cabang</label>
          <select id="cabang_id" name="cabang_id" class="form-select">
            <option value="">Semua Cabang</option>
            <?php
              $cabang_query = $mysqli->query("SELECT DISTINCT cabang_id FROM tbl_antrian_kredit ORDER BY cabang_id ASC");
              while ($cabang_row = $cabang_query->fetch_assoc()) {
                $sel = ($filter_cabang !== null && (string)$filter_cabang === (string)$cabang_row['cabang_id']) ? 'selected' : '';
                echo "<option value='".htmlspecialchars($cabang_row['cabang_id'])."' {$sel}>".htmlspecialchars($cabang_row['cabang_id'])."</option>";
              }
            ?>
          </select>
        </div>
        <?php endif; ?>
        <div class="col-md-2">
          <label for="tanggal_awal" class="form-label">Tanggal Awal</label>
          <input type="date" id="tanggal_awal" name="tanggal_awal" class="form-control" value="<?= isset($_GET['tanggal_awal']) ? $_GET['tanggal_awal'] : '' ?>">
        </div>
        <div class="col-md-2">
          <label for="tanggal_akhir" class="form-label">Tanggal Akhir</label>
          <input type="date" id="tanggal_akhir" name="tanggal_akhir" class="form-control" value="<?= isset($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : '' ?>">
        </div>
        <div class="col-md-2">
          <label for="bulan" class="form-label">Bulan</label>
          <select id="bulan" name="bulan" class="form-select">
            <option value="">Semua Bulan</option>
            <?php for ($i = 1; $i <= 12; $i++) {
              $selected = (isset($_GET['bulan']) && $_GET['bulan'] == $i) ? "selected" : "";
              echo "<option value='{$i}' {$selected}>" . date('F', mktime(0, 0, 0, $i, 1)) . "</option>";
            } ?>
          </select>
        </div>
        <div class="col-md-2">
          <label for="tahun" class="form-label">Tahun</label>
          <select id="tahun" name="tahun" class="form-select">
            <option value="">Semua Tahun</option>
            <?php $current_year = date('Y');
            for ($i = $current_year; $i >= 2000; $i--) {
              $selected = (isset($_GET['tahun']) && $_GET['tahun'] == $i) ? "selected" : "";
              echo "<option value='{$i}' {$selected}>{$i}</option>";
            } ?>
          </select>
        </div>
        <div class="col-md-2 d-flex align-items-end justify-content-end">
          <div class="btn-group w-100">
            <button type="submit" class="btn btn-theme">Filter</button>
            <button type="button" class="btn btn-theme dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
              <span class="visually-hidden">Toggle Dropdown</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="export-pdf-kredit.php?<?= http_build_query($_GET) ?>">Download PDF</a></li>
              <li><a class="dropdown-item" href="export-excel-kredit.php?<?= http_build_query($_GET) ?>">Download Excel</a></li>
              <li><hr class="dropdown-divider" style="background-color: #dee2e6;"></li>
              <li><a class="dropdown-item" href="print-kredit.php?<?= http_build_query($_GET) ?>" target="_blank">Print</a></li>
            </ul>
          </div>
        </div>
      </form>
      <!-- Actions moved into split dropdown next to Filter -->
      <!-- Tabel Laporan (styled like CS) -->
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
            <th>Waktu Mulai</th>
            <th>Waktu Selesai</th>
            <th>Status</th>
            <th>Durasi</th>
          </tr>
        </thead>
        <tbody>
          <?php
          // Query filter sama seperti export/print
          $tanggal_awal = isset($_GET['tanggal_awal']) ? $_GET['tanggal_awal'] : null;
          $tanggal_akhir = isset($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : null;
          $bulan = isset($_GET['bulan']) ? $_GET['bulan'] : null;
          $tahun = isset($_GET['tahun']) ? $_GET['tahun'] : null;
          $query = "SELECT * FROM tbl_antrian_kredit WHERE waktu_mulai IS NOT NULL AND waktu_selesai IS NOT NULL";
          // filter cabang (seperti CS): jika superadmin bisa pilih; jika bukan, default cabang session
          if (!empty($filter_cabang)) {
            $query .= " AND cabang_id = '" . $mysqli->real_escape_string($filter_cabang) . "'";
          }
          if (!empty($tanggal_awal) && !empty($tanggal_akhir)) {
            $query .= " AND tanggal_kredit BETWEEN '" . $mysqli->real_escape_string($tanggal_awal) . "' AND '" . $mysqli->real_escape_string($tanggal_akhir) . "'";
          }
          if (!empty($bulan)) {
            $query .= " AND MONTH(tanggal_kredit) = '" . $mysqli->real_escape_string($bulan) . "'";
          }
          if (!empty($tahun)) {
            $query .= " AND YEAR(tanggal_kredit) = '" . $mysqli->real_escape_string($tahun) . "'";
          }
          $query .= " ORDER BY tanggal_kredit ASC, no_antrian_kredit ASC";
          $result = $mysqli->query($query);
          $nomor = 1;
          if ($result && $result->num_rows > 0):
            while ($row = $result->fetch_assoc()):
          ?>
          <tr>
            <td><?= $nomor++ ?></td>
            <td><?= htmlspecialchars($row['cabang_id']) ?></td>
            <td><?= !empty($row['tanggal_kredit']) ? date('d/m/y', strtotime($row['tanggal_kredit'])) : '-' ?></td>
            <td><?= htmlspecialchars($row['no_antrian_kredit']) ?></td>
            <td><?= !empty($row['waktu_mulai']) ? date('H:i:s', strtotime($row['waktu_mulai'])) : '-' ?></td>
            <td><?= !empty($row['waktu_selesai']) ? date('H:i:s', strtotime($row['waktu_selesai'])) : '-' ?></td>
            <td><?= $row['status_kredit'] == '2' ? 'Selesai' : 'Menunggu' ?></td>
            <td>
              <?php
                $durasi = (int)$row['durasi'];
                $hh = floor($durasi / 3600);
                $mm = floor(($durasi % 3600) / 60);
                $ss = $durasi % 60;
                printf('%02d:%02d:%02d', $hh, $mm, $ss);
              ?>
            </td>
          </tr>
          <?php endwhile; else: ?>
          <tr><td colspan="8" class="text-center">Tidak ada data tersedia</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
          </div>
        </div>
      </div>
    </div>
          </main>
</body>
</html>
