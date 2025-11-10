<?php
session_start(); // Memulai sesi

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php"); // Redirect ke halaman login jika belum login
  exit;
}

include "header.php";
?>

<body class="d-flex flex-column h-100" style="background-color: #081941;">
  <main class="flex-shrink-0">
    <div class="container pt-4">
      <!-- tampilkan pesan selamat datang -->
      <div class="alert alert-light d-flex align-items-center mb-4" role="alert" style="background-color: #11224E; border-color: #11224E; color: #fff;">
        <i class="bi-info-circle text-success me-3 fs-3"></i>
        <div class="d-flex justify-content-between align-items-center w-100">
          <span style="background-color: #11224E;" class="text-white">
            Selamat Datang di <img src="assets/img/testfs.png" alt="BPR Sukabumi" style="width: 75px; height: 75px; object-fit: contain; vertical-align: middle;"> Silahkan pilih menu berikut.
          </span>
          <a href="logout.php" class="btn" style="background-color: #F87B1B; border-color: #F87B1B; color: #fff;">Logout</a>
        </div>
      </div>
      <div class="row g-3 justify-content-start">
           <!-- Tampilan Antrian TV -->
          <div class="col-12 col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="background-color: #11224E;">
              <div class="card-body p-4 d-flex flex-column align-items-center">
                <i class="bi-tv mb-3" style="color: #fff; font-size: 2rem;"></i>
                <h5 class="mb-2" style="color: #fff;">Tampilan Antrian TV</h5>
                <p class="mb-3 text-center small" style="color: #fff;">Layar TV untuk menampilkan nomor antrian secara real-time.</p>
                <a href="tampilan-tv/index.php" class="btn btn-success rounded-pill px-4 py-2 mt-auto" target="_blank">
                  Buka TV <i class="bi-chevron-right ms-2"></i>
                </a>
              </div>
            </div>
          </div>
        
        <!-- Antrian Nasabah -->
        <div class="col-12 col-md-6 col-lg-3">
          <div class="card border-0 shadow-sm h-100" style="background-color: #11224E;">
            <div class="card-body p-4 d-flex flex-column align-items-center">
              <i class="bi-people mb-3" style="color: #fff; font-size: 2rem;"></i>
              <h5 class="mb-2" style="color: #fff;">Antrian Nasabah</h5>
              <p class="mb-3 text-center small" style="color: #fff;">Nomor antrian untuk Customer Service dan Teller.</p>
              <a href="nomor-gabungan/index.php" class="btn btn-success rounded-pill px-4 py-2 mt-auto">
                Masuk <i class="bi-chevron-right ms-2"></i>
              </a>
            </div>
          </div>
        </div>
        <!-- Panggilan (role_id 1,2,3,4,5) -->
        <?php if (in_array($_SESSION['role_id'], [1, 2, 3, 4, 5])): ?>
        <div class="col-12 col-md-6 col-lg-3">
          <div class="card border-0 shadow-sm h-100" style="background-color: #11224E;">
            <div class="card-body p-4 d-flex flex-column align-items-center">
              <i class="bi-person-badge mb-3" style="color: #fff; font-size: 2rem;"></i>
              <h5 class="mb-2" style="color: #fff;">Panggilan</h5>
              <p class="mb-3 text-center small" style="color: #fff;">Kelola panggilan antrian nasabah.</p>
              <a href="/admin" class="btn btn-success rounded-pill px-4 py-2 mt-auto">
                Masuk <i class="bi-chevron-right ms-2"></i>
              </a>
            </div>
          </div>
        </div>
        <?php endif; ?>
        <!-- Laporan (role_id 1,2,3) -->
        <?php if (in_array($_SESSION['role_id'], [1, 2, 3])): ?>
          <!-- Laporan CS -->
          <div class="col-12 col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="background-color: #11224E;">
              <div class="card-body p-4 d-flex flex-column align-items-center">
               <i class="bi bi-headset mb-3" style="color: #fff; font-size: 2rem;"></i>
                <h5 class="mb-2" style="color: #fff;">Laporan CS</h5>
                <p class="mb-3 text-center small" style="color: #fff;">Lihat laporan aktivitas Customer Service.</p>
                <a href="laporan/laporan-cs.php" class="btn btn-success rounded-pill px-3 py-2 mt-auto">
                  Buka <i class="bi-chevron-right ms-2"></i>
                </a>
              </div>
            </div>
          </div>

          <!-- Laporan Teller -->
          <div class="col-12 col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="background-color: #11224E;">
              <div class="card-body p-4 d-flex flex-column align-items-center">
                <i class="bi bi-person-lines-fill" style="color: #fff; font-size: 2rem;"></i>
                <h5 class="mb-2" style="color: #fff; margin-top: 12px;">Laporan Teller</h5>
                <p class="mb-3 text-center small" style="color: #fff;">Lihat laporan aktivitas Teller.</p>
                <a href="laporan/laporan-teller.php" class="btn btn-success rounded-pill px-3 py-2 mt-auto">
                  Buka <i class="bi-chevron-right ms-2"></i>
                </a>
              </div>
            </div>
          </div>

          <!-- Laporan Kredit -->
          <div class="col-12 col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="background-color: #11224E;">
              <div class="card-body p-4 d-flex flex-column align-items-center">
                <i class="bi bi-credit-card mb-3" style="color: #fff; font-size: 2rem;"></i>
                <h5 class="mb-2" style="color: #fff;">Laporan Kredit</h5>
                <p class="mb-3 text-center small" style="color: #fff;">Lihat laporan aktivitas bagian kredit.</p>
                <a href="laporan/laporan-kredit.php" class="btn btn-success rounded-pill px-3 py-2 mt-auto">
                  Buka <i class="bi-chevron-right ms-2"></i>
                </a>
              </div>
            </div>
          </div>
        <?php endif; ?>
        <!-- Tambah User (role_id 1) -->
        <?php if ($_SESSION['role_id'] == 1): ?>
          <div class="col-12 col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="background-color: #11224E;">
              <div class="card-body p-4 d-flex flex-column align-items-center">
                <i class="bi-person-plus mb-3" style="color: #fff; font-size: 2rem;"></i>
                <h5 class="mb-2" style="color: #fff;">Tambah User</h5>
                <p class="mb-3 text-center small" style="color: #fff;">Tambah pengguna baru aplikasi.</p>
                <a href="tambah-user/index.php" class="btn btn-success rounded-pill px-3 py-2 mt-auto">
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