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

// Ambil data user untuk ditampilkan di tabel
$users = [];
$userQuery = "
    SELECT users.id, users.username, role.nama AS role, cabang.nama AS cabang, users.role_id, users.cabang_id
    FROM users
    JOIN role ON users.role_id = role.role_id
    JOIN cabang ON users.cabang_id = cabang.id
";
$userResult = $mysqli->query($userQuery);
if ($userResult && $userResult->num_rows > 0) {
    while ($row = $userResult->fetch_assoc()) {
        $users[] = $row;
    }
}
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
        .user-container .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: #fff !important;
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
    </style>
    <main class="flex-shrink-0">
        <div class="container pt-5 user-container">
            <div class="page-header d-flex justify-content-between align-items-center mb-4">
                <h1 class="h5 mb-0">Manajemen User</h1>
                <button class="btn btn-theme" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="bi-person-plus"></i> Tambah User
                </button>
            </div>

            <!-- Tabel Data User -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table id="userTable" class="table table-bordered table-striped mb-0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Cabang</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $key => $user): ?>
                        <tr>
                            <td><?php echo $key + 1; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                            <td><?php echo htmlspecialchars($user['cabang']); ?></td>
                            <td>
                                <button
                                    class="btn btn-warning btn-sm btn-edit"
                                    data-id="<?php echo $user['id']; ?>"
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
        </div>
    </main>

    <!-- Modal Tambah User -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">Tambah User Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addUserForm" method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" name="username" id="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" name="password" id="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="role_id" class="form-label">Role</label>
                            <select name="role_id" id="role_id" class="form-select" required>
                                <option value="" disabled selected>Pilih Role</option>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?php echo $role['role_id']; ?>"><?php echo htmlspecialchars($role['nama']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="cabang_id" class="form-label">Cabang</label>
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
                    <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editUserForm" method="POST" action="edit_user.php">
                        <input type="hidden" name="user_id" id="editUserId">
                        <div class="mb-3">
                            <label for="editUsername" class="form-label">Username</label>
                            <input type="text" name="username" id="editUsername" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="editRoleId" class="form-label">Role</label>
                            <select name="role_id" id="editRoleId" class="form-select" required>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?php echo $role['role_id']; ?>"><?php echo htmlspecialchars($role['nama']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editCabangId" class="form-label">Cabang</label>
                            <select name="cabang_id" id="editCabangId" class="form-select" required>
                                <?php foreach ($cabangs as $cabang): ?>
                                    <option value="<?php echo $cabang['id']; ?>"><?php echo htmlspecialchars($cabang['nama']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editPassword" class="form-label">Password</label>
                            <input type="password" name="password" id="editPassword" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah password">
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

    <!-- JavaScript -->
    <script>
        $(document).ready(function() {
            // Inisialisasi DataTable
            $(document).ready(function() {
                const table = $('#userTable').DataTable({
                    ajax: 'get_users.php',
                    columns: [{
                            data: 'no'
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
                            data: null,
                            render: function(data, type, row) {
                                return `
                        <button class="btn btn-warning btn-sm btn-edit" data-id="${row.id}" data-username="${row.username}" data-role-id="${row.role_id}" data-cabang-id="${row.cabang_id}" data-bs-toggle="modal" data-bs-target="#editUserModal">Edit</button>
                        <button class="btn btn-danger btn-sm btn-delete" data-id="${row.id}">Hapus</button>
                    `;
                            }
                        }
                    ],
                    language: {
                        search: "Cari:",
                        lengthMenu: "Tampilkan _MENU_ data",
                        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                        paginate: {
                            first: "Awal",
                            last: "Akhir",
                            next: "Berikutnya",
                            previous: "Sebelumnya"
                        }
                    }
                });

                // Re-bind event listener setelah tabel diperbarui
                $('#userTable').on('draw.dt', function() {
                    // Event Edit
                    $('.btn-edit').off('click').on('click', function() {
                        const userId = $(this).data('id');
                        const username = $(this).data('username');
                        const roleId = $(this).data('role-id');
                        const cabangId = $(this).data('cabang-id');

                        $('#editUserId').val(userId);
                        $('#editUsername').val(username);
                        $('#editRoleId').val(roleId);
                        $('#editCabangId').val(cabangId);
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