<?php
session_start();
require_once "../config/database.php";

// Ambil data role dari tabel role
$roles = [];
$roleQuery = "SELECT role_id, nama FROM role";
$roleResult = $mysqli->query($roleQuery);
if ($roleResult && $roleResult->num_rows > 0) {
    while ($row = $roleResult->fetch_assoc()) {
        $roles[] = $row;
    }
}

// Ambil data cabang dari tabel cabang
$cabangs = [];
$cabangQuery = "SELECT id, nama FROM cabang";
$cabangResult = $mysqli->query($cabangQuery);
if ($cabangResult && $cabangResult->num_rows > 0) {
    while ($row = $cabangResult->fetch_assoc()) {
        $cabangs[] = $row;
    }
}

// Ambil data jabatan yang difilter (CS, Teller, Pimpinan Cabang, Kepala Seksi, Kepala Satuan TI, Staff TI)
$jabatan_list = [];
try {
    $jabatanQuery = "SELECT id_jabatan, jabatan 
                     FROM bprsukab_eis.jabatan 
                     WHERE id_jabatan IN (27, 28, 9, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 41, 44, 56, 59, 63, 1002)
                     ORDER BY jabatan";
    $jabatanResult = $mysqli_eis->query($jabatanQuery);
    
    if ($jabatanResult && $jabatanResult->num_rows > 0) {
        while ($row = $jabatanResult->fetch_assoc()) {
            $jabatan_list[] = $row;
        }
    }
} catch (Exception $e) {
    error_log("Error fetching jabatan: " . $e->getMessage());
}

// Ambil data pegawai dari EIS database
$pegawai_list = [];
try {
    // Get actual column names first
    $columnsQuery = "SHOW COLUMNS FROM bprsukab_eis.pegawai";
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
    
    // Filter only: Customer Service, Teller, Pimpinan Cabang, Kepala Seksi, Kepala Satuan TI, Staff TI
    // Customer Service = 27, Teller = 28, Kepala Cabang = 9, Kepala Seksi = 11-20, 56, 59, 63, 1002, Kepala Satuan TI = 41, Staff TI = 44
    $pegawaiQuery = "SELECT p.id_pegawai, p.$nameColumn as nama_pegawai, p.kode_cabang, p.id_jabatan, j.jabatan 
                     FROM bprsukab_eis.pegawai p
                     LEFT JOIN bprsukab_eis.jabatan j ON p.id_jabatan = j.id_jabatan
                     WHERE p.id_jabatan IN (27, 28, 9, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 41, 44, 56, 59, 63, 1002)
                     ORDER BY j.jabatan, p.$nameColumn";
    $pegawaiResult = $mysqli_eis->query($pegawaiQuery);
    
    if ($pegawaiResult && $pegawaiResult->num_rows > 0) {
        while ($row = $pegawaiResult->fetch_assoc()) {
            $pegawai_list[] = $row;
        }
    }
} catch (Exception $e) {
    error_log("Error fetching pegawai: " . $e->getMessage());
}

// Get filter values
$filter_cabang = isset($_GET['cabang_id']) ? $_GET['cabang_id'] : null;
$filter_role = isset($_GET['role_id']) ? $_GET['role_id'] : null;
$filter_jabatan = isset($_GET['jabatan_id']) ? $_GET['jabatan_id'] : null;

// Ambil data user untuk ditampilkan di tabel
$users = [];
$userQuery = "
    SELECT users.id, users.id_pegawai, users.username, role.nama AS role, cabang.nama AS cabang, 
           users.role_id, users.cabang_id, p.id_jabatan, j.jabatan
    FROM users
    JOIN role ON users.role_id = role.role_id
    JOIN cabang ON users.cabang_id = cabang.id
    LEFT JOIN bprsukab_eis.pegawai p ON users.id_pegawai = p.id_pegawai
    LEFT JOIN bprsukab_eis.jabatan j ON p.id_jabatan = j.id_jabatan
    WHERE 1=1
";

// Add filters to query
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
    $userResult = $stmt->get_result();
} else {
    $userResult = $mysqli->query($userQuery);
}

if ($userResult && $userResult->num_rows > 0) {
    while ($row = $userResult->fetch_assoc()) {
        $users[] = $row;
    }
}

// Determine if filters are active
$has_filters = !empty($filter_cabang) || !empty($filter_role) || !empty($filter_jabatan);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User - BPR Sukabumi</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>

<body class="d-flex flex-column h-100" style="background-color: #081941;">
    <style>
        /* Page color scheme */
        .page-header {
            background-color: #11224E;
            color: #fff;
            padding: 1rem 1rem;
            border-radius: .5rem;
        }
        .page-header h1 { 
            color: #fff;
            margin: 0;
        }
        .user-container .card {
            background-color: #11224E;
            color: #fff;
            border: 0;
        }
        .user-container table {
            color: #fff;
        }
        /* Ensure all table text is white */
        .user-container table,
        .user-container table thead th,
        .user-container table tbody tr,
        .user-container table tbody td {
            color: #fff !important;
        }
        /* DataTables specific styling */
        .user-container .dataTables_wrapper {
            color: #fff;
        }
        .user-container .dataTables_wrapper .dataTables_length,
        .user-container .dataTables_wrapper .dataTables_filter,
        .user-container .dataTables_wrapper .dataTables_info,
        .user-container .dataTables_wrapper .dataTables_paginate {
            color: #fff;
        }
        /* Add spacing between the DataTable search (Cari) and the table */
        .user-container .dataTables_wrapper .dataTables_filter {
            margin-bottom: 0.75rem !important;
        }
        /* Ensure a little gap above the table itself */
        .user-container table.dataTable {
            margin-top: 0.5rem !important;
        }
        .user-container .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: #fff !important;
        }
        /* Add spacing between the table and pagination controls */
        .user-container .dataTables_wrapper .dataTables_paginate {
            margin-top: 0.75rem !important;
        }
        .user-container .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #F87B1B !important;
            border-color: #F87B1B !important;
            color: #fff !important;
        }
        .user-container .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #F87B1B !important;
            border-color: #F87B1B !important;
            color: #fff !important;
        }
        /* Fix DataTable borders */
        .user-container table.dataTable {
            border-collapse: collapse !important;
            width: 100% !important;
            font-size: 0.875rem;
        }
        .user-container table.dataTable thead th,
        .user-container table.dataTable thead td {
            border: 1px solid rgba(255,255,255,0.2) !important;
            padding: 0.5rem !important;
        }
        .user-container table.dataTable tbody th,
        .user-container table.dataTable tbody td {
            border: 1px solid rgba(255,255,255,0.2) !important;
            padding: 0.5rem !important;
        }
        .user-container table.dataTable.table-bordered {
            border: 1px solid rgba(255,255,255,0.2) !important;
        }
        .user-container table .btn-sm {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        .form-label { color: #fff; }
        .form-control, .form-select {
            background-color: #11224E !important;
            color: #fff !important;
            border: 1px solid rgba(255,255,255,0.15) !important;
        }
        .form-control:focus, .form-select:focus {
            background-color: #11224E !important;
            color: #fff !important;
            border: 1px solid rgba(255,255,255,0.3) !important;
            box-shadow: 0 0 0 0.25rem rgba(255,255,255,0.1) !important;
            outline: none !important;
        }
        .form-control:hover, .form-select:hover {
            background-color: #11224E !important;
            color: #fff !important;
            border: 1px solid rgba(255,255,255,0.3) !important;
        }
        select option {
            background-color: #11224E !important;
            color: #fff !important;
        }
        /* Custom white arrow for select dropdowns */
        .user-container .form-select {
            background-image:
                linear-gradient(45deg, transparent 50%, #fff 50%),
                linear-gradient(135deg, #fff 50%, transparent 50%),
                linear-gradient(to right, #11224E, #11224E);
            background-position:
                calc(100% - 18px) calc(50% + 2px),
                calc(100% - 12px) calc(50% + 2px),
                100% 0;
            background-size: 6px 6px, 6px 6px, 2.5em 100%;
            background-repeat: no-repeat;
            padding-right: 2.8em;
        }
        .btn-theme {
            background-color: #F87B1B;
            border-color: #F87B1B;
            color: #fff;
        }
        .btn-theme:hover,
        .btn-theme:focus,
        .btn-theme:active {
            background-color: #F87B1B !important;
            border-color: #F87B1B !important;
            color: #fff !important;
            box-shadow: none !important;
        }
        /* Modal styling */
        .modal-content {
            background-color: #11224E;
            color: #fff;
        }
        .modal-header {
            border-bottom-color: rgba(255,255,255,0.15);
        }
        .modal-title {
            color: #fff;
        }
        .btn-close {
            filter: invert(1);
        }
        /* Modal form-select white arrow */
        .modal-content .form-select {
            background-image:
                linear-gradient(45deg, transparent 50%, #fff 50%),
                linear-gradient(135deg, #fff 50%, transparent 50%),
                linear-gradient(to right, #11224E, #11224E);
            background-position:
                calc(100% - 18px) calc(50% + 2px),
                calc(100% - 12px) calc(50% + 2px),
                100% 0;
            background-size: 6px 6px, 6px 6px, 2.5em 100%;
            background-repeat: no-repeat;
            padding-right: 2.8em;
        }
        /* DataTables search input */
        .user-container .dataTables_wrapper input[type="search"] {
            background-color: #11224E !important;
            color: #fff !important;
            border: 1px solid rgba(255,255,255,0.15) !important;
        }
        .user-container .dataTables_wrapper select {
            background-color: #11224E !important;
            color: #fff !important;
            border: 1px solid rgba(255,255,255,0.15) !important;
        }
        /* Select2 styling for modals */
        .select2-container--default .select2-selection--single {
            background-color: #11224E !important;
            border: 1px solid rgba(255,255,255,0.15) !important;
            height: calc(1.5em + 0.75rem + 2px) !important;
            min-height: 38px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #fff !important;
            line-height: calc(1.5em + 0.75rem) !important;
            padding-left: 12px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: calc(1.5em + 0.75rem + 2px) !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: #fff transparent transparent transparent !important;
        }
        .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
            border-color: transparent transparent #fff transparent !important;
        }
        /* Select2 clear button (X) - make it white */
        .select2-container--default .select2-selection--single .select2-selection__clear {
            color: #fff !important;
            font-size: 1.2em !important;
            font-weight: bold !important;
            margin-left: 2.5rem !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__clear:hover {
            color: #F87B1B !important;
        }
        .select2-dropdown {
            background-color: #11224E !important;
            border: 1px solid rgba(255,255,255,0.15) !important;
        }
        .select2-container--default .select2-results__option {
            background-color: #11224E !important;
            color: #fff !important;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #F87B1B !important;
            color: #fff !important;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field {
            background-color: #11224E !important;
            color: #fff !important;
            border: 1px solid rgba(255,255,255,0.15) !important;
        }
        /* Make DataTable filter and the Add button align on the same line and match heights */
        .dataTables_filter {
            display: flex !important;
            align-items: center;
            gap: 0.5rem;
        }
        .dataTables_filter label {
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .dataTables_filter input[type="search"] {
            height: 38px !important;
            padding: 6px 12px !important;
            border-radius: 6px !important;
        }
        .dataTables_filter .btn-theme {
            height: 38px !important;
            padding: 6px 12px !important;
            display: inline-flex;
            align-items: center;
        }
        .dropdown-menu { background-color: #11224E; color: #fff; }
        .dropdown-menu a { color: #fff; }
    </style>
    <main class="flex-shrink-0">
        <div class="container pt-5 user-container">
            <div class="page-header d-flex align-items-center mb-3">
                <i class="bi-person-square me-3 fs-3" style="margin-top: 0.3rem;"></i>
                <h1 class="h5 pt-2 mb-0">Manajemen User</h1>
                <div class="text-end ms-auto">
                    <a href="../" class="btn btn-outline-light btn-sm">Kembali</a>
                </div>
            </div>

            <!-- Form Filter -->
            <form method="GET" class="row mb-4">
                <div class="col-md-3">
                    <label for="filter_cabang" class="form-label">Cabang</label>
                    <select id="filter_cabang" name="cabang_id" class="form-select">
                        <option value="">Semua Cabang</option>
                        <?php foreach ($cabangs as $cabang): ?>
                            <option value="<?php echo $cabang['id']; ?>" <?php echo (isset($_GET['cabang_id']) && $_GET['cabang_id'] == $cabang['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cabang['nama']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filter_jabatan" class="form-label">Jabatan</label>
                    <select id="filter_jabatan_filter" name="jabatan_id" class="form-select">
                        <option value="">Semua Jabatan</option>
                        <?php foreach ($jabatan_list as $jabatan): ?>
                            <option value="<?php echo $jabatan['id_jabatan']; ?>" <?php echo (isset($_GET['jabatan_id']) && $_GET['jabatan_id'] == $jabatan['id_jabatan']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($jabatan['jabatan']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="filter_role" class="form-label">Role</label>
                    <select id="filter_role" name="role_id" class="form-select">
                        <option value="">Semua Role</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?php echo $role['role_id']; ?>" <?php echo (isset($_GET['role_id']) && $_GET['role_id'] == $role['role_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($role['nama']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end justify-content-end">
                    <div class="btn-group w-100">
                        <button type="submit" class="btn btn-theme">Filter</button>
                        <button type="button" class="btn btn-theme dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="visually-hidden">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="export-pdf.php?<?= http_build_query($_GET) ?>">Download PDF</a></li>
                            <li><a class="dropdown-item" href="export-excel.php?<?= http_build_query($_GET) ?>">Download Excel</a></li>
                            <li><hr class="dropdown-divider" style="background-color: #dee2e6;"></li>
                            <li><a class="dropdown-item" href="print.php?<?= http_build_query($_GET) ?>" target="_blank">Print</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-theme w-100" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="bi-person-plus"></i> Tambah User
                    </button>
                </div>
            </form>

            <!-- Tabel Data User -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <table id="userTable" class="table table-bordered table-striped mb-0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>ID Pegawai</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Cabang</th>
                                <th>Jabatan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $key => $user): ?>
                                <tr>
                                    <td><?php echo $key + 1; ?></td>
                                    <td><?php echo htmlspecialchars($user['id_pegawai'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                                    <td><?php echo htmlspecialchars($user['cabang']); ?></td>
                                    <td><?php echo htmlspecialchars($user['jabatan'] ?? '-'); ?></td>
                                    <td>
                                        <button
                                            class="btn btn-sm btn-edit"
                                            style="background-color: #F87B1B; border-color: #F87B1B; color: #fff;"
                                            data-id="<?php echo $user['id']; ?>"
                                            data-id-pegawai="<?php echo htmlspecialchars($user['id_pegawai'] ?? ''); ?>"
                                            data-id-jabatan="<?php echo htmlspecialchars($user['id_jabatan'] ?? ''); ?>"
                                            data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                            data-role-id="<?php echo $user['role_id']; ?>"
                                            data-cabang-id="<?php echo $user['cabang_id']; ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editUserModal">
                                            Edit
                                        </button>

                                        <button class="btn btn-danger btn-sm btn-delete" data-id="<?php echo $user['id']; ?>">Hapus</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Tambah User -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <i class="bi-person-plus me-3 fs-3" style="margin-top: 0.3rem;"></i>
                    <h5 class="modal-title" id="addUserModalLabel">Tambah User Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addUserForm" method="POST">
                        <div class="mb-3">
                            <label for="filter_jabatan" class="form-label">Filter Jabatan <span style="color: #F87B1B;">*</span></label>
                            <select id="filter_jabatan" class="form-select" required>
                                <option value="">Pilih Jabatan Terlebih Dahulu</option>
                                <?php foreach ($jabatan_list as $jabatan): ?>
                                    <option value="<?php echo $jabatan['id_jabatan']; ?>">
                                        <?php echo htmlspecialchars($jabatan['jabatan']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="id_pegawai" class="form-label">ID Pegawai <span style="color: #F87B1B;">*</span></label>
                            <select name="id_pegawai" id="id_pegawai" class="form-select select2-pegawai" required disabled>
                                <option value="">Pilih Jabatan Terlebih Dahulu</option>
                                <?php foreach ($pegawai_list as $pegawai): ?>
                                    <option value="<?php echo htmlspecialchars($pegawai['id_pegawai']); ?>" 
                                            data-jabatan="<?php echo htmlspecialchars($pegawai['id_jabatan']); ?>"
                                            data-nama="<?php echo htmlspecialchars($pegawai['nama_pegawai']); ?>">
                                        <?php echo htmlspecialchars($pegawai['id_pegawai'] . ' - ' . $pegawai['nama_pegawai']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username <span style="color: #F87B1B;">*</span></label>
                            <input type="text" name="username" id="username" class="form-control" required>
                            <small style="color: #F87B1B;">Bisa sama dengan ID Pegawai atau berbeda</small>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password <span style="color: #F87B1B;">*</span></label>
                            <input type="password" name="password" id="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="role_id" class="form-label">Role <span style="color: #F87B1B;">*</span></label>
                            <select name="role_id" id="role_id" class="form-select" required>
                                <option value="" disabled selected>Pilih Role</option>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?php echo $role['role_id']; ?>"><?php echo htmlspecialchars($role['nama']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="cabang_id" class="form-label">Cabang <span style="color: #F87B1B;">*</span></label>
                            <select name="cabang_id" id="cabang_id" class="form-select" required>
                                <option value="" disabled selected>Pilih Cabang</option>
                                <?php foreach ($cabangs as $cabang): ?>
                                    <option value="<?php echo $cabang['id']; ?>"><?php echo htmlspecialchars($cabang['nama']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" id="submitAddUser" class="btn btn-theme w-100 py-2">Tambah User</button>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit User -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <i class="bi-pencil-square me-3 fs-3" style="margin-top: 0.3rem;"></i>
                    <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editUserForm" method="POST" action="edit_user.php">
                        <input type="hidden" name="user_id" id="editUserId">
                        <div class="mb-3">
                            <label for="edit_filter_jabatan" class="form-label">Filter Jabatan <span style="color: #F87B1B;">*</span></label>
                            <select id="edit_filter_jabatan" class="form-select" required>
                                <option value="">Pilih Jabatan Terlebih Dahulu</option>
                                <?php foreach ($jabatan_list as $jabatan): ?>
                                    <option value="<?php echo $jabatan['id_jabatan']; ?>">
                                        <?php echo htmlspecialchars($jabatan['jabatan']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editIdPegawai" class="form-label">ID Pegawai <span style="color: #F87B1B;">*</span></label>
                            <select name="id_pegawai" id="editIdPegawai" class="form-select select2-pegawai-edit" required disabled>
                                <option value="">Pilih Jabatan Terlebih Dahulu</option>
                                <?php foreach ($pegawai_list as $pegawai): ?>
                                    <option value="<?php echo htmlspecialchars($pegawai['id_pegawai']); ?>" 
                                            data-jabatan="<?php echo htmlspecialchars($pegawai['id_jabatan']); ?>"
                                            data-nama="<?php echo htmlspecialchars($pegawai['nama_pegawai']); ?>">
                                        <?php echo htmlspecialchars($pegawai['id_pegawai'] . ' - ' . $pegawai['nama_pegawai']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editUsername" class="form-label">Username <span style="color: #F87B1B;">*</span></label>
                            <input type="text" name="username" id="editUsername" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="editRoleId" class="form-label">Role <span style="color: #F87B1B;">*</span></label>
                            <select name="role_id" id="editRoleId" class="form-select" required>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?php echo $role['role_id']; ?>"><?php echo htmlspecialchars($role['nama']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editCabangId" class="form-label">Cabang <span style="color: #F87B1B;">*</span></label>
                            <select name="cabang_id" id="editCabangId" class="form-select" required>
                                <?php foreach ($cabangs as $cabang): ?>
                                    <option value="<?php echo $cabang['id']; ?>"><?php echo htmlspecialchars($cabang['nama']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editPassword" class="form-label">Password</label>
                            <input type="password" name="password" id="editPassword" class="form-control">
                            <small style="color: #F87B1B;">Kosongkan jika tidak ingin mengubah password</small>
                        </div>

                        <button type="submit" class="btn btn-theme w-100 py-2">Simpan Perubahan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- JavaScript -->
    <script>
        $(document).ready(function() {
            // Store original pegawai options for filtering
            var originalPegawaiOptions = $('#id_pegawai option').clone();
            var originalPegawaiOptionsEdit = $('#editIdPegawai option').clone();

            // Initialize Select2 for ID Pegawai dropdowns
            $('.select2-pegawai').select2({
                dropdownParent: $('#addUserModal'),
                placeholder: "Pilih Pegawai",
                width: '100%'
            });

            $('.select2-pegawai-edit').select2({
                dropdownParent: $('#editUserModal'),
                placeholder: "Pilih Pegawai",
                width: '100%'
            });

            // Reset Add modal when it's closed
            $('#addUserModal').on('hidden.bs.modal', function () {
                $('#addUserForm')[0].reset();
                $('#filter_jabatan').val('').trigger('change');
                $('#id_pegawai').val(null).trigger('change').prop('disabled', true);
            });

            // Reset Edit modal when it's closed
            $('#editUserModal').on('hidden.bs.modal', function () {
                $('#editUserForm')[0].reset();
                $('#edit_filter_jabatan').val('').trigger('change');
                $('#editIdPegawai').val(null).trigger('change').prop('disabled', true);
            });

            // Filter pegawai by jabatan in Add modal
            $('#filter_jabatan').on('change', function() {
                var selectedJabatan = $(this).val();
                var $pegawaiSelect = $('#id_pegawai');
                
                // Destroy Select2 first
                $pegawaiSelect.select2('destroy');
                
                // Clear all options
                $pegawaiSelect.empty();
                
                if (selectedJabatan) {
                    // Enable pegawai dropdown
                    $pegawaiSelect.prop('disabled', false);
                    
                    // Add placeholder first
                    $pegawaiSelect.append('<option value="">Pilih Pegawai</option>');
                    
                    // Filter and add only matching options
                    originalPegawaiOptions.each(function() {
                        var $option = $(this);
                        var optionJabatan = $option.data('jabatan');
                        
                        // Skip empty option (placeholder)
                        if ($option.val() === '') {
                            return;
                        }
                        
                        // Add only if jabatan matches
                        if (optionJabatan == selectedJabatan) {
                            $pegawaiSelect.append($option.clone());
                        }
                    });
                } else {
                    // Disable pegawai dropdown if no jabatan selected
                    $pegawaiSelect.prop('disabled', true);
                    $pegawaiSelect.append('<option value="">Pilih Jabatan Terlebih Dahulu</option>');
                }
                
                // Re-initialize Select2
                $pegawaiSelect.select2({
                    dropdownParent: $('#addUserModal'),
                    placeholder: "Pilih Pegawai",
                    width: '100%'
                });
            });

            // Filter pegawai by jabatan in Edit modal
            $('#edit_filter_jabatan').on('change', function() {
                var selectedJabatan = $(this).val();
                var $pegawaiSelect = $('#editIdPegawai');
                
                // Store current value before filtering
                var currentValue = $pegawaiSelect.val();
                
                // Destroy Select2 first
                $pegawaiSelect.select2('destroy');
                
                // Clear all options
                $pegawaiSelect.empty();
                
                if (selectedJabatan) {
                    // Enable pegawai dropdown
                    $pegawaiSelect.prop('disabled', false);
                    
                    // Add placeholder first
                    $pegawaiSelect.append('<option value="">Pilih Pegawai</option>');
                    
                    // Filter and add only matching options
                    originalPegawaiOptionsEdit.each(function() {
                        var $option = $(this);
                        var optionJabatan = $option.data('jabatan');
                        
                        // Skip empty option (placeholder)
                        if ($option.val() === '') {
                            return;
                        }
                        
                        // Add only if jabatan matches
                        if (optionJabatan == selectedJabatan) {
                            $pegawaiSelect.append($option.clone());
                        }
                    });
                    
                    // Restore previous value if it's still in the filtered list
                    if (currentValue && $pegawaiSelect.find('option[value="' + currentValue + '"]').length > 0) {
                        $pegawaiSelect.val(currentValue);
                    }
                } else {
                    // Disable pegawai dropdown if no jabatan selected
                    $pegawaiSelect.prop('disabled', true);
                    $pegawaiSelect.append('<option value="">Pilih Jabatan Terlebih Dahulu</option>');
                }
                
                // Re-initialize Select2
                $pegawaiSelect.select2({
                    dropdownParent: $('#editUserModal'),
                    placeholder: "Pilih Pegawai",
                    width: '100%'
                });
            });

            // Auto-fill username when pegawai is selected in Add modal
            $('#id_pegawai').on('change', function() {
                var selectedOption = $(this).find('option:selected');
                var idPegawai = selectedOption.val();
                
                if (idPegawai) {
                    // Auto-fill username with ID Pegawai
                    $('#username').val(idPegawai);
                } else {
                    $('#username').val('');
                }
            });

            // Auto-fill username when pegawai is selected in Edit modal
            $('#editIdPegawai').on('change', function() {
                var selectedOption = $(this).find('option:selected');
                var idPegawai = selectedOption.val();
                
                if (idPegawai) {
                    // Auto-fill username with ID Pegawai (only if username is empty)
                    if ($('#editUsername').val() === '') {
                        $('#editUsername').val(idPegawai);
                    }
                }
            });

            // Inisialisasi DataTable
            $(document).ready(function() {
                <?php if ($has_filters): ?>
                // Use static data when filters are active
                const table = $('#userTable').DataTable({
                    ordering: false,
                    language: {
                        search: "Cari:",
                        lengthMenu: "Tampilkan _MENU_ data",
                        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                        infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                        infoFiltered: "(disaring dari _MAX_ total data)",
                        paginate: {
                            first: "Awal",
                            last: "Akhir",
                            next: "Berikutnya",
                            previous: "Sebelumnya"
                        }
                    }
                });
                <?php else: ?>
                // Use AJAX data when no filters
                const table = $('#userTable').DataTable({
                    ajax: 'get_users.php',
                    ordering: false,
                    columns: [{
                            data: 'no'
                        },
                        {
                            data: 'id_pegawai',
                            render: function(data, type, row) {
                                return data ? data : '-';
                            }
                        },
                        {
                            data: 'username'
                        },
                        {
                            data: 'role'
                        },
                        {
                            data: 'cabang'
                        },
                        {
                            data: 'jabatan',
                            render: function(data, type, row) {
                                return data ? data : '-';
                            }
                        },
                        {
                            data: null,
                            render: function(data, type, row) {
                                return `
                        <button class="btn btn-sm btn-edit" style="background-color: #F87B1B; border-color: #F87B1B; color: #fff;" 
                                data-id="${row.id}" 
                                data-id-pegawai="${row.id_pegawai || ''}" 
                                data-id-jabatan="${row.id_jabatan || ''}" 
                                data-username="${row.username}" 
                                data-role-id="${row.role_id}" 
                                data-cabang-id="${row.cabang_id}" 
                                data-bs-toggle="modal" 
                                data-bs-target="#editUserModal">Edit</button>
                        <button class="btn btn-danger btn-sm btn-delete" data-id="${row.id}">Hapus</button>
                    `;
                            }
                        }
                    ],
                    language: {
                        search: "Cari:",
                        lengthMenu: "Tampilkan _MENU_ data",
                        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                        infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                        infoFiltered: "(disaring dari _MAX_ total data)",
                        paginate: {
                            first: "Awal",
                            last: "Akhir",
                            next: "Berikutnya",
                            previous: "Sebelumnya"
                        }
                    }
                });
                <?php endif; ?>

                // Re-bind event listener setelah tabel diperbarui
                $('#userTable').on('draw.dt', function() {
                    // Event Edit
                    $('.btn-edit').off('click').on('click', function() {
                        const userId = $(this).data('id');
                        const idPegawai = $(this).data('id-pegawai');
                        const idJabatan = $(this).data('id-jabatan');
                        const username = $(this).data('username');
                        const roleId = $(this).data('role-id');
                        const cabangId = $(this).data('cabang-id');

                        $('#editUserId').val(userId);
                        $('#editUsername').val(username);
                        $('#editRoleId').val(roleId);
                        $('#editCabangId').val(cabangId);
                        
                        // Set jabatan filter first, then set pegawai
                        if (idJabatan) {
                            $('#edit_filter_jabatan').val(idJabatan).trigger('change');
                            
                            // Wait a bit for the filter to apply, then set pegawai
                            setTimeout(function() {
                                $('#editIdPegawai').val(idPegawai).trigger('change');
                            }, 100);
                        } else {
                            $('#editIdPegawai').val(idPegawai).trigger('change');
                        }
                    });

                    // Event Delete
                    $('.btn-delete').off('click').on('click', function() {
                        const userId = $(this).data('id');

                        if (confirm('Apakah Anda yakin ingin menghapus user ini?')) {
                            $.ajax({
                                url: 'delete_user.php',
                                type: 'POST',
                                data: {
                                    id: userId
                                },
                                success: function(response) {
                                    alert('User berhasil dihapus!');
                                    table.ajax.reload();
                                },
                                error: function(xhr, status, error) {
                                    alert('Gagal menghapus user: ' + error);
                                }
                            });
                        }
                    });
                });
            });

            $(document).ready(function() {
                // Tangkap submit event pada form Edit User
                $('#editUserForm').on('submit', function(e) {
                    e.preventDefault(); // Mencegah form submit secara default (reload halaman)

                    // Ambil data dari form
                    const formData = {
                        user_id: $('#editUserId').val(),
                        id_pegawai: $('#editIdPegawai').val(),
                        username: $('#editUsername').val(),
                        role_id: $('#editRoleId').val(),
                        cabang_id: $('#editCabangId').val(),
                        password: $('#editPassword').val() // Kosong jika tidak diisi
                    };

                    // Kirim data ke server menggunakan AJAX
                    $.ajax({
                        url: 'edit_user.php', // URL untuk memproses data di server
                        type: 'POST',
                        data: formData,
                        success: function(response) {
                            // Jika berhasil, tampilkan notifikasi dan reload tabel
                            alert('User berhasil diperbarui!');
                            $('#editUserModal').modal('hide'); // Tutup modal Edit User
                            $('#userTable').DataTable().ajax.reload(); // Reload data di tabel tanpa reload halaman
                        },
                        error: function(xhr, status, error) {
                            // Jika ada error, tampilkan notifikasi
                            alert('Gagal memperbarui user: ' + error);
                        }
                    });
                });
            });


        });
    </script>


    <script>
        $(document).ready(function() {
            // Tangkap submit event pada form Tambah User
            $('#addUserForm').on('submit', function(e) {
                e.preventDefault(); // Mencegah form submit secara default (reload halaman)

                // Ambil data dari form
                const formData = {
                    id_pegawai: $('#id_pegawai').val(),
                    username: $('#username').val(),
                    password: $('#password').val(),
                    role_id: $('#role_id').val(),
                    cabang_id: $('#cabang_id').val()
                };

                // Kirim data ke server menggunakan AJAX
                $.ajax({
                    url: 'tambah_user.php', // URL untuk memproses data di server
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        // Jika berhasil, tampilkan notifikasi dan refresh tabel
                        alert('User berhasil ditambahkan!');
                        $('#addUserModal').modal('hide'); // Tutup modal Tambah User
                        $('#addUserForm')[0].reset(); // Reset form
                        $('#id_pegawai').val(null).trigger('change'); // Reset Select2
                        $('#userTable').DataTable().ajax.reload(); // Reload data di tabel
                    },
                    error: function(xhr, status, error) {
                        // Jika ada error, tampilkan notifikasi
                        alert('Gagal menambahkan user: ' + error);
                    }
                });
            });
        });
    </script>

</body>

</html>