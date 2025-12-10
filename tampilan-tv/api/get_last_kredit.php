<?php
// Return last called Kredit number (status 1 or finished 2) for today
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
  require_once "../../config/database.php";
  session_start();
  $cabang_id = $_SESSION['cabang_id'] ?? null;
  if (!$cabang_id) { echo "-"; exit; }
  $tanggal = gmdate("Y-m-d", time() + 60 * 60 * 7);
  $sql = "SELECT no_antrian_kredit FROM tbl_antrian_kredit 
          WHERE tanggal_kredit='$tanggal' AND status_kredit IN ('1','2') AND cabang_id='$cabang_id'
          ORDER BY updated_date_kredit DESC LIMIT 1";
  $query = mysqli_query($mysqli, $sql) or die('-');
  if (mysqli_num_rows($query) > 0) {
    $data = mysqli_fetch_assoc($query);
    echo number_format($data['no_antrian_kredit'], 0, '', '.');
  } else {
    echo "-";
  }
}
