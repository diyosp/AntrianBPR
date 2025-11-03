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
    <div class="container pt-4">
      <!-- tampilkan pesan selamat datang -->
      <div class="alert alert-light d-flex align-items-center mb-4" role="alert">
        <i class="bi-info-circle text-success me-3 fs-3"></i>
        <div class="d-flex justify-content-between align-items-center w-100">
          <span>
            Selamat Datang di <strong>BPR Sukabumi</strong>. Silahkan pilih menu berikut.
          </span>
          <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
      </div>
      <div class="row g-3 justify-content-start">
           <!-- Tampilan Antrian TV -->
          <div class="col-12 col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-body p-4 d-flex flex-column align-items-center">
                <div class="feature-icon-1 bg-success bg-gradient mb-3">
                  <i class="bi-tv"></i>
                </div>
                <h5 class="mb-2">Tampilan Antrian TV</h5>
                <p class="mb-3 text-center small">Layar TV untuk menampilkan nomor antrian secara real-time.</p>
                <a href="tampilan-tv/index.php" class="btn btn-success rounded-pill px-4 py-2 mt-auto" target="_blank">
                  Buka TV <i class="bi-chevron-right ms-2"></i>
                </a>
              </div>
            </div>
          </div>
        
        <!-- Antrian Nasabah -->
        <div class="col-12 col-md-6 col-lg-3">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4 d-flex flex-column align-items-center">
              <div class="feature-icon-1 bg-success bg-gradient mb-3">
                <i class="bi-people"></i>
              </div>
              <h5 class="mb-2">Antrian Nasabah</h5>
              <p class="mb-3 text-center small">Nomor antrian untuk Customer Service dan Teller.</p>
              <a href="nomor-gabungan/index.php" class="btn btn-success rounded-pill px-4 py-2 mt-auto">
                Masuk <i class="bi-chevron-right ms-2"></i>
              </a>
            </div>
          </div>
        </div>
        <!-- Panggilan (role_id 1,2,3,4,5) -->
        <?php if (in_array($_SESSION['role_id'], [1, 2, 3, 4, 5])): ?>
        <div class="col-12 col-md-6 col-lg-3">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4 d-flex flex-column align-items-center">
              <div class="feature-icon-1 bg-success bg-gradient mb-3">
                <i class="bi-person-badge"></i>
              </div>
              <h5 class="mb-2">Panggilan</h5>
              <p class="mb-3 text-center small">Kelola panggilan antrian nasabah.</p>
              <a href="/admin" class="btn btn-success rounded-pill px-4 py-2 mt-auto">
                Masuk <i class="bi-chevron-right ms-2"></i>
              </a>
            </div>
          </div>
        </div>
        <?php endif; ?>
        <!-- Laporan (role_id 1,2,3) -->
        <?php if (in_array($_SESSION['role_id'], [1, 2, 3])): ?>
        <div class="col-12 col-md-6 col-lg-3">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4 d-flex flex-column align-items-center">
              <div class="feature-icon-1 bg-success bg-gradient mb-3">
                <i class="bi-file-text"></i>
              </div>
              <h5 class="mb-2">Laporan</h5>
              <p class="mb-3 text-center small">Lihat laporan aktivitas dan data nasabah.</p>
              <div class="d-flex flex-wrap gap-2 justify-content-center mt-auto">
                <a href="laporan/laporan-cs.php" class="btn btn-success rounded-pill px-3 py-2">
                  CS <i class="bi-chevron-right ms-2"></i>
                </a>
                <a href="laporan/laporan-teller.php" class="btn btn-success rounded-pill px-3 py-2">
                  Teller <i class="bi-chevron-right ms-2"></i>
                </a>
                <a href="laporan/laporan-kredit.php" class="btn btn-success rounded-pill px-3 py-2">
                  Kredit <i class="bi-chevron-right ms-2"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
        <?php endif; ?>
        <!-- Tambah User (role_id 1) -->
        <?php if ($_SESSION['role_id'] == 1): ?>
          <div class="col-12 col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-body p-4 d-flex flex-column align-items-center">
                <div class="feature-icon-1 bg-success bg-gradient mb-3">
                  <i class="bi-person-plus"></i>
                </div>
                <h5 class="mb-2">Tambah User</h5>
                <p class="mb-3 text-center small">Tambah pengguna baru aplikasi.</p>
                <a href="tambah-user/index.php" class="btn btn-success rounded-pill px-4 py-2 mt-auto">
                  Masuk <i class="bi-chevron-right ms-2"></i>
                </a>
              </div>
            </div>
          </div>
        <?php endif; ?>  
      </div>
    </div>
  </main>
</body>

</html>