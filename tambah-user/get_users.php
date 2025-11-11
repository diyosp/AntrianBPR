<?php
header('Content-Type: application/json');
require_once "../config/database.php";

try {
    $query = "
        SELECT users.id, users.id_pegawai, users.username, role.nama AS role, cabang.nama AS cabang, 
               users.role_id, users.cabang_id, p.id_jabatan, j.jabatan
        FROM users
        JOIN role ON users.role_id = role.role_id
        JOIN cabang ON users.cabang_id = cabang.id
        LEFT JOIN bprsukab_eis.pegawai p ON users.id_pegawai = p.id_pegawai
        LEFT JOIN bprsukab_eis.jabatan j ON p.id_jabatan = j.id_jabatan
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
