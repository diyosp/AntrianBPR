<?php
// Return last called CS number (status 1 or finished 2) for today
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
  require_once "../../config/database.php";
  session_start();
  $cabang_id = $_SESSION['cabang_id'] ?? null;
  if (!$cabang_id) { echo "-"; exit; }
  $tanggal = gmdate("Y-m-d", time() + 60 * 60 * 7);
  $sql = "SELECT no_antrian FROM tbl_antrian 
          WHERE tanggal='$tanggal' AND status IN ('1','2') AND cabang_id='$cabang_id'
          ORDER BY updated_date DESC LIMIT 1";
  $query = mysqli_query($mysqli, $sql) or die('-');
  if (mysqli_num_rows($query) > 0) {
    $data = mysqli_fetch_assoc($query);
    echo number_format($data['no_antrian'], 0, '', '.');
  } else {
    echo "-";
  }
}
