<?php
// Mulai session dan koneksi ke database
session_start();
require_once "../config/database.php"; // File koneksi database Anda

// Pastikan user sudah login
if (!isset($_SESSION['cabang_id'])) {
    header("Location: ../login.php");
    exit;
}

// Ambil cabang_id dari session
$cabang_id = $_SESSION['cabang_id'];

// Query untuk mendapatkan nama cabang berdasarkan cabang_id
$query = "SELECT nama FROM cabang WHERE id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $cabang_id);
$stmt->execute();
$stmt->bind_result($nama_cabang);
$stmt->fetch();
$stmt->close();

// Jika nama cabang tidak ditemukan, gunakan nama default
if (empty($nama_cabang)) {
    $nama_cabang = "Cabang Tidak Diketahui";
}
?>

<?php include "../header.php"; ?>

<body class="d-flex flex-column h-100">
    <main class="flex-shrink-0">
        <div class="container pt-4">
            <div class="row g-3 justify-content-center">
                <div class="col-12">
                    <div class="alert alert-light d-flex align-items-center mb-5" role="alert" style="background-color: #11224E; overflow: hidden; border-radius: 0.50rem !important;">
                        <i class="bi-people me-3 fs-3" style="color: #fff;"></i>
                        <div class="d-flex justify-content-between align-items-center w-100">
                            <div class="text-white fw-semibold">Ambil Nomor - <?= htmlspecialchars($nama_cabang) ?></div>
                            <div class="text-end">
                                <a href="../" class="btn btn-outline-light btn-sm">Kembali</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cards grid similar to main index -->
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card border-0 shadow-sm h-100 rounded-2" style="background-color: #11224E; overflow: hidden; border-radius: 1rem !important;">
                        <div class="card-body p-4 d-flex flex-column align-items-center">
                            <i class="bi-person-plus mb-3" style="color: #fff; font-size: 2.2rem;"></i>
                            <h5 class="mb-2" style="color: #fff;">Antrian Customer Service</h5>
                            <p class="mb-3 text-center small" style="color: #fff;">Ambil nomor antrian untuk Customer Service.</p>
                            <div class="display-4 fw-bold text-center mb-3" id="antrian_cs" style="color: #fff;"></div>
                            <a id="insert_cs" href="javascript:void(0)" class="btn rounded-pill px-4 py-2 mt-auto w-100" style="background-color: #F87B1B; color: #fff;">
                                Ambil Nomor CS <i class="bi-chevron-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card border-0 shadow-sm h-100 rounded-2" style="background-color: #11224E; overflow: hidden; border-radius: 1rem !important;">
                        <div class="card-body p-4 d-flex flex-column align-items-center">
                            <i class="bi-cash-stack mb-3" style="color: #fff; font-size: 2.2rem;"></i>
                            <h5 class="mb-2" style="color: #fff;">Antrian Teller</h5>
                            <p class="mb-3 text-center small" style="color: #fff;">Ambil nomor antrian untuk Teller.</p>
                            <div class="display-4 fw-bold text-center mb-3" id="antrian_teller" style="color: #fff;"></div>
                            <a id="insert_teller" href="javascript:void(0)" class="btn rounded-pill px-4 py-2 mt-auto w-100" style="background-color: #F87B1B; color: #fff;">
                                Ambil Nomor Teller <i class="bi-chevron-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card border-0 shadow-sm h-100 rounded-2" style="background-color: #11224E; overflow: hidden; border-radius: 1rem !important;">
                        <div class="card-body p-4 d-flex flex-column align-items-center">
                            <i class="bi-credit-card-2-back mb-3" style="color: #fff; font-size: 2.2rem;"></i>
                            <h5 class="mb-2" style="color: #fff;">Antrian Admin Kredit</h5>
                            <p class="mb-3 text-center small" style="color: #fff;">Ambil nomor antrian untuk Admin Kredit.</p>
                            <div class="display-4 fw-bold text-center mb-3" id="antrian_admin_kredit" style="color: #fff;"></div>
                            <a id="insert_admin_kredit" href="javascript:void(0)" class="btn rounded-pill px-4 py-2 mt-auto w-100" style="background-color: #F87B1B; color: #fff;">
                                Ambil Nomor Kredit <i class="bi-chevron-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>
    <!-- jQuery Core -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
    <script src="print_blue.js"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            // Ambil nama cabang dari PHP
            const namaCabang = `<?= addslashes($nama_cabang) ?>`;

            // Load data antrian awal
            $('#antrian_cs').load('../nomor-antrian/get_antrian.php');
            $('#antrian_teller').load('../nomor-antrian-teller/get_antrian_teller.php');
            $('#antrian_admin_kredit').load('../panggilan-antrian-kredit/get_antrian_admin_kredit.php');

            // Klik untuk Customer Service
            $('#insert_cs').on('click', function() {
                $.ajax({
                    type: 'POST',
                    url: '../nomor-antrian/insert.php',
                    success: function(result) {
                        if (result === 'Sukses') {
                            $('#antrian_cs').load('../nomor-antrian/get_antrian.php', function() {
                                const nomorAntrian = $('#antrian_cs').text().trim();
                                const content = `
\x1B\x40
\x1B\x61\x01
\x1B\x45\x01PERUMDA BPR SUKABUMI\x1B\x45\x00
${namaCabang}\n
\x1B\x61\x01ANTRIAN Customer Service\n
\x1D\x21\x11NO ${nomorAntrian}\x1D\x21\x00\n
${new Date().toLocaleString('id-ID')}
\n-------------------\n
`;
                                connectToBluetoothPrinter(content);
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr.responseText);
                    },
                });
            });

            // Klik untuk Teller
            $('#insert_teller').on('click', function() {
                $.ajax({
                    type: 'POST',
                    url: '../nomor-antrian-teller/insert_teller.php',
                    success: function(result) {
                        if (result === 'Sukses') {
                            $('#antrian_teller').load('../nomor-antrian-teller/get_antrian_teller.php', function() {
                                const nomorAntrian = $('#antrian_teller').text().trim();
                                const content = `
\x1B\x40
\x1B\x61\x01
\x1B\x45\x01PERUMDA BPR SUKABUMI\x1B\x45\x00
${namaCabang}\n
\x1B\x61\x01ANTRIAN Teller\n
\x1D\x21\x11NO ${nomorAntrian}\x1D\x21\x00\n
${new Date().toLocaleString('id-ID')}
\n-------------------\n
`;
                                connectToBluetoothPrinter(content);
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr.responseText);
                    },
                });
            });

            // Klik untuk Admin Kredit
            $('#insert_admin_kredit').on('click', function() {
                $.ajax({
                    type: 'POST',
                    url: '../nomor-antrian-kredit/insert_admin_kredit.php',
                    success: function(result) {
                        console.log('AJAX result for kredit:', result);
                        if (result === 'Sukses') {
                            $('#antrian_admin_kredit').load('../nomor-antrian-kredit/get_antrian_kredit.php', function() {
                                const nomorAntrian = $('#antrian_admin_kredit').text().trim();
                                console.log('Printing kredit', nomorAntrian);
                                const content = `
\x1B\x40
\x1B\x61\x01
\x1B\x45\x01PERUMDA BPR SUKABUMI\x1B\x45\x00
${namaCabang}\n
\x1B\x61\x01ANTRIAN Admin Kredit\n
\x1D\x21\x11NO ${nomorAntrian}\x1D\x21\x00\n
${new Date().toLocaleString('id-ID')}
\n-------------------\n
`;
                                console.log('Calling connectToBluetoothPrinter for kredit');
                                connectToBluetoothPrinter(content);
                            });
                        } else {
                            console.warn('Kredit insert did not return Sukses:', result);
                        }
                    },
                    error: function(xhr) {
                        console.error('AJAX error for kredit:', xhr.responseText);
                    },
                });
            });
        });
    </script>
</body>

</html>