<?php
// Mulai sesi DULU sebelum output apapun
session_start();

// Ensure database connection is available
if (file_exists(__DIR__ . '/../config/database.php')) {
  include_once __DIR__ . '/../config/database.php';
}

// Ambil cabang_id dari session
$cabang_id = $_SESSION['cabang_id'] ?? null; // Gunakan null jika session tidak tersedia

include "../header.php";

// Default nama cabang
$nama_cabang = "BPR Sukabumi";

// Jika cabang_id tersedia and $mysqli (DB) exists, ambil nama cabang
if (!empty($cabang_id) && isset($mysqli)) {
  $query = "SELECT nama FROM cabang WHERE id = ?";
  if ($stmt = $mysqli->prepare($query)) {
    $stmt->bind_param("i", $cabang_id);
    $stmt->execute();
    $stmt->bind_result($fetched_name);
    if ($stmt->fetch() && !empty($fetched_name)) {
      $nama_cabang = $fetched_name;
    }
    $stmt->close();
  }
}
?>

<body class="d-flex flex-column h-100" style="background-color: #081941;">
  <main class="flex-shrink-0">
    <div class="container pt-4">
  <div class="row g-3 justify-content-start">
        <div class="col-12">
          <div class="alert alert-light d-flex align-items-center mb-6" role="alert" style="background-color: #11224E; border-color: #11224E; color: #fff; overflow: hidden; border-radius: 0.5rem !important;">
            <i class="bi-info-circle me-3 fs-3" style="color: #fff;"></i>
            <div class="d-flex justify-content-between align-items-center w-100">
              <div class="text-white fw-semibold">Panel Panggilan - <?= htmlspecialchars($nama_cabang) ?></div>
              <div class="text-end">
                <a href="../" class="btn btn-outline-light btn-sm">Kembali</a>
              </div>
            </div>
          </div>
        </div>

        <!-- card: Customer Service -->
        <div class="col-12 col-md-6 col-lg-3">
          <div class="card border-0 shadow-sm h-100 rounded-2" style="background-color: #11224E; overflow: hidden; border-radius: 1rem !important;">
            <div class="card-body p-4 d-flex flex-column align-items-center">
              <i class="bi-people mb-3" style="color: #fff; font-size: 2.2rem;"></i>
              <h5 class="mb-2" style="color: #fff;">Customer Service</h5>
              <p class="mb-3 text-center small" style="color: #fff;">Panggil Nasabah Loket Customer Service</p>
              <a href="../panggilan-antrian" class="btn rounded-pill px-4 py-2 mt-auto w-100" style="background-color: #F87B1B; color: #fff;">
                Tampilkan <i class="bi-chevron-right ms-2"></i>
              </a>
            </div>
          </div>
        </div>

        <!-- card: Teller -->
        <div class="col-12 col-md-6 col-lg-3">
          <div class="card border-0 shadow-sm h-100 rounded-2" style="background-color: #11224E; overflow: hidden; border-radius: 1rem !important;">
            <div class="card-body p-4 d-flex flex-column align-items-center">
              <i class="bi-mic mb-3" style="color: #fff; font-size: 2.2rem;"></i>
              <h5 class="mb-2" style="color: #fff;">Teller</h5>
              <p class="mb-3 text-center small" style="color: #fff;">Panggil Nasabah Loket Teller</p>
              <div class="d-flex gap-2 w-100">
                <?php if ($cabang_id == 312): ?>
                  <a href="../panggilan-antrian-teller-a" class="btn rounded-pill px-3 py-2 w-50" style="background-color: #F87B1B; color: #fff;">Teller 1<i class="bi-chevron-right ms-2"></i></a>
                  <a href="../panggilan-antrian-teller-b" class="btn rounded-pill px-3 py-2 w-50" style="background-color: #F87B1B; color: #fff;">Teller 2<i class="bi-chevron-right ms-2"></i></a>
                <?php else: ?>
                  <a href="../panggilan-antrian-teller" class="btn rounded-pill px-4 py-2 w-100" style="background-color: #F87B1B; color: #fff;">Tampilkan <i class="bi-chevron-right ms-2"></i></a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>

        <!-- card: Admin Kredit -->
        <div class="col-12 col-md-6 col-lg-3">
          <div class="card border-0 shadow-sm h-100 rounded-2" style="background-color: #11224E; overflow: hidden; border-radius: 1rem !important;">
            <div class="card-body p-4 d-flex flex-column align-items-center">
              <i class="bi-credit-card-2-back mb-3" style="color: #fff; font-size: 2.2rem;"></i>
              <h5 class="mb-2" style="color: #fff;">Admin Kredit</h5>
              <p class="mb-3 text-center small" style="color: #fff;">Panggil Nasabah Loket Admin Kredit</p>
              <a href="../panggilan-antrian-kredit" class="btn rounded-pill px-4 py-2 mt-auto w-100" style="background-color: #F87B1B; color: #fff;">Tampilkan <i class="bi-chevron-right ms-2"></i></a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>


  <!-- Popper and Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.min.js" integrity="sha384-Atwg2Pkwv9vp0ygtn1JAojH0nYbwNJLPhwyoVbhoPwBhjQPR5VtM2+xf0Uwh9KtT" crossorigin="anonymous"></script>
</body>

</html>