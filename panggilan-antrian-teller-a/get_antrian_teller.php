<?php
// pengecekan ajax request untuk mencegah direct access file, agar file tidak bisa diakses secara langsung dari browser
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
  // panggil file "database.php" untuk koneksi ke database
  require_once "../config/database.php";

  // mulai session untuk mengambil data cabang pengguna
  session_start();

  // ambil cabang_id dari session
  $cabang_id = $_SESSION['cabang_id'] ?? null;

  // cek apakah cabang_id tersedia
  if (!$cabang_id) {
    die('Akses tidak diizinkan!');
  }

  // ambil tanggal sekarang
  $tanggal = gmdate("Y-m-d", time() + 60 * 60 * 7);

  // sql statement untuk menampilkan data dari tabel "tbl_antrian_teller" berdasarkan "tanggal" dan "cabang_id"
  // Teller A hanya bisa melihat antrian yang belum diambil (bagian IS NULL) atau sudah diambil oleh Teller A (bagian = '1')
  $query = mysqli_query($mysqli, "SELECT id_teller, no_antrian_teller, status_teller, bagian FROM tbl_antrian_teller 
                                    WHERE tanggal_teller='$tanggal' AND cabang_id='$cabang_id'
                                    AND (bagian IS NULL OR bagian = '1')")
    or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));

  // ambil jumlah baris data hasil query
  $rows = mysqli_num_rows($query);

  // cek hasil query
  if ($rows <> 0) {
    $response         = array();
    $response["data"] = array();

    // ambil data hasil query
    while ($row = mysqli_fetch_assoc($query)) {
      $data['id_teller']         = $row["id_teller"];
      $data['no_antrian_teller'] = $row["no_antrian_teller"];
      $data['status_teller']     = $row["status_teller"];
      $data['bagian']            = $row["bagian"];

      array_push($response["data"], $data);
    }

    // tampilkan data
    echo json_encode($response);
  } else {
    $response         = array();
    $response["data"] = array();

    // buat data kosong untuk ditampilkan
    $data['id_teller']         = "";
    $data['no_antrian_teller'] = "-";
    $data['status_teller']     = "";

    array_push($response["data"], $data);

    // tampilkan data
    echo json_encode($response);
  }
}
