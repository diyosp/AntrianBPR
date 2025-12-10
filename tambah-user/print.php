<?php
session_start();
require_once "../config/database.php";

// Get filter values
$filter_cabang = isset($_GET['cabang_id']) ? $_GET['cabang_id'] : null;
$filter_role = isset($_GET['role_id']) ? $_GET['role_id'] : null;
$filter_jabatan = isset($_GET['jabatan_id']) ? $_GET['jabatan_id'] : null;

// Build query
$userQuery = "
    SELECT users.id, users.id_pegawai, users.username, role.nama AS role, cabang.nama AS cabang, 
           users.role_id, users.cabang_id, p.id_jabatan, j.jabatan
    FROM users
    JOIN role ON users.role_id = role.role_id
    JOIN cabang ON users.cabang_id = cabang.id
    LEFT JOIN bprsukab_eis_update.pegawai p ON users.id_pegawai = p.id_pegawai
    LEFT JOIN bprsukab_eis_update.jabatan j ON p.id_jabatan = j.id_jabatan
    WHERE 1=1
";

$params = [];
$types = '';

if (!empty($filter_cabang)) {
    $userQuery .= " AND users.cabang_id = ?";
    $params[] = $filter_cabang;
    $types .= 'i';
}

if (!empty($filter_role)) {
    $userQuery .= " AND users.role_id = ?";
    $params[] = $filter_role;
    $types .= 'i';
}

if (!empty($filter_jabatan)) {
    $userQuery .= " AND p.id_jabatan = ?";
    $params[] = $filter_jabatan;
    $types .= 'i';
}

$userQuery .= " ORDER BY users.id ASC";

if (!empty($params)) {
    $stmt = $mysqli->prepare($userQuery);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $mysqli->query($userQuery);
}

// Build filter description
$filter_desc = [];
if (!empty($filter_cabang)) {
    $cabang_query = $mysqli->prepare("SELECT nama FROM cabang WHERE id = ?");
    $cabang_query->bind_param("i", $filter_cabang);
    $cabang_query->execute();
    $cabang_result = $cabang_query->get_result();
    if ($cabang_row = $cabang_result->fetch_assoc()) {
        $filter_desc[] = "Cabang: " . $cabang_row['nama'];
    }
}
if (!empty($filter_role)) {
    $role_query = $mysqli->prepare("SELECT nama FROM role WHERE role_id = ?");
    $role_query->bind_param("i", $filter_role);
    $role_query->execute();
    $role_result = $role_query->get_result();
    if ($role_row = $role_result->fetch_assoc()) {
        $filter_desc[] = "Role: " . $role_row['nama'];
    }
}
if (!empty($filter_jabatan)) {
    $jabatan_query = $mysqli_eis->prepare("SELECT jabatan FROM bprsukab_eis_update.jabatan WHERE id_jabatan = ?");
    $jabatan_query->bind_param("i", $filter_jabatan);
    $jabatan_query->execute();
    $jabatan_result = $jabatan_query->get_result();
    if ($jabatan_row = $jabatan_result->fetch_assoc()) {
        $filter_desc[] = "Jabatan: " . $jabatan_row['jabatan'];
    }
}

$filter_text = !empty($filter_desc) ? implode(", ", $filter_desc) : "Semua Data";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Laporan User</title>
    <style>
        @media print {
            .no-print { display: none; }
            @page { margin: 1cm; }
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        h2 {
            text-align: center;
            margin-bottom: 5px;
        }
        h3 {
            text-align: center;
            margin-top: 5px;
            font-weight: normal;
            font-size: 14px;
        }
        .filter-info {
            text-align: center;
            margin: 10px 0;
            font-style: italic;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .print-button {
            text-align: center;
            margin: 20px 0;
        }
        .btn {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .footer {
            margin-top: 20px;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="print-button no-print">
        <button class="btn" onclick="window.print()">Print</button>
        <button class="btn" onclick="window.close()" style="background-color: #6c757d;">Tutup</button>
    </div>

    <h2>Laporan Manajemen User</h2>
    <h3>BPR Sukabumi</h3>
    <div class="filter-info">Filter: <?php echo htmlspecialchars($filter_text); ?></div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>ID Pegawai</th>
                <th>Username</th>
                <th>Role</th>
                <th>Cabang</th>
                <th>Jabatan</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $nomor = 1;
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$nomor}</td>";
                echo "<td>" . htmlspecialchars($row['id_pegawai'] ?? '-') . "</td>";
                echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                echo "<td>" . htmlspecialchars($row['role']) . "</td>";
                echo "<td>" . htmlspecialchars($row['cabang']) . "</td>";
                echo "<td>" . htmlspecialchars($row['jabatan'] ?? '-') . "</td>";
                echo "</tr>";
                $nomor++;
            }
            ?>
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: <?php echo date('d/m/Y H:i:s'); ?>
    </div>
</body>
</html>
