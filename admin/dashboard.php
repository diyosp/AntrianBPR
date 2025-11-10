<?php
include "../header.php";
session_start();

// Ensure DB available
if (!isset($mysqli) && file_exists(__DIR__ . '/../config/database.php')) {
  include_once __DIR__ . '/../config/database.php';
}

$cabang_id = $_SESSION['cabang_id'] ?? null;
$nama_cabang = "BPR Sukabumi";

// Fetch branch name
if (!empty($cabang_id) && isset($mysqli)) {
  $query = "SELECT nama FROM cabang WHERE id = ?";
  if ($stmt = $mysqli->prepare($query)) {
    $stmt->bind_param("i", $cabang_id);
    $stmt->execute();
    $stmt->bind_result($fetched_name);
    if ($stmt->fetch() && !empty($fetched_name)) {
      $nama_cabang = $fetched_name;
    }
    $stmt->close();
  }
}
?>
<style>
  .stat-card {
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
    overflow: hidden;
  }
  .stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.3) !important;
  }
  .stat-card .card-body {
    position: relative;
    z-index: 2;
  }
  .stat-card .icon-badge {
    position: relative;
    z-index: 1;
    flex-shrink: 0;
  }
  .chart-card {
    transition: box-shadow 0.3s ease;
  }
  .chart-card:hover {
    box-shadow: 0 8px 25px rgba(0,0,0,0.25) !important;
  }
  .performance-table tbody tr {
    transition: background-color 0.2s ease;
  }
  .performance-table tbody tr:hover {
    background-color: rgba(248, 123, 27, 0.1);
  }
  /* Date range filter styling */
  #dateRangeFilter {
    background-color: #11224E !important;
    color: #fff !important;
    border: 1px solid rgba(255,255,255,0.2) !important;
  }
  #dateRangeFilter:focus {
    background-color: #11224E !important;
    color: #fff !important;
    border: 1px solid rgba(255,255,255,0.3) !important;
    box-shadow: 0 0 0 0.25rem rgba(255,255,255,0.1) !important;
    outline: none !important;
  }
  #dateRangeFilter:hover {
    background-color: #11224E !important;
    color: #fff !important;
    border: 1px solid rgba(255,255,255,0.3) !important;
  }
  #dateRangeFilter option {
    background-color: #11224E !important;
    color: #fff !important;
    border: none !important;
  }
  /* White arrow for select dropdown */
  #dateRangeFilter {
    background-image:
      linear-gradient(45deg, transparent 50%, #fff 50%),
      linear-gradient(135deg, #fff 50%, transparent 50%),
      linear-gradient(to right, #11224E, #11224E);
    background-position:
      calc(100% - 18px) calc(50% + 2px),
      calc(100% - 12px) calc(50% + 2px),
      100% 0;
    background-size: 6px 6px, 6px 6px, 2.5em 100%;
    background-repeat: no-repeat;
    padding-right: 2.8em;
  }
</style>
<body class="d-flex flex-column h-100" style="background-color: #081941;">
  <main class="flex-shrink-0">
    <div class="container-fluid pt-4 px-4" style="max-width: 1400px;">
      
      <!-- Header Section -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #11224E 0%, #1a3461 100%); border-radius: 1rem;">
            <div class="card-body p-4">
              <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                  <h3 class="mb-1 text-white fw-bold">
                    <i class="bi-graph-up-arrow me-2"></i>Dashboard Analytics
                  </h3>
                  <p class="mb-0 text-white-50">
                    <i class="bi-building me-1"></i><?= htmlspecialchars($nama_cabang) ?>
                  </p>
                </div>
                <div class="d-flex gap-2 align-items-center flex-wrap">
                  <select id="dateRangeFilter" class="form-select form-select-sm" style="width: 160px;">
                    <option value="7">7 Hari Terakhir</option>
                    <option value="14">14 Hari Terakhir</option>
                    <option value="30">30 Hari Terakhir</option>
                  </select>
                  <button id="btnRefresh" class="btn btn-sm btn-light">
                    <i class="bi-arrow-clockwise"></i> Refresh
                  </button>
                  <a href="../index.php" class="btn btn-sm btn-outline-light">
                    <i class="bi-arrow-left"></i> Kembali
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- KPI Cards -->
      <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
          <div class="card border-0 shadow-sm stat-card" style="background-color: #1e40af; border-radius: 1rem;">
            <div class="card-body p-4">
              <div class="d-flex justify-content-between align-items-start gap-3">
                <div class="flex-grow-1">
                  <p class="mb-2 text-white-50 small fw-semibold text-uppercase">Total Antrian</p>
                  <h2 class="mb-1 text-white fw-bold" id="kpiTotalQueue">
                    <span class="spinner-border spinner-border-sm text-white" role="status"></span>
                  </h2>
                  <small class="text-white-50">Periode terpilih</small>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background-color: #3b82f6;">
                  <i class="bi-people fs-3 text-white"></i>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-sm-6 col-lg-3">
          <div class="card border-0 shadow-sm stat-card" style="background-color: #0f766e; border-radius: 1rem;">
            <div class="card-body p-4">
              <div class="d-flex justify-content-between align-items-start gap-3">
                <div class="flex-grow-1">
                  <p class="mb-2 text-white-50 small fw-semibold text-uppercase">Rata-rata Tunggu</p>
                  <h2 class="mb-1 text-white fw-bold" id="kpiAvgWait">
                    <span class="spinner-border spinner-border-sm text-white" role="status"></span>
                  </h2>
                  <small class="text-white-50">Dalam menit</small>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background-color: #10b981;">
                  <i class="bi-clock-history fs-3 text-white"></i>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-sm-6 col-lg-3">
          <div class="card border-0 shadow-sm stat-card" style="background-color: #6b21a8; border-radius: 1rem;">
            <div class="card-body p-4">
              <div class="d-flex justify-content-between align-items-start gap-3">
                <div class="flex-grow-1">
                  <p class="mb-2 text-white-50 small fw-semibold text-uppercase">Bagian Aktif</p>
                  <h2 class="mb-1 text-white fw-bold" id="kpiActiveSections">
                    <span class="spinner-border spinner-border-sm text-white" role="status"></span>
                  </h2>
                  <small class="text-white-50">Total layanan</small>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background-color: #8b5cf6;">
                  <i class="bi-person-badge fs-3 text-white"></i>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-sm-6 col-lg-3">
          <div class="card border-0 shadow-sm stat-card" style="background-color: #0284c7; border-radius: 1rem;">
            <div class="card-body p-4">
              <div class="d-flex justify-content-between align-items-start gap-3">
                <div class="flex-grow-1">
                  <p class="mb-2 text-white-50 small fw-semibold text-uppercase">Jam Sibuk</p>
                  <h2 class="mb-1 text-white fw-bold" id="kpiPeakHour">
                    <span class="spinner-border spinner-border-sm text-white" role="status"></span>
                  </h2>
                  <small class="text-white-50">Peak time</small>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background-color: #0ea5e9;">
                  <i class="bi-graph-up fs-3 text-white"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Row 1: Throughput & Activity -->
      <div class="row g-3 mb-4">
        <div class="col-lg-8">
          <div class="card border-0 shadow-sm chart-card" style="background-color: #11224E; border-radius: 1rem; height: 450px;">
            <div class="card-body p-4 d-flex flex-column h-100">
              <h6 class="mb-4 text-white fw-semibold d-flex align-items-center">
              <i class="bi-graph-up text-warning" style="margin-right: 0.5rem;"></i>
                Throughput - Tren Antrian
              </h6>
              <div class="flex-grow-1" style="position: relative;">
                <canvas id="chartThroughput"></canvas>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="card border-0 shadow-sm chart-card" style="background-color: #11224E; border-radius: 1rem; height: 450px;">
            <div class="card-body p-4 d-flex flex-column h-100">
              <h6 class="mb-4 text-white fw-semibold d-flex align-items-center">
                  <i class="bi-pie-chart text-info" style="margin-right: 0.5rem;"></i>
                Aktivitas per Bagian
              </h6>
              <div class="flex-grow-1" style="position: relative;">
                <canvas id="chartPerStaff"></canvas>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Row 2: Wait Time & Hourly Distribution -->
      <div class="row g-3 mb-4">
        <div class="col-lg-6">
          <div class="card border-0 shadow-sm chart-card" style="background-color: #11224E; border-radius: 1rem;">
            <div class="card-body p-4">
              <h6 class="mb-4 text-white fw-semibold d-flex align-items-center">
                  <i class="bi-hourglass-split text-danger" style="margin-right: 0.5rem;"></i>
                Rata-rata Waktu Tunggu
              </h6>
              <canvas id="chartAvgWait" height="100"></canvas>
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="card border-0 shadow-sm chart-card" style="background-color: #11224E; border-radius: 1rem;">
            <div class="card-body p-4">
              <h6 class="mb-4 text-white fw-semibold d-flex align-items-center">
                  <i class="bi-clock text-success" style="margin-right: 0.5rem;"></i>
                Distribusi Jam Sibuk
              </h6>
              <canvas id="chartHourlyDist" height="100"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- Service Comparison -->
      <div class="row g-3 mb-4">
        <div class="col-12">
          <div class="card border-0 shadow-sm chart-card" style="background-color: #11224E; border-radius: 1rem;">
            <div class="card-body p-4">
              <h6 class="mb-4 text-white fw-semibold d-flex align-items-center">
                  <i class="bi-bar-chart-line text-primary" style="margin-right: 0.5rem;"></i>
                Perbandingan Jenis Layanan
              </h6>
              <canvas id="chartServiceComparison" height="70"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- Performance Table -->
      <div class="row g-3 mb-4">
        <div class="col-12">
          <div class="card border-0 shadow-sm" style="background-color: #11224E; border-radius: 1rem;">
            <div class="card-body p-4">
              <h6 class="mb-4 text-white fw-semibold d-flex align-items-center">
                  <i class="bi-trophy text-warning" style="margin-right: 0.5rem;"></i>
                Performa per Bagian
              </h6>
              <div class="table-responsive">
                <table class="table table-dark table-hover performance-table mb-0">
                  <thead style="background-color: rgba(248, 123, 27, 0.1);">
                    <tr>
                      <th class="border-0 py-3 fw-semibold">Bagian</th>
                      <th class="border-0 py-3 text-center fw-semibold">Total Dilayani</th>
                      <th class="border-0 py-3 text-center fw-semibold">Rata-rata Tunggu</th>
                      <th class="border-0 py-3 text-center fw-semibold">Waktu Layanan</th>
                      <th class="border-0 py-3 text-center fw-semibold">Efisiensi</th>
                    </tr>
                  </thead>
                  <tbody id="performanceTableBody">
                    <tr>
                      <td colspan="5" class="text-center py-5">
                        <div class="spinner-border spinner-border-sm text-warning me-2" role="status"></div>
                        <span class="text-white-50">Memuat data performa...</span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </main>

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    let charts = {};
    let currentDays = 7;

    async function fetchAnalytics(days = 7) {
      const res = await fetch(`api/analytics.php?days=${days}`);
      if (!res.ok) throw new Error('Failed to load analytics');
      return res.json();
    }

    function updateKPIs(data) {
      const total = data.throughput.reduce((a, b) => a + b, 0);
      const avgWaitSeconds = data.avg_wait.length > 0 
        ? Math.round(data.avg_wait.reduce((a, b) => a + b, 0) / data.avg_wait.length) 
        : 0;
      const avgWaitMinutes = (avgWaitSeconds / 60).toFixed(1);
      
      document.getElementById('kpiTotalQueue').textContent = total.toLocaleString('id-ID');
      document.getElementById('kpiAvgWait').textContent = avgWaitMinutes + ' menit';
      document.getElementById('kpiActiveSections').textContent = data.per_staff.length;
      document.getElementById('kpiPeakHour').textContent = data.peak_hour || '-';
    }

    function createOrUpdateChart(id, config) {
      if (charts[id]) {
        charts[id].destroy();
      }
      const ctx = document.getElementById(id).getContext('2d');
      charts[id] = new Chart(ctx, config);
    }

    function renderCharts(data) {
      const chartDefaults = {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: { labels: { color: '#fff', font: { size: 12 } } }
        },
        scales: {
          y: { 
            beginAtZero: true, 
            ticks: { color: '#aaa', font: { size: 11 } },
            grid: { color: 'rgba(255,255,255,0.1)' }
          },
          x: { 
            ticks: { color: '#aaa', font: { size: 11 } },
            grid: { color: 'rgba(255,255,255,0.1)' }
          }
        }
      };

      // Throughput Chart
      createOrUpdateChart('chartThroughput', {
        type: 'line',
        data: { 
          labels: data.dates, 
          datasets: [{ 
            label: 'Jumlah Antrian', 
            data: data.throughput, 
            borderColor: '#FFA500', 
            backgroundColor: 'rgba(255,165,0,0.15)', 
            tension: 0.4,
            fill: true,
            borderWidth: 3,
            pointRadius: 4,
            pointHoverRadius: 6
          }] 
        },
        options: { 
          responsive: true,
          maintainAspectRatio: false,
          plugins: { 
            legend: { 
              display: false,
              labels: { color: '#fff', font: { size: 12 } }
            },
            tooltip: { 
              mode: 'index', 
              intersect: false,
              backgroundColor: 'rgba(0,0,0,0.8)',
              padding: 12,
              titleFont: { size: 13 },
              bodyFont: { size: 12 }
            }
          },
          scales: {
            y: { 
              beginAtZero: true, 
              ticks: { color: '#aaa', font: { size: 11 } },
              grid: { color: 'rgba(255,255,255,0.1)' }
            },
            x: { 
              ticks: { color: '#aaa', font: { size: 11 } },
              grid: { color: 'rgba(255,255,255,0.1)' }
            }
          }
        }
      });

      // Avg Wait Chart
      createOrUpdateChart('chartAvgWait', {
        type: 'bar',
        data: { 
          labels: data.dates, 
          datasets: [{ 
            label: 'Waktu Tunggu (detik)', 
            data: data.avg_wait, 
            backgroundColor: '#4BC0C0',
            borderRadius: 6,
            barThickness: 20
          }] 
        },
        options: { 
          ...chartDefaults,
          plugins: { legend: { display: false } }
        }
      });

      // Per Staff Pie Chart
      const staffLabels = data.per_staff.map(i => i.label);
      const staffData = data.per_staff.map(i => i.value);
      createOrUpdateChart('chartPerStaff', {
        type: 'doughnut',
        data: { 
          labels: staffLabels, 
          datasets: [{ 
            data: staffData, 
            backgroundColor: ['#FF6384','#36A2EB','#FFCE56','#7C4DFF','#4CAF50','#FF9F40'],
            borderWidth: 0
          }] 
        },
        options: { 
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { 
              position: 'bottom', 
              labels: { color: '#fff', padding: 15, font: { size: 12 } }
            }
          }
        }
      });

      // Hourly Distribution Chart
      createOrUpdateChart('chartHourlyDist', {
        type: 'bar',
        data: {
          labels: data.hourly_dist?.hours || [],
          datasets: [{
            label: 'Antrian per Jam',
            data: data.hourly_dist?.counts || [],
            backgroundColor: '#36A2EB',
            borderRadius: 6,
            barThickness: 18
          }]
        },
        options: {
          ...chartDefaults,
          plugins: { legend: { display: false } }
        }
      });

      // Service Comparison Chart
      createOrUpdateChart('chartServiceComparison', {
        type: 'bar',
        data: {
          labels: data.dates,
          datasets: [
            {
              label: 'CS',
              data: data.service_breakdown?.cs || [],
              backgroundColor: '#FF6384',
              borderRadius: 4
            },
            {
              label: 'Teller',
              data: data.service_breakdown?.teller || [],
              backgroundColor: '#36A2EB',
              borderRadius: 4
            },
            {
              label: 'Kredit',
              data: data.service_breakdown?.kredit || [],
              backgroundColor: '#FFCE56',
              borderRadius: 4
            }
          ]
        },
        options: {
          ...chartDefaults,
          plugins: { 
            legend: { position: 'top' }
          },
          scales: {
            y: { stacked: true, ...chartDefaults.scales.y },
            x: { stacked: true, ...chartDefaults.scales.x }
          }
        }
      });

      // Performance Table
      const tbody = document.getElementById('performanceTableBody');
      if (data.staff_performance && data.staff_performance.length > 0) {
        tbody.innerHTML = data.staff_performance.map(staff => `
          <tr>
            <td class="py-3">
              <span class="fw-semibold">${staff.name}</span>
            </td>
            <td class="text-center py-3">
              <span class="badge bg-primary">${staff.total_served}</span>
            </td>
            <td class="text-center py-3">${(staff.avg_wait / 60).toFixed(1)} menit</td>
            <td class="text-center py-3">${(staff.avg_service_time / 60).toFixed(1)} menit</td>
            <td class="text-center py-3">
              <span class="badge ${staff.efficiency >= 80 ? 'bg-success' : staff.efficiency >= 60 ? 'bg-warning text-dark' : 'bg-danger'}" style="min-width: 60px;">
                ${staff.efficiency}%
              </span>
            </td>
          </tr>
        `).join('');
      } else {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-white-50">Tidak ada data performa tersedia</td></tr>';
      }
    }

    async function loadDashboard(days = 7) {
      try {
        const data = await fetchAnalytics(days);
        updateKPIs(data);
        renderCharts(data);
      } catch (err) {
        console.error(err);
        alert('Gagal memuat analytics: ' + err.message);
      }
    }

    // Event Listeners
    document.getElementById('dateRangeFilter').addEventListener('change', (e) => {
      currentDays = parseInt(e.target.value);
      loadDashboard(currentDays);
    });

    document.getElementById('btnRefresh').addEventListener('click', () => {
      location.reload();
    });

    // Initial load
    loadDashboard();
  </script>

  <!-- Popper and Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
</body>
</html>
