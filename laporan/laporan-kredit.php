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
  <main class="flex-shrink-0">
    <div class="container pt-5">
      <div class="d-flex align-items-center mb-4">
        <i class="bi-file-earmark-text text-success me-3 fs-3"></i>
        <h1 class="h5 pt-2">Laporan Antrian Admin Kredit</h1>
      </div>
      <!-- Form Filter -->
      <form method="GET" class="row mb-4">
        <div class="col-md-3">
          <label for="tanggal_awal" class="form-label">Tanggal Awal</label>
          <input type="date" id="tanggal_awal" name="tanggal_awal" class="form-control" value="<?= isset($_GET['tanggal_awal']) ? $_GET['tanggal_awal'] : '' ?>">
        </div>
        <div class="col-md-3">
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
        <div class="col-md-2 d-flex align-items-end">
          <button type="submit" class="btn btn-primary">Filter</button>
        </div>
      </form>
      <!-- Tombol Export PDF dan Print -->
      <div class="mb-4">
        <a href="export-pdf-kredit.php?<?= http_build_query($_GET) ?>" class="btn btn-danger">Download PDF</a>
        <a href="export-excel-kredit.php?<?= http_build_query($_GET) ?>" class="btn btn-warning">Download Excel</a>
        <a href="print-kredit.php?<?= http_build_query($_GET) ?>" target="_blank" class="btn btn-success">Print</a>
      </div>
      <!-- Tabel Laporan -->
      <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
          <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" id="tabel-laporan-kredit">
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
                while ($row = $result->fetch_assoc()):
                ?>
                <tr>
                  <td><?= $nomor++ ?></td>
                  <td><?= htmlspecialchars($row['cabang_id']) ?></td>
                  <td><?= htmlspecialchars($row['tanggal_kredit']) ?></td>
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
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </main>
  <?php include "../footer.php"; ?>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" />
  <script>
    $(document).ready(function() {
      $('#tabel-laporan-kredit').DataTable({
        "order": [[0, "desc"], [1, "asc"]]
      });
    });
  </script>
</body>
</html>
