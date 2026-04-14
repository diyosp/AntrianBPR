<?php
session_start();
header('Content-Type: application/json');
require_once "../config/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    http_response_code(403);
    echo json_encode(['error' => 'Akses ditolak. Anda tidak memiliki izin.']);
    exit;
}

try {
    $query = "
        SELECT users.id, users.id_pegawai, users.username, role.nama AS role, cabang.nama AS cabang, 
               users.role_id, users.cabang_id, p.id_jabatan, j.jabatan
        FROM users
        JOIN role ON users.role_id = role.role_id
        JOIN cabang ON users.cabang_id = cabang.id
        LEFT JOIN bprsukab_eis_update.pegawai p ON users.id_pegawai = p.id_pegawai
        LEFT JOIN bprsukab_eis_update.jabatan j ON p.id_jabatan = j.id_jabatan
    ";
    $result = $mysqli->query($query);

    if (!$result) {
        throw new Exception($mysqli->error);
    }

    $users = [];
    if ($result->num_rows > 0) {
        $no = 1;
        while ($row = $result->fetch_assoc()) {
            $row['no'] = $no++; // Tambahkan nomor urut
            $users[] = $row;
        }
    }

    echo json_encode(['data' => $users]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
