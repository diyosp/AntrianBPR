<?php
// DataTables server-side for antrian kredit
require_once "../config/database.php";
session_start();
$cabang_id = $_SESSION['cabang_id'] ?? null;
$tanggal = gmdate("Y-m-d", time() + 60 * 60 * 7);

header('Content-Type: application/json');

$data = array();
$query = mysqli_query($mysqli, "SELECT id_kredit, no_antrian_kredit, status_kredit FROM tbl_antrian_kredit WHERE tanggal_kredit='$tanggal' AND cabang_id='$cabang_id' ORDER BY no_antrian_kredit DESC");
while ($row = mysqli_fetch_assoc($query)) {
    $data[] = array(
        'id_kredit' => $row['id_kredit'],
        'no_antrian_kredit' => $row['no_antrian_kredit'],
        'status_kredit' => $row['status_kredit']
    );
}
echo json_encode(["data" => $data]);
