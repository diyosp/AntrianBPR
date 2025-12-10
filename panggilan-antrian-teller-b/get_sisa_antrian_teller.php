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

  // sql statement untuk menghitung jumlah sisa antrian teller berdasarkan "tanggal", "status_teller = 0", "cabang_id", dan "bagian"
  // Hitung antrian yang belum diklaim (bagian IS NULL) saja untuk Teller B
  $query = mysqli_query($mysqli, "SELECT count(id_teller) as jumlah FROM tbl_antrian_teller 
                                    WHERE tanggal_teller='$tanggal' AND status_teller='0' AND cabang_id='$cabang_id'
                                    AND bagian IS NULL")
    or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));

  // ambil data hasil query
  $data = mysqli_fetch_assoc($query);

  // buat variabel untuk menampilkan data
  $sisa_antrian = $data['jumlah'];

  // tampilkan data
  echo number_format($sisa_antrian, 0, '', '.');
}
