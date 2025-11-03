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
        <div class="container pt-5">
            <div class="row justify-content-lg-center">
                <div class="col-lg-8 mb-4">
                    <div class="px-4 py-3 mb-4 bg-white rounded-2 shadow-sm">
                        <!-- Judul Halaman -->
                        <div class="d-flex align-items-center me-md-auto">
                            <i class="bi-people-fill text-success me-3 fs-3"></i>
                            <h1 class="h5 pt-2">Nomor Antrian</h1>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center d-grid p-5">
                            <div class="row">
                                <!-- Antrian Customer Service -->
                                <div class="col-12 col-md-4 mb-4">
                                    <div class="border border-success rounded-2 py-2 mb-3">
                                        <h3 class="pt-3">ANTRIAN CS</h3>
                                        <h1 id="antrian_cs" class="display-4 fw-bold text-success text-center lh-1 pb-2"></h1>
                                    </div>
                                    <a id="insert_cs" href="javascript:void(0)" class="btn btn-success btn-block rounded-pill fs-6 px-4 py-3">
                                        <i class="bi-person-plus fs-4 me-2"></i> Ambil Nomor CS
                                    </a>
                                </div>

                                <!-- Antrian Teller -->
                                <div class="col-12 col-md-4 mb-4">
                                    <div class="border border-success rounded-2 py-2 mb-3">
                                        <h3 class="pt-3">ANTRIAN TELLER</h3>
                                        <h1 id="antrian_teller" class="display-4 fw-bold text-success text-center lh-1 pb-2"></h1>
                                    </div>
                                    <a id="insert_teller" href="javascript:void(0)" class="btn btn-success btn-block rounded-pill fs-6 px-4 py-3">
                                        <i class="bi-person-plus fs-4 me-2"></i> Ambil Nomor Teller
                                    </a>
                                </div>

                                <!-- Antrian Admin Kredit -->
                                <div class="col-12 col-md-4 mb-4">
                                    <div class="border border-success rounded-2 py-2 mb-3">
                                        <h3 class="pt-3">ANTRIAN KREDIT</h3>
                                        <h1 id="antrian_admin_kredit" class="display-4 fw-bold text-success text-center lh-1 pb-2"></h1>
                                    </div>
                                    <a id="insert_admin_kredit" href="javascript:void(0)" class="btn btn-success btn-block rounded-pill fs-6 px-4 py-3">
                                        <i class="bi-person-plus fs-4 me-2"></i> Ambil Nomor Kredit
                                    </a>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include "../footer.php"; ?>

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