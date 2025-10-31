<?php
include "../header.php";
?>

<body class="d-flex flex-column h-100">
  <main class="flex-shrink-0">
    <div class="container pt-4">
      <div class="d-flex flex-column flex-md-row px-4 py-3 mb-4 bg-white rounded-2 shadow-sm">
        <!-- judul halaman -->
        <div class="d-flex align-items-center me-md-auto">
          <i class="bi-mic-fill text-success me-3 fs-3"></i>
          <h1 class="h5 pt-2">Panggilan Antrian Teller</h1>
        </div>
        <!-- breadcrumbs -->
        <div class="ms-5 ms-md-0 pt-md-3 pb-md-0">
          <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="http://www.indrasatya.com/"><i class="bi-house-fill text-success"></i></a></li>
              <li class="breadcrumb-item" aria-current="page">Dashboard</li>
              <li class="breadcrumb-item" aria-current="page">Antrian</li>
            </ol>
          </nav>
        </div>
      </div>
      <div class="row">
        <!-- menampilkan informasi jumlah antrian -->
        <div class="col-md-3 mb-4">
          <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
              <div class="d-flex justify-content-start">
                <div class="feature-icon-3 me-4">
                  <i class="bi-people text-warning"></i>
                </div>
                <div>
                  <p id="jumlah-antrian-teller" class="fs-3 text-warning mb-1"></p>
                  <p class="mb-0">Jumlah Antrian</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- menampilkan informasi nomor antrian yang sedang dipanggil -->
        <div class="col-md-3 mb-4">
          <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
              <div class="d-flex justify-content-start">
                <div class="feature-icon-3 me-4">
                  <i class="bi-person-check text-success"></i>
                </div>
                <div>
                  <p id="antrian-sekarang-teller" class="fs-3 text-success mb-1"></p>
                  <p class="mb-0">Antrian Sekarang</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- menampilkan informasi nomor antrian yang akan dipanggil selanjutnya -->
        <div class="col-md-3 mb-4">
          <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
              <div class="d-flex justify-content-start">
                <div class="feature-icon-3 me-4">
                  <i class="bi-person-plus text-info"></i>
                </div>
                <div>
                  <p id="antrian-selanjutnya-teller" class="fs-3 text-info mb-1"></p>
                  <p class="mb-0">Antrian Selanjutnya</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- menampilkan informasi jumlah antrian yang belum dipanggil -->
        <div class="col-md-3 mb-4">
          <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
              <div class="d-flex justify-content-start">
                <div class="feature-icon-3 me-4">
                  <i class="bi-person text-danger"></i>
                </div>
                <div>
                  <p id="sisa-antrian-teller" class="fs-3 text-danger mb-1"></p>
                  <p class="mb-0">Sisa Antrian</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
          <div class="table-responsive">
            <table id="tabel-antrian-teller" class="table table-bordered table-striped table-hover" width="100%">
              <thead>
                <tr>
                  <th>Nomor Antrian</th>
                  <th>Status</th>
                  <th>Panggil</th>
                </tr>
              </thead>
            </table>
          </div>
        </div>
      </div>
    </div>
  </main>

  <?php
  include "../footer.php"; ?>

  <audio id="tingtung" src="../assets/audio/tingtung.mp3"></audio>

  <!-- jQuery Core -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
  <!-- Popper and Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.min.js" integrity="sha384-Atwg2Pkwv9vp0ygtn1JAojH0nYbwNJLPhwyoVbhoPwBhjQPR5VtM2+xf0Uwh9KtT" crossorigin="anonymous"></script>

  <!-- DataTables -->
  <script type="text/javascript" src="https://cdn.datatables.net/v/bs5/dt-1.10.25/datatables.min.js"></script>
  <!-- Responsivevoice -->
  <!-- Get API Key -> https://responsivevoice.org/ -->
  <script src="https://code.responsivevoice.org/responsivevoice.js?key=wI8dA0pN"></script>

  <script type="text/javascript">
    $(document).ready(function() {
      // tampilkan informasi antrian
      $('#jumlah-antrian-teller').load('get_jumlah_antrian_teller.php');
      $('#antrian-sekarang-teller').load('get_antrian_sekarang_teller.php');
      $('#antrian-selanjutnya-teller').load('get_antrian_selanjutnya_teller.php');
      $('#sisa-antrian-teller').load('get_sisa_antrian_teller.php');

      // menampilkan data antrian menggunakan DataTables
      var table = $('#tabel-antrian-teller').DataTable({
        "lengthChange": false, // non-aktifkan fitur "lengthChange"
        "searching": false, // non-aktifkan fitur "Search"
        "ajax": "get_antrian_teller.php", // url file proses tampil data dari database
        // menampilkan data
        "columns": [{
            "data": "no_antrian_teller",
            "width": '250px',
            "className": 'text-center'
          },
          {
            "data": "status_teller",
            "visible": false
          },
          {
            "data": null,
            "orderable": false,
            "searchable": false,
            "width": '100px',
            "className": 'text-center',
            "render": function(data, type, row) {
              var btn = "-";
              if (data["status_teller"] === "0") {
                btn = `<button class='btn btn-success btn-sm rounded-circle btn-panggil' data-action='start' title='Panggil'><i class='bi-mic-fill'></i></button>`;
              } else if (data["status_teller"] === "1") {
                btn = `<button class='btn btn-secondary btn-sm rounded-circle btn-panggil' data-action='start' title='Ulangi'><i class='bi-mic-fill'></i></button> ` +
                      `<button class='btn btn-danger btn-sm rounded-circle btn-selesai ms-1' data-action='finish' title='Selesai'><i class='bi-check-lg'></i></button>`;
              }
              return btn;
            }
          },
        ],
        "order": [
          [0, "desc"] // urutkan data berdasarkan "no_antrian" secara descending
        ],
        "iDisplayLength": 10, // tampilkan 10 data per halaman
      });

      // panggilan antrian dan update data
      $('#tabel-antrian-teller tbody').on('click', 'button', function() {
        var data = table.row($(this).parents('tr')).data();
        var id = data["id_teller"];
        var action = $(this).data('action');
        var bell = document.getElementById('tingtung');

        if (action === 'start') {
          bell.pause();
          bell.currentTime = 0;
          bell.play();
          durasi_bell = bell.duration * 770;
          setTimeout(function() {
            responsiveVoice.speak("Nomor Antrian, " + data["no_antrian_teller"] + ", silahkan ke teller satu", "Indonesian Female", {
              rate: 0.9,
              pitch: 1,
              volume: 10
            });
          }, durasi_bell);
        }

        $.ajax({
          type: "POST",
          url: "update.php",
          data: {
            id_teller: id,
            action: action,
            bagian: "1"
          }
        });
      });

      // auto reload data antrian setiap 1 detik untuk menampilkan data secara realtime
      setInterval(function() {
        $('#jumlah-antrian-teller').load('get_jumlah_antrian_teller.php').fadeIn("slow");
        $('#antrian-sekarang-teller').load('get_antrian_sekarang_teller.php').fadeIn("slow");
        $('#antrian-selanjutnya-teller').load('get_antrian_selanjutnya_teller.php').fadeIn("slow");
        $('#sisa-antrian-teller').load('get_sisa_antrian_teller.php').fadeIn("slow");
        table.ajax.reload(null, false);
      }, 1000);
    });
  </script>
</body>

</html>