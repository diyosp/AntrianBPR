<?php
// Return last called Teller 2 number for today (cabang 12 only)
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
  require_once "../../config/database.php";
  session_start();
  $cabang_id = $_SESSION['cabang_id'] ?? null;
  if ($cabang_id != '312') { echo "-"; exit; }
  $tanggal = gmdate("Y-m-d", time() + 60 * 60 * 7);
  // Use 'bagian' column to identify teller assignment (2 = Teller 2)
  $sql = "SELECT no_antrian_teller FROM tbl_antrian_teller 
    WHERE tanggal_teller='$tanggal' AND status_teller IN ('1','2') AND cabang_id='$cabang_id' AND bagian='2'
    ORDER BY updated_date_teller DESC LIMIT 1";
  $query = mysqli_query($mysqli, $sql) or die('-');
  if (mysqli_num_rows($query) > 0) {
    $data = mysqli_fetch_assoc($query);
    echo number_format($data['no_antrian_teller'], 0, '', '.');
  } else {
    echo "-";
  }
}
