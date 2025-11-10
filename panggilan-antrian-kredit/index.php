<?php
include "../header.php";
?>
<body class="d-flex flex-column h-100" style="background-color: #081941;">
  <main class="flex-shrink-0">
    <div class="container pt-5">
      <div class="d-flex flex-column flex-md-row px-4 py-3 mb-4 rounded-2 shadow-sm" style="background-color: #11224E;">
        <div class="d-flex align-items-center me-md-auto">
          <i class="bi-mic-fill me-3 fs-3" style="color: #fff;"></i>
          <h1 class="h5 pt-2" style="color: #fff;">Panggilan Antrian Admin Kredit</h1>
        </div>
        <div class="ms-5 ms-md-0 pt-md-3 pb-md-0">
          <nav style="--bs-breadcrumb-divider: '>'" aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
              <li class="breadcrumb-item"><a href="../index.php" style="color: #fff;"><i class="bi-house-fill"></i></a></li>
              <li class="breadcrumb-item"><a href="../index.php" style="color: #fff; text-decoration: none;">Dashboard</a></li>
              <li class="breadcrumb-item" aria-current="page" style="color: #fff;">Antrian</li>
            </ol>
          </nav>
        </div>
      </div>
      <div class="row">
        <!-- menampilkan informasi jumlah antrian -->
        <div class="col-md-3 mb-4">
          <div class="card border-0 shadow-sm" style="background-color: #11224E;">
            <div class="card-body p-4">
              <div class="d-flex justify-content-start">
                <div class="feature-icon-3 me-4">
                  <i class="bi-people text-warning"></i>
                </div>
                <div>
                  <p id="jumlah-antrian-kredit" class="fs-3 text-warning mb-1"></p>
                  <p class="mb-0" style="color: #fff;">Jumlah Antrian Kredit</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- menampilkan informasi nomor antrian yang sedang dipanggil -->
        <div class="col-md-3 mb-4">
          <div class="card border-0 shadow-sm" style="background-color: #11224E;">
            <div class="card-body p-4">
              <div class="d-flex justify-content-start">
                <div class="feature-icon-3 me-4">
                  <i class="bi-person-check text-success"></i>
                </div>
                <div>
                  <p id="antrian-sekarang-kredit" class="fs-3 text-success mb-1"></p>
                  <p class="mb-0" style="color: #fff;">Antrian Sekarang</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- menampilkan informasi nomor antrian yang akan dipanggil selanjutnya -->
        <div class="col-md-3 mb-4">
          <div class="card border-0 shadow-sm" style="background-color: #11224E;">
            <div class="card-body p-4">
              <div class="d-flex justify-content-start">
                <div class="feature-icon-3 me-4">
                  <i class="bi-person-plus text-info"></i>
                </div>
                <div>
                  <p id="antrian-selanjutnya-kredit" class="fs-3 text-info mb-1"></p>
                  <p class="mb-0" style="color: #fff;">Antrian Selanjutnya</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- menampilkan informasi jumlah antrian yang belum dipanggil -->
        <div class="col-md-3 mb-4">
          <div class="card border-0 shadow-sm" style="background-color: #11224E;">
            <div class="card-body p-4">
              <div class="d-flex justify-content-start">
                <div class="feature-icon-3 me-4">
                  <i class="bi-person text-danger"></i>
                </div>
                <div>
                  <p id="sisa-antrian-kredit" class="fs-3 text-danger mb-1"></p>
                  <p class="mb-0" style="color: #fff;">Sisa Antrian</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="card border-0 shadow-sm" style="background-color: #11224E;">
        <div class="card-body p-4">
          <div class="table">
            <table id="tabel-antrian-kredit" class="table table-bordered table-striped table-hover" width="100%" style="color: #fff;">
              <thead>
                <tr>
                  <th style="color: #fff;">Nomor Antrian</th>
                  <th style="color: #fff;">Status</th>
                  <th style="color: #fff;">Panggil</th>
                </tr>
              </thead>
            </table>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- load file audio bell antrian -->
  <audio id="tingtung" src="../assets/audio/tingtung.mp3"></audio>

  <style>
    /* Make DataTables info text white */
    .dataTables_info {
      color: #fff !important;
    }
    /* Pagination button styling */
    .dataTables_paginate .paginate_button {
      color: #fff !important;
    }
    .dataTables_paginate .paginate_button.current {
      color: #fff !important;
      background: #F87B1B !important;
      border-color: #F87B1B !important;
    }
    .dataTables_paginate .paginate_button:hover {
      color: #fff !important;
      background: #F87B1B !important;
      border-color: #F87B1B !important;
    }
    .pagination .page-link {
      background-color: transparent !important;
      border-color: #fff !important;
      color: #fff !important;
    }
    .pagination .page-item.active .page-link {
      background-color: #F87B1B !important;
      border-color: #F87B1B !important;
      color: #fff !important;
    }
    .pagination .page-link:hover {
      background-color: #F87B1B !important;
      border-color: #F87B1B !important;
      color: #fff !important;
    }
    .pagination .page-item.disabled .page-link {
      background-color: transparent !important;
      border-color: #fff !important;
      color: #fff !important;
    }
    /* Position pagination to bottom right */
    .dataTables_wrapper .dataTables_paginate {
      float: right !important;
      text-align: right !important;
      padding-top: 1rem;
      margin-top: 1rem;
    }
    .dataTables_wrapper .dataTables_info {
      float: left !important;
      padding-top: 1rem;
      margin-top: 1rem;
    }
    #tabel-antrian-kredit_paginate {
      display: flex;
      justify-content: flex-end;
    }
    /* Make info cards uniform height */
    .row .col-md-3 {
      display: flex;
    }
    .row .col-md-3 .card {
      width: 100%;
      min-height: 150px;
    }
    .row .col-md-3 .card-body {
      display: flex;
      align-items: center;
    }
    /* Make table text white for all rows */
    #tabel-antrian-kredit tbody tr {
      color: #fff !important;
    }
    #tabel-antrian-kredit tbody tr td {
      color: #fff !important;
    }
    #tabel-antrian-kredit tbody tr:hover {
      color: #fff !important;
    }
    .table-striped tbody tr:nth-of-type(odd) {
      color: #fff !important;
    }
    .table-striped tbody tr:nth-of-type(even) {
      color: #fff !important;
    }
  </style>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.min.js"></script>
          <script type="text/javascript" src="https://cdn.datatables.net/v/bs5/dt-1.10.25/datatables.min.js"></script>
          <script src="https://code.responsivevoice.org/responsivevoice.js?key=wI8dA0pN"></script>
          <script type="text/javascript">
            $(document).ready(function() {
              $('#jumlah-antrian-kredit').load('get_jumlah_antrian_kredit.php');
              $('#antrian-sekarang-kredit').load('get_antrian_sekarang_kredit.php');
              $('#antrian-selanjutnya-kredit').load('get_antrian_selanjutnya_kredit.php');
              $('#sisa-antrian-kredit').load('get_sisa_antrian_kredit.php');
              var table = $('#tabel-antrian-kredit').DataTable({
                "lengthChange": false,
                "searching": false,
                "ajax": "get_antrian_kredit.php",
                "language": {
                  "decimal": ",",
                  "thousands": ".",
                  "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                  "infoEmpty": "Menampilkan 0 sampai 0 dari 0 entri",
                  "infoFiltered": "(disaring dari _MAX_ total entri)",
                  "lengthMenu": "Tampilkan _MENU_ entri",
                  "loadingRecords": "Memuat...",
                  "processing": "Memproses...",
                  "search": "Cari:",
                  "zeroRecords": "Tidak ada data yang cocok",
                  "paginate": {
                    "first": "Pertama",
                    "last": "Terakhir",
                    "next": "Berikutnya",
                    "previous": "Sebelumnya"
                  }
                },
                "columns": [
                  { "data": "no_antrian_kredit", "width": '250px', "className": 'text-center' },
                  { "data": "status_kredit", "visible": false },
                  {
                    "data": null,
                    "orderable": false,
                    "searchable": false,
                    "width": '100px',
                    "className": 'text-center',
                    "render": function(data, type, row) {
                      var btn = "-";
                      if (data["status_kredit"] === "0") {
                        btn = `<button class='btn btn-success btn-sm rounded-circle btn-panggil' data-action='start' title='Panggil'><i class='bi-mic-fill'></i></button>`;
                      } else if (data["status_kredit"] === "1") {
                        btn = `<button class='btn btn-secondary btn-sm rounded-circle btn-panggil' data-action='start' title='Ulangi'><i class='bi-mic-fill'></i></button> ` +
                              `<button class='btn btn-danger btn-sm rounded-circle btn-selesai ms-1' data-action='finish' title='Selesai'><i class='bi-check-lg'></i></button>`;
                      }
                      return btn;
                    }
                  },
                ],
                "order": [[0, "desc"]],
                "iDisplayLength": 10
              });
              $('#tabel-antrian-kredit tbody').on('click', 'button', function() {
                var data = table.row($(this).parents('tr')).data();
                var id = data["id_kredit"];
                var action = $(this).data('action');
                var bell = document.getElementById('tingtung');
                if (action === 'start') {
                  bell.pause();
                  bell.currentTime = 0;
                  bell.play();
                  durasi_bell = bell.duration * 770;
                  setTimeout(function() {
                    responsiveVoice.speak("Nomor Antrian, " + data["no_antrian_kredit"] + ", silahkan ke admin kredit", "Indonesian Female", {
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
                    id_kredit: id,
                    action: action
                  },
                  success: function(response) {
                    console.log('AJAX success:', response);
                  },
                  error: function(xhr, status, error) {
                    console.error('AJAX error:', status, error, xhr.responseText);
                  }
                });
              });
              setInterval(function() {
                $('#jumlah-antrian-kredit').load('get_jumlah_antrian_kredit.php').fadeIn("slow");
                $('#antrian-sekarang-kredit').load('get_antrian_sekarang_kredit.php').fadeIn("slow");
                $('#antrian-selanjutnya-kredit').load('get_antrian_selanjutnya_kredit.php').fadeIn("slow");
                $('#sisa-antrian-kredit').load('get_sisa_antrian_kredit.php').fadeIn("slow");
                table.ajax.reload(null, false);
              }, 1000);
            });
          </script>
        </body>

        </html>
