<?php
session_start();
header('Content-Type: application/json');
require_once "../config/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    http_response_code(403);
    echo json_encode(['error' => 'Akses ditolak. Anda tidak memiliki izin.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? null;
    $id_pegawai = $_POST['id_pegawai'] ?? null;
    $username = $_POST['username'] ?? null;
    $role_id = $_POST['role_id'] ?? null;
    $cabang_id = $_POST['cabang_id'] ?? null;
    $password = $_POST['password'] ?? '';

    // Validate required fields
    if (empty($user_id) || empty($username) || empty($role_id) || empty($cabang_id)) {
        http_response_code(400);
        echo json_encode(['error' => 'Semua field wajib harus diisi']);
        exit;
    }

    // Jika password tidak kosong, update password juga
    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $query = "UPDATE users SET id_pegawai = ?, username = ?, role_id = ?, cabang_id = ?, password = ? WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ssiisi", $id_pegawai, $username, $role_id, $cabang_id, $hashedPassword, $user_id);
    } else {
        // Jika password kosong, jangan update password
        $query = "UPDATE users SET id_pegawai = ?, username = ?, role_id = ?, cabang_id = ? WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ssiii", $id_pegawai, $username, $role_id, $cabang_id, $user_id);
    }

    // Eksekusi query dan beri response
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'User berhasil diperbarui']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal memperbarui data: ' . $stmt->error]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
