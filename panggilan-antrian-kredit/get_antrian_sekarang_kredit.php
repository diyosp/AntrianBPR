<?php
// pengecekan ajax request untuk mencegah direct access file
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
    require_once "../config/database.php";
    session_start();
    $cabang_id = $_SESSION['cabang_id'] ?? null;
    if (!$cabang_id) die('Akses tidak diizinkan!');
    $tanggal = gmdate("Y-m-d", time() + 60 * 60 * 7);
    $query = mysqli_query($mysqli, "SELECT no_antrian_kredit FROM tbl_antrian_kredit WHERE tanggal_kredit='$tanggal' AND status_kredit='1' AND cabang_id='$cabang_id' ORDER BY updated_date_kredit DESC LIMIT 1") or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));
    $rows = mysqli_num_rows($query);
    if ($rows <> 0) {
        $data = mysqli_fetch_assoc($query);
        $no_antrian = $data['no_antrian_kredit'];
        echo number_format($no_antrian, 0, '', '.');
    } else {
        echo "-";
    }
}
