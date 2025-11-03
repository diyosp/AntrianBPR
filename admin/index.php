<?php
include "../header.php";

// Mulai sesi untuk mendapatkan data pengguna yang login
session_start();

// Ambil cabang_id dari session
$cabang_id = $_SESSION['cabang_id'] ?? null; // Gunakan null jika session tidak tersedia
?>

<body class="d-flex flex-column h-100">
  <main class="flex-shrink-0">
    <div class="container pt-5">
      <!-- tampilkan pesan selamat datang -->
      <div class="alert alert-light d-flex align-items-center mb-5" role="alert">
        <i class="bi-info-circle text-success me-3 fs-3"></i>
        <div>
          Selamat Datang di Aplikasi Nomor Antrian <strong>BPR Sukabumi</strong>.
        </div>
      </div>

      <div class="row gx-5">
        <!-- link halaman nomor antrian -->
        <div class="col-lg-6 mb-4">
          <div class="card border-0 shadow-sm">
            <div class="card-body p-5">
              <div class="feature-icon-1 bg-success bg-gradient mb-4">
                <i class="bi-people"></i>
              </div>
              <h3>Customer Service</h3>
              <p class="mb-4">Panggil Nasabah Loket Customer Service</p>
              <a href="../panggilan-antrian" class="btn btn-success rounded-pill px-4 py-2">
                Tampilkan <i class="bi-chevron-right ms-2"></i>
              </a>
            </div>
          </div>
        </div>

        <!-- link halaman panggilan antrian teller -->
        <div class="col-lg-6 mb-4">
          <div class="card border-0 shadow-sm">
            <div class="card-body p-5">
              <div class="feature-icon-1 bg-success bg-gradient mb-4">
                <i class="bi-mic"></i>
              </div>
              <h3>Teller</h3>
              <p class="mb-4">Panggil Nasabah Loket Teller</p>
              <?php if ($cabang_id == 312): ?>
                <!-- Khusus cabang_id 312, tampilkan Teller A dan Teller B -->
                <a href="../panggilan-antrian-teller-a" class="btn btn-success rounded-pill px-4 py-2 me-2">
                  Teller 1
                </a>
                <a href="../panggilan-antrian-teller-b" class="btn btn-success rounded-pill px-4 py-2">
                  Teller 2
                </a>
              <?php else: ?>
                <!-- Selain cabang_id 312, tampilkan tombol Tampilkan -->
                <a href="../panggilan-antrian-teller" class="btn btn-success rounded-pill px-4 py-2">
                  Tampilkan <i class="bi-chevron-right ms-2"></i>
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="row gx-5">
        <!-- link halaman nomor antrian -->
        <div class="col-lg-6 mb-4">
          <div class="card border-0 shadow-sm">
            <div class="card-body p-5">
              <div class="feature-icon-1 bg-success bg-gradient mb-4">
                <i class="bi-credit-card"></i>
              </div>
              <h3>Admin Kredit</h3>
              <p class="mb-4">Panggil Nasabah Loket Admin Kredit</p>
              <a href="../panggilan-antrian-kredit" class="btn btn-success rounded-pill px-4 py-2">
                Tampilkan <i class="bi-chevron-right ms-2"></i>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Footer -->
  <?php
  include "../footer.php";
  ?>

  <!-- Popper and Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.min.js" integrity="sha384-Atwg2Pkwv9vp0ygtn1JAojH0nYbwNJLPhwyoVbhoPwBhjQPR5VtM2+xf0Uwh9KtT" crossorigin="anonymous"></script>
</body>

</html>