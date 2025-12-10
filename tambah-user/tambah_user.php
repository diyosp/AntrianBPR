<?php
session_start();
require_once "../config/database.php";

// Ambil data pegawai dari EIS database
$pegawai_list = [];
try {
    // Get actual column names first
    $columnsQuery = "SHOW COLUMNS FROM bprsukab_eis_update.pegawai";
    $columnsResult = $mysqli_eis->query($columnsQuery);
    $columns = [];
    while ($col = $columnsResult->fetch_assoc()) {
        $columns[] = $col['Field'];
    }
    
    // Determine the correct column name for pegawai name
    $nameColumn = 'id_pegawai'; // default
    if (in_array('nama_pegawai', $columns)) {
        $nameColumn = 'nama_pegawai';
    } elseif (in_array('nama', $columns)) {
        $nameColumn = 'nama';
    } elseif (in_array('name', $columns)) {
        $nameColumn = 'name';
    }
    
    $pegawaiQuery = "SELECT id_pegawai, $nameColumn as nama_pegawai, kode_cabang 
                     FROM bprsukab_eis_update.pegawai 
                     ORDER BY $nameColumn";
    $pegawaiResult = $mysqli_eis->query($pegawaiQuery);
    
    if ($pegawaiResult && $pegawaiResult->num_rows > 0) {
        while ($row = $pegawaiResult->fetch_assoc()) {
            $pegawai_list[] = $row;
        }
    }
} catch (Exception $e) {
    error_log("Error fetching pegawai: " . $e->getMessage());
}

// Ambil data role dari Antrix
$roles = [];
$roleQuery = "SELECT role_id, nama FROM role";
$roleResult = $mysqli->query($roleQuery);
if ($roleResult && $roleResult->num_rows > 0) {
    while ($row = $roleResult->fetch_assoc()) {
        $roles[] = $row;
    }
}

// Ambil data cabang dari Antrix
$cabangs = [];
$cabangQuery = "SELECT id, nama FROM cabang";
$cabangResult = $mysqli->query($cabangQuery);
if ($cabangResult && $cabangResult->num_rows > 0) {
    while ($row = $cabangResult->fetch_assoc()) {
        $cabangs[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pegawai = $_POST['id_pegawai'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $role_id = $_POST['role_id'] ?? '';
    $cabang_id = $_POST['cabang_id'] ?? '';
    $status = $_POST['status'] ?? 'active';

    // Validasi input
    if (empty($id_pegawai) || empty($username) || empty($password) || empty($role_id) || empty($cabang_id)) {
        http_response_code(400);
        echo json_encode(['error' => 'Semua field harus diisi']);
        exit;
    }

    // Cek apakah username sudah ada
    $query = "SELECT * FROM users WHERE username = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Username sudah digunakan']);
        exit;
    }

    // Cek apakah id_pegawai sudah terdaftar
    $checkPegawai = "SELECT * FROM users WHERE id_pegawai = ?";
    $stmtPegawai = $mysqli->prepare($checkPegawai);
    $stmtPegawai->bind_param("s", $id_pegawai);
    $stmtPegawai->execute();
    $resultPegawai = $stmtPegawai->get_result();

    if ($resultPegawai->num_rows > 0) {
        http_response_code(400);
        echo json_encode(['error' => 'ID Pegawai sudah terdaftar sebagai user']);
        exit;
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Tambahkan user baru dengan id_pegawai
    $insertQuery = "INSERT INTO users (id_pegawai, username, password, role_id, cabang_id, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $insertStmt = $mysqli->prepare($insertQuery);
    $insertStmt->bind_param("sssiss", $id_pegawai, $username, $hashedPassword, $role_id, $cabang_id, $status);

    if ($insertStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'User berhasil ditambahkan']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal menambahkan user: ' . $insertStmt->error]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah User - BPR Sukabumi</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .card-header {
            border-radius: 15px 15px 0 0 !important;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .select2-container--default .select2-selection--single {
            height: 38px;
            padding: 6px 12px;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 24px;
        }
        .required {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="card shadow-lg">
                    <div class="card-header text-white py-3">
                        <h4 class="mb-0">
                            <i class="fas fa-user-plus me-2"></i>Tambah User Baru
                        </h4>
                    </div>
                    <div class="card-body p-4">
                        <!-- Alert Container -->
                        <div id="alertContainer"></div>

                        <form id="formTambahUser" method="POST">
                            <div class="row">
                                <!-- Pilih Pegawai dari EIS -->
                                <div class="col-md-6 mb-3">
                                    <label for="id_pegawai" class="form-label">
                                        ID Pegawai <span class="required">*</span>
                                    </label>
                                    <select name="id_pegawai" id="id_pegawai" class="form-select select2" required>
                                        <option value="">-- Pilih Pegawai dari EIS --</option>
                                        <?php foreach ($pegawai_list as $pegawai): ?>
                                            <option value="<?php echo htmlspecialchars($pegawai['id_pegawai']); ?>" 
                                                    data-nama="<?php echo htmlspecialchars($pegawai['nama_pegawai']); ?>"
                                                    data-cabang="<?php echo htmlspecialchars($pegawai['kode_cabang']); ?>">
                                                <?php echo htmlspecialchars($pegawai['id_pegawai'] . ' - ' . $pegawai['nama_pegawai']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted">Data pegawai dari database EIS</small>
                                </div>

                                <!-- Username -->
                                <div class="col-md-6 mb-3">
                                    <label for="username" class="form-label">
                                        Username <span class="required">*</span>
                                    </label>
                                    <input type="text" name="username" id="username" class="form-control" 
                                           placeholder="Masukkan username" required>
                                    <small class="text-muted">Bisa sama dengan ID Pegawai atau berbeda</small>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Password -->
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">
                                        Password <span class="required">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="password" name="password" id="password" class="form-control" 
                                               placeholder="Masukkan password" required minlength="6">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fas fa-eye" id="eyeIcon"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">Minimal 6 karakter</small>
                                </div>

                                <!-- Role -->
                                <div class="col-md-6 mb-3">
                                    <label for="role_id" class="form-label">
                                        Role <span class="required">*</span>
                                    </label>
                                    <select name="role_id" id="role_id" class="form-select" required>
                                        <option value="">-- Pilih Role --</option>
                                        <?php foreach ($roles as $role): ?>
                                            <option value="<?php echo $role['role_id']; ?>">
                                                <?php echo htmlspecialchars($role['nama']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Cabang -->
                                <div class="col-md-6 mb-3">
                                    <label for="cabang_id" class="form-label">
                                        Cabang <span class="required">*</span>
                                    </label>
                                    <select name="cabang_id" id="cabang_id" class="form-select" required>
                                        <option value="">-- Pilih Cabang --</option>
                                        <?php foreach ($cabangs as $cabang): ?>
                                            <option value="<?php echo $cabang['id']; ?>">
                                                <?php echo htmlspecialchars($cabang['nama']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Status -->
                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label">
                                        Status <span class="required">*</span>
                                    </label>
                                    <select name="status" id="status" class="form-select" required>
                                        <option value="active" selected>Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary btn-lg" id="btnSubmit">
                                    <i class="fas fa-save me-2"></i>Tambah User
                                </button>
                                <a href="../index.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Kembali
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize Select2 untuk dropdown pegawai
            $('#id_pegawai').select2({
                placeholder: "-- Pilih Pegawai dari EIS --",
                allowClear: true,
                width: '100%'
            });

            // Auto-fill username ketika pegawai dipilih
            $('#id_pegawai').on('change', function() {
                var selectedOption = $(this).find('option:selected');
                var idPegawai = selectedOption.val();
                
                if (idPegawai) {
                    // Auto-fill username dengan ID Pegawai
                    $('#username').val(idPegawai);
                } else {
                    $('#username').val('');
                }
            });

            // Toggle password visibility
            $('#togglePassword').on('click', function() {
                const passwordInput = $('#password');
                const eyeIcon = $('#eyeIcon');
                
                if (passwordInput.attr('type') === 'password') {
                    passwordInput.attr('type', 'text');
                    eyeIcon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    passwordInput.attr('type', 'password');
                    eyeIcon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });

            // Handle form submission dengan AJAX
            $('#formTambahUser').on('submit', function(e) {
                e.preventDefault();
                
                const submitBtn = $('#btnSubmit');
                const originalText = submitBtn.html();
                
                // Disable button dan show loading
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...');
                
                $.ajax({
                    url: 'tambah_user.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showAlert('success', response.message || 'User berhasil ditambahkan!');
                            
                            // Reset form
                            $('#formTambahUser')[0].reset();
                            $('#id_pegawai').val(null).trigger('change');
                            
                            // Redirect setelah 2 detik
                            setTimeout(function() {
                                window.location.href = '../index.php';
                            }, 2000);
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Gagal menambahkan user';
                        
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMsg = xhr.responseJSON.error;
                        }
                        
                        showAlert('danger', errorMsg);
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });

            function showAlert(type, message) {
                const alertHtml = `
                    <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                $('#alertContainer').html(alertHtml);
                
                // Auto dismiss after 5 seconds
                setTimeout(function() {
                    $('.alert').fadeOut();
                }, 5000);
            }
        });
    </script>
</body>
</html>
