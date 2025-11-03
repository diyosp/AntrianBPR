<?php
// pengecekan ajax request untuk mencegah direct access file
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
    require_once "../config/database.php";
    session_start();
    $cabang_id = $_SESSION['cabang_id'] ?? null;
    if (!$cabang_id) die('Akses tidak diizinkan!');
    $tanggal = gmdate("Y-m-d", time() + 60 * 60 * 7);
    $query = mysqli_query($mysqli, "SELECT COUNT(id_kredit) as jumlah FROM tbl_antrian_kredit WHERE tanggal_kredit='$tanggal' AND status_kredit='0' AND cabang_id='$cabang_id'") or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));
    $data = mysqli_fetch_assoc($query);
    $sisa_antrian = $data['jumlah'];
    echo number_format($sisa_antrian, 0, '', '.');
}
