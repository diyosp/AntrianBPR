<?php
// Pengecekan ajax request untuk mencegah direct access file
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
    session_start();
    require_once "../config/database.php";
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['cabang_id'])) {
        die('Akses tidak diizinkan!');
    }
    $cabang_id = $_SESSION['cabang_id'];
    $tanggal = gmdate("Y-m-d", time() + 60 * 60 * 7);
    $query = mysqli_query($mysqli, "SELECT MAX(no_antrian_kredit) as nomor FROM tbl_antrian_kredit WHERE tanggal_kredit='$tanggal' AND cabang_id='$cabang_id'")
        or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));
    $data = mysqli_fetch_assoc($query);
    $no_antrian = isset($data['nomor']) ? $data['nomor'] + 1 : 1;
    $checkQuery = mysqli_query($mysqli, "SELECT COUNT(*) AS count FROM tbl_antrian_kredit WHERE tanggal_kredit='$tanggal' AND cabang_id='$cabang_id' AND no_antrian_kredit='$no_antrian'")
        or die('Ada kesalahan pada query pengecekan data : ' . mysqli_error($mysqli));
    $checkResult = mysqli_fetch_assoc($checkQuery);
    if ($checkResult['count'] == 0) {
        $insert = mysqli_query($mysqli, "INSERT INTO tbl_antrian_kredit(tanggal_kredit, no_antrian_kredit, cabang_id, bagian) VALUES('$tanggal', '$no_antrian', '$cabang_id', NULL)")
            or die('Ada kesalahan pada query insert : ' . mysqli_error($mysqli));
        if ($insert) {
            echo "Sukses";
        }
    } else {
        echo "Nomor sudah ada";
    }
}
