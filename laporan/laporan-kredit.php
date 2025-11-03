<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role_id'], [1,2,3])) {
  header("Location: ../login.php");
  exit;
}
include "../header.php";
require_once "../config/database.php";
?>


<body class="d-flex flex-column h-100">
  <script>
    // Set browser tab title for this page without modifying shared header
    document.title = 'Laporan Admin Kredit';
  </script>
  <main class="flex-shrink-0">
    <div class="container pt-5">
      <div class="d-flex align-items-center mb-4">
        <i class="bi-file-earmark-text text-success me-3 fs-3"></i>
        <h1 class="h5 pt-2">Laporan Admin Kredit</h1>
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
            <button type="submit" class="btn btn-primary">Filter</button>
            <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
              <span class="visually-hidden">Toggle Dropdown</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="export-pdf-kredit.php?<?= http_build_query($_GET) ?>">Download PDF</a></li>
              <li><a class="dropdown-item" href="export-excel-kredit.php?<?= http_build_query($_GET) ?>">Download Excel</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="print-kredit.php?<?= http_build_query($_GET) ?>" target="_blank">Print</a></li>
            </ul>
          </div>
        </div>
      </form>
      <!-- Actions moved into split dropdown next to Filter -->
      <!-- Tabel Laporan (styled like CS) -->
      <table class="table table-bordered table-striped">
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
          </main>
</body>
</html>
