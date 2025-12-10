<?php
// Antrix Database (main application)
$mysqli = new mysqli("localhost", "root", "", "bprsukab_antrix");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
$mysqli->set_charset("utf8mb4");

// EIS Database (for pegawai data)
$mysqli_eis = new mysqli("localhost", "root", "", "bprsukab_eis_update");
if ($mysqli_eis->connect_error) {
    die("EIS Connection failed: " . $mysqli_eis->connect_error);
}
$mysqli_eis->set_charset("utf8mb4");
?>
