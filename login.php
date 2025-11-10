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
    // Safely read POST values to avoid undefined index warnings
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    // role_id and cabang_id may be empty strings when user hasn't selected an option
    $role_id = isset($_POST['role_id']) && $_POST['role_id'] !== '' ? (int) $_POST['role_id'] : null; // Role yang dipilih
    $cabang_id = isset($_POST['cabang_id']) && $_POST['cabang_id'] !== '' ? (int) $_POST['cabang_id'] : null; // Cabang yang dipilih

    // Basic validation: ensure all fields provided
    if ($username === '' || $password === '' || empty($role_id) || empty($cabang_id)) {
        // use flash + PRG so the error is cleared on refresh
        $_SESSION['flash_error'] = "Semua field harus diisi (Username, Password, Role, dan Cabang).";
        header("Location: login.php");
        exit;
    } else {
        // Mencari username di database dengan role dan cabang yang sesuai
        $query = "SELECT * FROM users WHERE username = ? AND role_id = ? AND cabang_id = ?";
        $stmt = $mysqli->prepare($query);
        if ($stmt) {
            $stmt->bind_param("sii", $username, $role_id, $cabang_id); // "sii" artinya string, int, int
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();

                // Verifikasi password
                if (password_verify($password, $user['password'])) {
                    // Login berhasil, set session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role_id'] = $user['role_id'];
                    $_SESSION['cabang_id'] = $user['cabang_id'];

                    // Redirect ke halaman index.php
                    header("Location: index.php");
                    exit;
                } else {
                    $_SESSION['flash_error'] = "Password salah!";
                    header("Location: login.php");
                    exit;
                }
            } else {
                $_SESSION['flash_error'] = "Username, Role, atau Cabang tidak sesuai!";
                header("Location: login.php");
                exit;
            }
        } else {
            // Prepare failed
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

<body class="d-flex flex-column h-100" style="background-color: #081941!important;">
    <main class="flex-shrink-0 login-page" style="background: #081941 !important;">
        <div class="container d-flex align-items-center justify-content-center min-vh-100">
            <div class="row w-100 justify-content-center">
                <div class="col-md-8 col-lg-5">
                    <!-- (moved) error will be displayed below the Masuk button inside the form -->

                    <!-- Card Form Login -->
                    <div class="card login-card shadow-lg">
                        <div class="card-body p-4 p-md-5">
                            <div class="d-flex align-items-center mb-2">
                                <img src="assets/img/testfs.png" alt="logo" class="brand-logo me-3" onerror="this.style.display='none'">
                            </div>

                            <form method="POST" action="login.php" novalidate>
                                <!-- Username -->
                                <div class="mb-3 form-floating">
                                    <input type="text" name="username" id="username" class="form-control" placeholder=" " required aria-required="true">
                                    <label for="username">Username</label>
                                </div>

                                <!-- Password with toggle -->
                                <div class="mb-3 position-relative form-floating">
                                    <input type="password" name="password" id="password" class="form-control" placeholder=" " required aria-required="true">
                                    <label for="password">Password</label>
                                    <button type="button" class="btn btn-sm btn-outline-secondary password-toggle" aria-label="Show password" onclick="togglePassword()">
                                <!-- eye icon (visible) - stroked eye with filled pupil -->
                                    <img src="assets/img/view.png" class="icon-eye" width="20" height="20" alt="Show password" onerror="this.style.display='none'">
                                <!-- eye-slash icon (hidden) -->
                                    <img src="assets/img/hide.png" class="icon-eye-slash d-none" width="20" height="20" alt="Hide password" onerror="this.style.display='none'">
                                    </button>
                                </div>

                                <div class="row g-2">
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3 form-floating">
                                            <select name="role_id" id="role_id" class="form-select" required>
                                                <option value="" disabled selected>Pilih Role</option>
                                                <?php foreach ($roles as $role): ?>
                                                    <option value="<?php echo $role['role_id']; ?>"><?php echo htmlspecialchars($role['nama']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <div class="mb-3 form-floating">
                                            <select name="cabang_id" id="cabang_id" class="form-select" required>
                                                <option value="" disabled selected>Pilih Cabang</option>
                                                <?php foreach ($cabangs as $cabang): ?>
                                                    <option value="<?php echo $cabang['id']; ?>"><?php echo htmlspecialchars($cabang['nama']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-success btn-lg">Masuk</button>
                                </div>
                                <!-- Show server-side error messages directly under the submit button -->
                                <?php if (!empty($error)): ?>
                                    <div class="alert alert-danger text-center shadow-sm mt-3"><?php echo htmlspecialchars($error); ?></div>
                                <?php endif; ?>
                            </form>

                            <div class="text-center mt-3 text-muted small">
                                <a href="#" class="text-decoration-none">Lupa password?</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
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