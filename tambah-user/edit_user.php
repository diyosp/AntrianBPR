<?php
require_once "../config/database.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $id_pegawai = $_POST['id_pegawai'] ?? null;
    $username = $_POST['username'];
    $role_id = $_POST['role_id'];
    $cabang_id = $_POST['cabang_id'];
    $password = $_POST['password'];

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
        $stmt->bind_param("ssisi", $id_pegawai, $username, $role_id, $cabang_id, $user_id);
    }

    // Eksekusi query dan beri response
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(['error' => 'Gagal memperbarui data']);
    }
}
