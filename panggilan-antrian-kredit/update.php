<?php
// pengecekan ajax request untuk mencegah direct access file, agar file hanya bisa diakses via AJAX
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
    require_once "../config/database.php";
    session_start();
    $cabang_id = $_SESSION['cabang_id'] ?? null;
    if (!$cabang_id) {
        die('Akses tidak diizinkan!');
    }

    // Pastikan parameter id_kredit diterima
    if (isset($_POST['id_kredit'])) {
        $id = mysqli_real_escape_string($mysqli, $_POST['id_kredit']);
        $action = isset($_POST['action']) ? $_POST['action'] : '';
        $updated_date = gmdate("Y-m-d H:i:s", time() + 60 * 60 * 7);

        $check_query = mysqli_query($mysqli, "SELECT id_kredit, status_kredit, waktu_mulai, waktu_selesai FROM tbl_antrian_kredit WHERE id_kredit='$id' AND cabang_id='$cabang_id'")
            or die('Ada kesalahan pada query validasi cabang : ' . mysqli_error($mysqli));

        if (mysqli_num_rows($check_query) > 0) {
            $row = mysqli_fetch_assoc($check_query);
            $status_kredit = $row['status_kredit'];
            $waktu_mulai = $row['waktu_mulai'];
            $waktu_selesai = $row['waktu_selesai'];

            if ($action === 'start') {
                if ($status_kredit == '0') {
                    if (empty($waktu_mulai)) {
                        $update = mysqli_query($mysqli, "UPDATE tbl_antrian_kredit SET status_kredit='1', updated_date_kredit='$updated_date', waktu_mulai='$updated_date' WHERE id_kredit='$id' AND cabang_id='$cabang_id'")
                            or die('Ada kesalahan pada query update : ' . mysqli_error($mysqli));
                    } else {
                        $update = mysqli_query($mysqli, "UPDATE tbl_antrian_kredit SET status_kredit='1', updated_date_kredit='$updated_date' WHERE id_kredit='$id' AND cabang_id='$cabang_id'")
                            or die('Ada kesalahan pada query update : ' . mysqli_error($mysqli));
                    }
                } else if ($status_kredit == '1') {
                    $update = mysqli_query($mysqli, "UPDATE tbl_antrian_kredit SET updated_date_kredit='$updated_date' WHERE id_kredit='$id' AND cabang_id='$cabang_id'")
                        or die('Ada kesalahan pada query update : ' . mysqli_error($mysqli));
                }
            } else if ($action === 'finish') {
                if ($status_kredit == '1') {
                    $waktu_selesai = $updated_date;
                    $mulai_ts = strtotime($waktu_mulai);
                    $selesai_ts = strtotime($waktu_selesai);
                    $durasi = ($mulai_ts && $selesai_ts) ? max(0, $selesai_ts - $mulai_ts) : 0;
                    $update = mysqli_query($mysqli, "UPDATE tbl_antrian_kredit SET status_kredit='2', updated_date_kredit='$updated_date', waktu_selesai='$waktu_selesai', durasi='$durasi' WHERE id_kredit='$id' AND cabang_id='$cabang_id'")
                        or die('Ada kesalahan pada query update : ' . mysqli_error($mysqli));
                }
            }
        } else {
            die('Data tidak ditemukan atau Anda tidak memiliki akses untuk memperbarui data ini.');
        }
    }
}
