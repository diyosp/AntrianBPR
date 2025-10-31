<?php
session_start(); // Memulai sesi

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php"); // Redirect ke halaman login jika belum login
  exit;
}

include "header.php";
?>

<body class="d-flex flex-column h-100">
  <main class="flex-shrink-0">
    <div class="container pt-5">
      <!-- tampilkan pesan selamat datang -->
      <div class="alert alert-light d-flex align-items-center mb-5" role="alert">
        <i class="bi-info-circle text-success me-3 fs-3"></i>
        <div class="d-flex justify-content-between align-items-center w-100">
          <span>
            Selamat Datang di <strong>BPR Sukabumi</strong>. Silahkan pilih menu berikut.
          </span>
          <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
      </div>

  <div class="row gx-5">
        <!-- link halaman Panggilan Antrian Admin Kredit (all roles) -->
        <div class="col-lg-6 mb-4">
          <div class="card border-0 shadow-sm">
            <div class="card-body p-5">
              <div class="feature-icon-1 bg-success bg-gradient mb-4">
                <i class="bi-credit-card"></i>
              </div>
              <h3>Panggilan Antrian Admin Kredit</h3>
              <p class="mb-4">Kelola dan pantau antrian admin kredit.</p>
              <a href="panggilan-antrian-kredit/index.php" class="btn btn-success rounded-pill px-4 py-2">
                Masuk <i class="bi-chevron-right ms-2"></i>
              </a>
            </div>
          </div>
        </div>
        <!-- link halaman Antrian Nasabah -->
        <div class="col-lg-6 mb-4">
          <div class="card border-0 shadow-sm">
            <div class="card-body p-5">
              <div class="feature-icon-1 bg-success bg-gradient mb-4">
                <i class="bi-people"></i>
              </div>
              <h3>Antrian Nasabah</h3>
              <p class="mb-4">Mengelola nomor antrian untuk Customer Service dan Teller.</p>
              <a href="nomor-gabungan/index.php" class="btn btn-success rounded-pill px-4 py-2">
                Masuk <i class="bi-chevron-right ms-2"></i>
              </a>
            </div>
          </div>
        </div>

        <!-- link halaman Panggilan (role_id 1, 4, 5) -->
        <?php if (in_array($_SESSION['role_id'], [1, 2, 3, 4, 5])): ?>
          <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
              <div class="card-body p-5">
                <div class="feature-icon-1 bg-success bg-gradient mb-4">
                  <i class="bi-person-badge"></i>
                </div>
                <h3>Panggilan</h3>
                <p class="mb-4">Kelola panggilan antrian nasabah.</p>
                <a href="/admin" class="btn btn-success rounded-pill px-4 py-2">
                  Masuk <i class="bi-chevron-right ms-2"></i>
                </a>
              </div>
            </div>
          </div>
        <?php endif; ?>

        <!-- link halaman Laporan (role_id 1, 2, 3) -->
        <?php if (in_array($_SESSION['role_id'], [1, 2, 3])): ?>
          <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
              <div class="card-body p-5">
                <div class="feature-icon-1 bg-success bg-gradient mb-4">
                  <i class="bi-file-text"></i>
                </div>
                <h3>Laporan</h3>
                <p class="mb-4">Lihat laporan aktivitas dan data nasabah.</p>
                <!-- Tombol untuk Customer Service -->
                <a href="laporan/laporan-cs.php" class="btn btn-success rounded-pill px-4 py-2 me-2">
                  Customer Service <i class="bi-chevron-right ms-2"></i>
                </a>
                <!-- Tombol untuk Teller -->
                <a href="laporan/laporan-teller.php" class="btn btn-success rounded-pill px-4 py-2 me-2">
                  Teller <i class="bi-chevron-right ms-2"></i>
                </a>
                <!-- Tombol untuk Admin Kredit -->
                <a href="laporan/laporan-kredit.php" class="btn btn-success rounded-pill px-4 py-2">
                  Admin Kredit <i class="bi-chevron-right ms-2"></i>
                </a>
              </div>
            </div>
          </div>
        <?php endif; ?>


        <!-- link halaman Tambah User (role_id 1) -->
        <?php if ($_SESSION['role_id'] == 1): ?>
          <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
              <div class="card-body p-5">
                <div class="feature-icon-1 bg-success bg-gradient mb-4">
                  <i class="bi-person-plus"></i>
                </div>
                <h3>Tambah User</h3>
                <p class="mb-4">Menambah pengguna baru untuk aplikasi.</p>
                <a href="tambah-user/index.php" class="btn btn-success rounded-pill px-4 py-2">
                  Masuk <i class="bi-chevron-right ms-2"></i>
                </a>
              </div>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </main>

  <!-- Footer -->
  <?php include "footer.php"; ?>
</body>

</html>