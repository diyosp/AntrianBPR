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

  // mengecek data post dari ajax
  if (isset($_POST['id_teller']) && isset($_POST['bagian'])) {
    $id = mysqli_real_escape_string($mysqli, $_POST['id_teller']);
    $bagian = mysqli_real_escape_string($mysqli, $_POST['bagian']);
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $updated_date = gmdate("Y-m-d H:i:s", time() + 60 * 60 * 7);

    // Validasi: Teller B hanya bisa mengakses antrian yang belum diklaim (bagian IS NULL) atau sudah diklaim oleh Teller B (bagian = '2')
    $check_query = mysqli_query($mysqli, "SELECT id_teller, status_teller, waktu_mulai, waktu_selesai, bagian FROM tbl_antrian_teller 
                                           WHERE id_teller='$id' AND cabang_id='$cabang_id' 
                                           AND (bagian IS NULL OR bagian = '2')")
      or die('Ada kesalahan pada query validasi cabang : ' . mysqli_error($mysqli));

    if (mysqli_num_rows($check_query) > 0) {
      $row = mysqli_fetch_assoc($check_query);
      $status_teller = $row['status_teller'];
      $waktu_mulai = $row['waktu_mulai'];
      $waktu_selesai = $row['waktu_selesai'];

      if ($action === 'start') {
        if ($status_teller == '0') {
          if (empty($waktu_mulai)) {
            $update = mysqli_query($mysqli, "UPDATE tbl_antrian_teller SET status_teller='1', updated_date_teller='$updated_date', waktu_mulai='$updated_date', bagian='$bagian' WHERE id_teller='$id' AND cabang_id='$cabang_id'")
              or die('Ada kesalahan pada query update : ' . mysqli_error($mysqli));
          } else {
            $update = mysqli_query($mysqli, "UPDATE tbl_antrian_teller SET status_teller='1', updated_date_teller='$updated_date', bagian='$bagian' WHERE id_teller='$id' AND cabang_id='$cabang_id'")
              or die('Ada kesalahan pada query update : ' . mysqli_error($mysqli));
          }
        } else if ($status_teller == '1') {
          $update = mysqli_query($mysqli, "UPDATE tbl_antrian_teller SET updated_date_teller='$updated_date', bagian='$bagian' WHERE id_teller='$id' AND cabang_id='$cabang_id'")
            or die('Ada kesalahan pada query update : ' . mysqli_error($mysqli));
        }
      } else if ($action === 'finish') {
        if ($status_teller == '1') {
          $waktu_selesai = $updated_date;
          $mulai = strtotime($waktu_mulai);
          $selesai = strtotime($waktu_selesai);
          $durasi = $selesai - $mulai;
          $update = mysqli_query($mysqli, "UPDATE tbl_antrian_teller SET status_teller='2', updated_date_teller='$updated_date', waktu_selesai='$waktu_selesai', durasi='$durasi', bagian='$bagian' WHERE id_teller='$id' AND cabang_id='$cabang_id'")
            or die('Ada kesalahan pada query update : ' . mysqli_error($mysqli));
        }
      }
    } else {
      die('Data tidak ditemukan atau Anda tidak memiliki akses untuk memperbarui data ini.');
    }
  }
}
