<?php
include "../header.php";
?>

<body class="d-flex flex-column h-100">
  <main class="flex-shrink-0">
    <div class="container pt-5">
      <div class="row justify-content-lg-center">
        <div class="col-lg-5 mb-4">
          <div class="px-4 py-3 mb-4 bg-white rounded-2 shadow-sm">
            <div class="d-flex align-items-center me-md-auto">
              <i class="bi-people-fill text-success me-3 fs-3"></i>
              <h1 class="h5 pt-2">Nomor Antrian Admin Kredit</h1>
            </div>
          </div>
          <div class="card border-0 shadow-sm">
            <div class="card-body text-center d-grid p-5">
              <div class="border border-success rounded-2 py-2 mb-5">
                <h3 class="pt-4">ANTRIAN</h3>
                <h1 id="antrian" class="display-1 fw-bold text-success text-center lh-1 pb-2"></h1>
              </div>
              <a id="insert" href="javascript:void(0)" class="btn btn-success btn-block rounded-pill fs-5 px-5 py-4 mb-2">
                <i class="bi-person-plus fs-4 me-2"></i> Ambil Nomor
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
  <?php include "../footer.php"; ?>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
  <script src="print_blue.js"></script>
  <script type="text/javascript">
    $(document).ready(function() {
      $('#antrian').load('get_antrian_kredit.php');
      $('#insert').on('click', function() {
        $.ajax({
          type: 'POST',
          url: 'insert_admin_kredit.php',
          success: function(result) {
            if (result === 'Sukses') {
              $('#antrian').load('get_antrian_kredit.php', function() {
                const nomorAntrian = $('#antrian').text().trim();
                const content = `\x1B\x40 \n\x1B\x61\x01\n\x1B\x45\x01PERUMDA BPR SUKABUMI\x1B\x45\x00\nCabang Cikembar\n\x1B\x61\x01ANTRIAN Admin Kredit\n\x1D\x21\x11NO ${nomorAntrian}\x1D\x21\x00\n${new Date().toLocaleString('id-ID', { weekday: 'long', day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', })}\n-------------------\n`;
                connectToBluetoothPrinter(content);
              });
            }
          }
        });
      });
    });
  </script>
</body>
</html>
