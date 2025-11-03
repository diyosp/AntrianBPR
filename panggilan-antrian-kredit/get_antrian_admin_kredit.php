<?php
require_once "../config/database.php";
session_start();
$cabang_id = $_SESSION['cabang_id'] ?? 0;
$tanggal = date('Y-m-d');
$query = "SELECT MAX(no_antrian_kredit) FROM tbl_antrian_kredit WHERE tanggal_kredit = ? AND cabang_id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("si", $tanggal, $cabang_id);
$stmt->execute();
$stmt->bind_result($nomor);
$stmt->fetch();
$stmt->close();
echo $nomor ?? '0';
