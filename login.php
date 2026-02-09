<?php
session_start(); // Memulai sesi
// Read and clear one-time flash error so messages don't persist after refresh
if (isset($_SESSION['flash_error'])) {
    $error = $_SESSION['flash_error'];
    unset($_SESSION['flash_error']);
}
include "config/database.php"; // Koneksi ke database

// Ambil data role dari tabel role
$roles = [];
$roleQuery = "SELECT role_id, nama FROM role";
$roleResult = $mysqli->query($roleQuery);
if ($roleResult->num_rows > 0) {
    while ($row = $roleResult->fetch_assoc()) {
        $roles[] = $row;
    }
}

// Ambil data cabang dari tabel cabang
$cabangs = [];
$cabangQuery = "SELECT id, nama FROM cabang";
$cabangResult = $mysqli->query($cabangQuery);
if ($cabangResult->num_rows > 0) {
    while ($row = $cabangResult->fetch_assoc()) {
        $cabangs[] = $row;
    }
}

// Cek jika form dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? ''; // This will now accept nomor_pegawai or username
    $password = $_POST['password'] ?? '';
    $role_id = isset($_POST['role_id']) && $_POST['role_id'] !== '' ? (int) $_POST['role_id'] : null;
    $cabang_id = isset($_POST['cabang_id']) && $_POST['cabang_id'] !== '' ? (int) $_POST['cabang_id'] : null;

    if ($username === '' || $password === '' || empty($role_id) || empty($cabang_id)) {
        $_SESSION['flash_error'] = "Semua field harus diisi (Username/ID Pegawai, Password, Role, dan Cabang).";
        header("Location: login.php");
        exit;
    } else {
        // First, get the column name from pegawai table to avoid errors
        // Query without JOIN first to find user
        $query = "SELECT u.* 
                  FROM users u 
                  WHERE (u.username = ? OR u.id_pegawai = ?) 
                  AND u.role_id = ? 
                  AND u.cabang_id = ?
                  AND u.status = 'active'";
        $stmt = $mysqli->prepare($query);
        
        if ($stmt) {
            $stmt->bind_param("ssii", $username, $username, $role_id, $cabang_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();

                if (password_verify($password, $user['password'])) {
                    // Get pegawai name from EIS database if id_pegawai exists
                    $nama_pegawai = $user['username']; // default to username
                    
                    if (!empty($user['id_pegawai'])) {
                        // Check what columns exist in pegawai table
                        $pegawaiQuery = "SELECT * FROM bprsukab_eis_update.pegawai WHERE id_pegawai = ? LIMIT 1";
                        $stmtPegawai = $mysqli_eis->prepare($pegawaiQuery);
                        if ($stmtPegawai) {
                            $stmtPegawai->bind_param("s", $user['id_pegawai']);
                            $stmtPegawai->execute();
                            $pegawaiResult = $stmtPegawai->get_result();
                            
                            if ($pegawaiResult && $pegawaiResult->num_rows > 0) {
                                $pegawai = $pegawaiResult->fetch_assoc();
                                
                                // Try different possible column names
                                if (isset($pegawai['nama_pegawai'])) {
                                    $nama_pegawai = $pegawai['nama_pegawai'];
                                } elseif (isset($pegawai['nama'])) {
                                    $nama_pegawai = $pegawai['nama'];
                                } elseif (isset($pegawai['name'])) {
                                    $nama_pegawai = $pegawai['name'];
                                }
                            }
                            $stmtPegawai->close();
                        }
                    }
                    
                    // Login berhasil
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['id_pegawai'] = $user['id_pegawai'];
                    $_SESSION['nama_pegawai'] = $nama_pegawai;
                    $_SESSION['role_id'] = $user['role_id'];
                    $_SESSION['cabang_id'] = $user['cabang_id'];
                    $_SESSION['logged_in'] = true;
                    $_SESSION['login_time'] = time();

                    // Update last login
                    $updateStmt = $mysqli->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                    $updateStmt->bind_param("i", $user['id']);
                    $updateStmt->execute();
                    $updateStmt->close();

                    header("Location: index.php");
                    exit;
                } else {
                    $_SESSION['flash_error'] = "Password salah!";
                    header("Location: login.php");
                    exit;
                }
            } else {
                $_SESSION['flash_error'] = "Username/ID Pegawai, Role, atau Cabang tidak sesuai!";
                header("Location: login.php");
                exit;
            }
            $stmt->close();
        } else {
            $_SESSION['flash_error'] = "Terjadi kesalahan pada server. Silakan coba lagi.";
            header("Location: login.php");
            exit;
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BPR Sukabumi</title>
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css"> <!-- project stylesheet -->
</head>

<body class="d-flex flex-column h-100" style="background-color: #f5f5f5!important;">
    <main class="flex-shrink-0 login-page" style="background: #f5f5f5 !important;">
        <div class="container-fluid p-0 min-vh-100">
            <div class="row g-0 min-vh-100">
                <!-- Left Side - Login Form -->
                <div class="col-lg-6 d-flex align-items-center justify-content-center p-4" style="background-color: #081941;">
                    <div class="w-100" style="max-width: 480px;">
                        <!-- Logo -->
                        <div class="text-center mb-4">
                            <img src="assets/img/logo-antrix.png" alt="logo" style="width: 120px; height: 120px; object-fit: contain;" onerror="this.style.display='none'">
                        </div>

                        <!-- Login Card -->
                        <div class="card shadow-lg" style="border-radius: 20px; border: none; background-color: #0a2351;">
                            <div class="card-body p-4">
                            <form method="POST" action="login.php" novalidate>
                                <!-- Username or ID Pegawai -->
                                <div class="mb-3">
                                    <label for="username" class="form-label fw-semibold mb-2" style="color: #ffffff; font-size: 14px;">Username / ID Pegawai</label>
                                    <input type="text" name="username" id="username" class="form-control" placeholder="Masukkan username atau nomor pegawai" required aria-required="true" style="border-radius: 10px; border: 1px solid #e0e0e0; padding: 10px 14px; font-size: 15px;">
                                    <small class="d-block mt-1" style="color: #b8c5d6; font-size: 12px;">Gunakan username atau nomor pegawai Anda</small>
                                </div>

                                <!-- Password with toggle -->
                                <div class="mb-3 position-relative">
                                    <label for="password" class="form-label fw-semibold mb-2" style="color: #ffffff; font-size: 14px;">Password</label>
                                    <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password" required aria-required="true" style="border-radius: 10px; border: 1px solid #e0e0e0; padding: 10px 14px; padding-right: 45px; font-size: 15px;">
                                    <button type="button" class="btn btn-sm password-toggle" aria-label="Show password" onclick="togglePassword()" style="position: absolute; right: 10px; top: 50%; transform: translateY(-40%); margin-top: 10px; border: none; background: transparent; padding: 4px;">
                                        <img src="assets/img/view.png" class="icon-eye" width="20" height="20" alt="Show password" onerror="this.style.display='none'">
                                        <img src="assets/img/hide.png" class="icon-eye-slash d-none" width="20" height="20" alt="Hide password" onerror="this.style.display='none'">
                                    </button>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-12 col-md-6">
                                        <label for="role_id" class="form-label fw-semibold mb-2" style="color: #ffffff; font-size: 14px;">Role</label>
                                        <select name="role_id" id="role_id" class="form-select" required style="border-radius: 10px; border: 1px solid #e0e0e0; padding: 10px 14px; font-size: 15px;">
                                            <option value="" disabled selected>Pilih Role</option>
                                            <?php foreach ($roles as $role): ?>
                                                <option value="<?php echo $role['role_id']; ?>"><?php echo htmlspecialchars($role['nama']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="cabang_id" class="form-label fw-semibold mb-2" style="color: #ffffff; font-size: 14px;">Cabang</label>
                                        <select name="cabang_id" id="cabang_id" class="form-select" required style="border-radius: 10px; border: 1px solid #e0e0e0; padding: 10px 14px; font-size: 15px;">
                                            <option value="" disabled selected>Pilih Cabang</option>
                                            <?php foreach ($cabangs as $cabang): ?>
                                                <option value="<?php echo $cabang['id']; ?>"><?php echo htmlspecialchars($cabang['nama']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="d-grid mb-3">
                                    <button type="submit" class="btn" style="background: linear-gradient(135deg, #F87B1B 0%, #ff9a4d 100%); border: none; color: white; font-weight: 600; padding: 12px; border-radius: 10px; box-shadow: 0 4px 12px rgba(248, 123, 27, 0.3); transition: all 0.3s ease; font-size: 16px;">Masuk</button>
                                </div>
                                
                                <!-- Show server-side error messages -->
                                <?php if (!empty($error)): ?>
                                    <div class="alert alert-danger text-center shadow-sm" style="border-radius: 10px; border: none; font-size: 14px;"><?php echo htmlspecialchars($error); ?></div>
                                <?php endif; ?>
                            </form>

                            <div class="text-center mt-3">
                                <a href="https://helpdesk.bprsukabumi.co.id/" target="_blank" rel="noopener noreferrer" class="text-decoration-none" style="color: #F87B1B; font-weight: 500; font-size: 14px;">Lupa password?</a>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>

                <!-- Right Side - Branding Panel -->
                <div class="col-lg-6 d-none d-lg-flex align-items-center justify-content-center p-4" style="background-color: #081941;">
                    <div class="text-center text-white mt-4 pt-4">
                        
                        <h1 class="display-4 fw-bold mb-3">Selamat Datang<br>di Antrix</h1>
                        <h5 class="mb-4" style="font-weight: 400; opacity: 0.95;">Sistem Antrian Nasabah</h5>
                        
                        <div class="mx-auto" style="max-width: 500px;">
                            <p class="lead mb-4" style="font-size: 1.1rem; opacity: 0.9;">
                                Kelola antrian dengan mudah dan efisien. Tingkatkan pelayanan nasabah dengan sistem yang modern dan terintegrasi.
                            </p>
                        </div>

                        <div class="row g-4 mt-4 mx-auto" style="max-width: 600px;">
                            <div class="col-md-4">
                                <div class="p-3 h-100 d-flex flex-column align-items-center justify-content-center" style="background: rgba(255,255,255,0.1); border-radius: 12px; min-height: 180px;">
                                    <div class="mb-3">
                                        <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
                                            <circle cx="20" cy="20" r="18" stroke="white" stroke-width="2"/>
                                            <path d="M20 12V20L26 23" stroke="white" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                    </div>
                                    <h6 class="fw-semibold mb-2">Efisien</h6>
                                    <small style="opacity: 0.8;">Hemat waktu pelayanan</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 h-100 d-flex flex-column align-items-center justify-content-center" style="background: rgba(255,255,255,0.1); border-radius: 12px; min-height: 180px;">
                                    <div class="mb-3">
                                        <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
                                            <rect x="8" y="12" width="24" height="20" rx="2" stroke="white" stroke-width="2"/>
                                            <path d="M14 8V12M26 8V12M8 18H32" stroke="white" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                    </div>
                                    <h6 class="fw-semibold mb-2">Terorganisir</h6>
                                    <small style="opacity: 0.8;">Antrian lebih rapi</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 h-100 d-flex flex-column align-items-center justify-content-center" style="background: rgba(255,255,255,0.1); border-radius: 12px; min-height: 180px;">
                                    <div class="mb-3">
                                        <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
                                            <circle cx="20" cy="15" r="5" stroke="white" stroke-width="2"/>
                                            <path d="M10 32C10 26 14 22 20 22C26 22 30 26 30 32" stroke="white" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                    </div>
                                    <h6 class="fw-semibold mb-2">User Friendly</h6>
                                    <small style="opacity: 0.8;">Mudah digunakan</small>
                                </div>
                            </div>
                        </div>

                        
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .btn[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(248, 123, 27, 0.4) !important;
        }
        .form-control:focus, .form-select:focus {
            border-color: #F87B1B !important;
            box-shadow: 0 0 0 0.2rem rgba(248, 123, 27, 0.15) !important;
        }
    </style>
    <script>
        // Password visibility toggle
        function togglePassword() {
            var pw = document.getElementById('password');
            var btn = document.querySelector('.password-toggle');
            if (!pw || !btn) return;
            var eye = btn.querySelector('.icon-eye');
            var eyeSlash = btn.querySelector('.icon-eye-slash');
            if (pw.type === 'password') {
                pw.type = 'text';
                btn.setAttribute('aria-label', 'Hide password');
                if (eye) eye.classList.add('d-none');
                if (eyeSlash) eyeSlash.classList.remove('d-none');
            } else {
                pw.type = 'password';
                btn.setAttribute('aria-label', 'Show password');
                if (eye) eye.classList.remove('d-none');
                if (eyeSlash) eyeSlash.classList.add('d-none');
            }
        }
    </script>
</body>

</html>