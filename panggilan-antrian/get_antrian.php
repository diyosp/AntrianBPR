<?php
// pengecekan ajax request untuk mencegah direct access file
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
  // panggil file "database.php" untuk koneksi ke database
  require_once "../config/database.php";

  // mulai session untuk mengambil data cabang pengguna yang login
  session_start();

  // ambil cabang_id dari session
  $cabang_id = $_SESSION['cabang_id'] ?? null;

  // cek apakah cabang_id tersedia
  if (!$cabang_id) {
    die('Akses tidak diizinkan!');
  }

  // ambil tanggal sekarang
  $tanggal = gmdate("Y-m-d", time() + 60 * 60 * 7);

  // sql statement untuk menampilkan data dari tabel "tbl_antrian" berdasarkan "tanggal" dan "cabang_id"
  $query = mysqli_query($mysqli, "SELECT id, no_antrian, status, jumlah_transaksi FROM tbl_antrian 
                                    WHERE tanggal='$tanggal' AND cabang_id='$cabang_id'")
    or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));

  // ambil jumlah baris data hasil query
  $rows = mysqli_num_rows($query);

  // cek hasil query
  if ($rows <> 0) {
    $response         = array();
    $response["data"] = array();

    // ambil data hasil query
    while ($row = mysqli_fetch_assoc($query)) {
      $data['id']                = $row["id"];
      $data['no_antrian']        = $row["no_antrian"];
      $data['status']            = $row["status"];
      $data['jumlah_transaksi']  = $row["jumlah_transaksi"];

      array_push($response["data"], $data);
    }

    // tampilkan data
    echo json_encode($response);
  } else {
    $response         = array();
    $response["data"] = array();

    // buat data kosong untuk ditampilkan
    $data['id']                = "";
    $data['no_antrian']        = "-";
    $data['status']            = "";
    $data['jumlah_transaksi']  = "";

    array_push($response["data"], $data);

    // tampilkan data
    echo json_encode($response);
  }
}
