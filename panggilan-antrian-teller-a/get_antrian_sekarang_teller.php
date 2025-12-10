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

  // sql statement untuk menampilkan data "no_antrian_teller" dari tabel "tbl_antrian_teller"
  // berdasarkan "tanggal", "status_teller = 1", "cabang_id", dan "bagian"
  // Teller A hanya melihat antrian yang sudah diklaim oleh Teller A (bagian = '1')
  $query = mysqli_query($mysqli, "SELECT no_antrian_teller FROM tbl_antrian_teller 
                                    WHERE tanggal_teller='$tanggal' AND status_teller='1' AND cabang_id='$cabang_id'
                                    AND bagian = '1'
                                    ORDER BY updated_date_teller DESC LIMIT 1")
    or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));

  // ambil jumlah baris data hasil query
  $rows = mysqli_num_rows($query);

  // cek hasil query
  // jika data "no_antrian_teller" ada
  if ($rows <> 0) {
    // ambil data hasil query
    $data = mysqli_fetch_assoc($query);
    // buat variabel untuk menampilkan data
    $no_antrian = $data['no_antrian_teller'];

    // tampilkan data
    echo number_format($no_antrian, 0, '', '.');
  }
  // jika data "no_antrian_teller" tidak ada
  else {
    // tampilkan "-"
    echo "-";
  }
}
