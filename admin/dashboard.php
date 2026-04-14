<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
include "../header.php";

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

// Determine greeting based on hour
$hour = (int) date('G');
if ($hour < 11) {
  $greeting = "Selamat Pagi";
  $greetIcon = "bi-brightness-high";
} elseif ($hour < 15) {
  $greeting = "Selamat Siang";
  $greetIcon = "bi-sun";
} elseif ($hour < 18) {
  $greeting = "Selamat Sore";
  $greetIcon = "bi-sunset";
} else {
  $greeting = "Selamat Malam";
  $greetIcon = "bi-moon-stars";
}
?>
<style>
  /* ===== Google Fonts ===== */
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

  /* ===== Base ===== */
  .dashboard-wrap {
    font-family: 'Inter', 'Raleway', sans-serif;
    min-height: 100vh;
    background: #081941;
  }

  /* ===== Greeting Header ===== */
  .greeting-card {
    background: #11224E;
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 1.25rem;
    padding: 2rem 2.25rem;
    position: relative;
    overflow: hidden;
  }
  .greeting-title {
    font-size: 1.65rem;
    font-weight: 700;
    color: #fff;
    margin-bottom: 0.25rem;
    letter-spacing: -0.02em;
  }
  .greeting-sub {
    color: rgba(255,255,255,0.55);
    font-size: 0.9rem;
    font-weight: 400;
  }
  .greeting-date {
    color: rgba(255,255,255,0.45);
    font-size: 0.82rem;
  }

  /* ===== Period Selector ===== */
  .period-selector {
    display: inline-flex;
    background: rgba(255,255,255,0.06);
    border-radius: 0.75rem;
    padding: 4px;
    gap: 2px;
  }
  .period-btn {
    background: none;
    border: none;
    color: rgba(255,255,255,0.5);
    font-size: 0.8rem;
    font-weight: 500;
    padding: 0.4rem 1rem;
    border-radius: 0.6rem;
    cursor: pointer;
    transition: all 0.25s ease;
  }
  .period-btn:hover {
    color: rgba(255,255,255,0.8);
    background: rgba(255,255,255,0.05);
  }
  .period-btn.active {
    background: #F87B1B;
    color: #fff;
    box-shadow: 0 4px 14px rgba(248,123,27,0.35);
  }

  /* ===== Summary Cards ===== */
  .summary-card {
    background: #11224E;
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 1.15rem;
    padding: 1.5rem 1.6rem;
    position: relative;
    overflow: hidden;
    cursor: default;
  }
  .summary-card .card-icon {
    width: 52px;
    height: 52px;
    border-radius: 0.85rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    flex-shrink: 0;
  }
  .summary-card .card-icon.orange {
    background: #F87B1B;
    color: #fff;
    box-shadow: 0 6px 20px rgba(248,123,27,0.3);
  }
  .summary-card .card-icon.blue,
  .summary-card .card-icon.teal,
  .summary-card .card-icon.purple {
    background: #F87B1B;
    color: #fff;
    box-shadow: 0 6px 20px rgba(248,123,27,0.3);
  }
  .card-label {
    font-size: 0.78rem;
    font-weight: 600;
    color: rgba(255,255,255,0.5);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.35rem;
  }
  .card-value {
    font-size: 1.85rem;
    font-weight: 800;
    color: #fff;
    line-height: 1.1;
    margin-bottom: 0.25rem;
    letter-spacing: -0.03em;
  }
  .card-hint {
    font-size: 0.75rem;
    color: rgba(255,255,255,0.38);
    font-weight: 400;
    line-height: 1.4;
  }

  /* ===== Chart Panels ===== */
  .chart-panel {
    background: #11224E;
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 1.15rem;
    padding: 1.6rem 1.8rem;
    transition: all 0.35s ease;
  }
  .chart-panel:hover {
    border-color: rgba(255,255,255,0.1);
    box-shadow: 0 8px 30px rgba(0,0,0,0.25);
  }
  .chart-panel-title {
    font-size: 1rem;
    font-weight: 700;
    color: #fff;
    margin-bottom: 0.15rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  .chart-panel-title i {
    color: #F87B1B;
    font-size: 1.1rem;
  }
  .chart-panel-desc {
    font-size: 0.78rem;
    color: rgba(255,255,255,0.4);
    margin-bottom: 1.25rem;
    line-height: 1.5;
  }

  /* ===== Action Buttons ===== */
  .btn-action {
    background: #F87B1B;
    color: #fff;
    border: none;
    border-radius: 0.7rem;
    padding: 0.5rem 1.15rem;
    font-size: 0.82rem;
    font-weight: 600;
    transition: all 0.25s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
  }
  .btn-action:hover {
    background: #e56c10;
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 4px 14px rgba(248,123,27,0.35);
  }
  .btn-back {
    background: rgba(255,255,255,0.08);
    color: rgba(255,255,255,0.7);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 0.7rem;
    padding: 0.5rem 1.15rem;
    font-size: 0.82rem;
    font-weight: 500;
    transition: all 0.25s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
  }
  .btn-back:hover {
    background: rgba(255,255,255,0.12);
    color: #fff;
  }

  /* ===== Skeleton Loading ===== */
  .skeleton-pulse {
    display: inline-block;
    width: 60px;
    height: 32px;
    background: linear-gradient(90deg, rgba(255,255,255,0.06) 25%, rgba(255,255,255,0.12) 50%, rgba(255,255,255,0.06) 75%);
    background-size: 200% 100%;
    animation: pulse 1.5s ease-in-out infinite;
    border-radius: 0.5rem;
  }
  @keyframes pulse {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
  }

  /* ===== Fade-in Animation ===== */
  .fade-up {
    opacity: 0;
    transform: translateY(16px);
    animation: fadeUp 0.5s ease forwards;
  }
  @keyframes fadeUp {
    to { opacity: 1; transform: translateY(0); }
  }
  .fade-up:nth-child(1) { animation-delay: 0.05s; }
  .fade-up:nth-child(2) { animation-delay: 0.1s; }
  .fade-up:nth-child(3) { animation-delay: 0.15s; }
  .fade-up:nth-child(4) { animation-delay: 0.2s; }

  /* ===== Today Badge ===== */
  .today-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    background: rgba(248,123,27,0.12);
    color: #F87B1B;
    font-size: 0.72rem;
    font-weight: 600;
    padding: 0.3rem 0.7rem;
    border-radius: 2rem;
    border: 1px solid rgba(248,123,27,0.15);
  }

  /* ===== Info tooltip ===== */
  .info-tip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: rgba(255,255,255,0.08);
    color: rgba(255,255,255,0.4);
    font-size: 0.65rem;
    cursor: help;
    transition: all 0.2s ease;
    margin-left: 0.35rem;
  }
  .info-tip:hover {
    background: rgba(248,123,27,0.2);
    color: #F87B1B;
  }

  /* ===== Teller Info Pill ===== */
  .teller-info-pill {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 0.65rem;
    padding: 0.55rem 0.85rem;
    font-size: 0.72rem;
    color: rgba(255,255,255,0.5);
    display: flex;
    align-items: center;
    gap: 0.4rem;
  }
  .teller-info-pill i {
    color: #60a5fa;
    font-size: 0.8rem;
  }

  /* ===== Responsive ===== */
  @media (max-width: 768px) {
    .greeting-card { padding: 1.5rem; }
    .greeting-title { font-size: 1.3rem; }
    .chart-panel { padding: 1.2rem; }
    .summary-card { padding: 1.25rem; }
    .card-value { font-size: 1.5rem; }
  }
</style>

<body class="dashboard-wrap">
  <main class="flex-shrink-0">
    <div class="container-fluid pt-4 pb-5 px-3 px-md-4" style="max-width: 1400px;">

      <!-- ===== Greeting Header ===== -->
      <div class="greeting-card mb-4 fade-up">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
          <div>
            <div class="greeting-title">
              <i class="<?= $greetIcon ?> me-2" style="color: #F87B1B;"></i><?= $greeting ?>!
            </div>
            <div class="greeting-sub mt-1">
              <i class="bi-building me-1"></i><?= htmlspecialchars($nama_cabang) ?>
            </div>
            <div class="greeting-date mt-2">
              <i class="bi-calendar3 me-1"></i><?php
                $fmt = new IntlDateFormatter('id_ID', IntlDateFormatter::FULL, IntlDateFormatter::NONE, null, null, 'EEEE, dd MMMM yyyy');
                echo $fmt->format(new DateTime());
              ?>
            </div>
          </div>
          <div class="d-flex flex-column flex-sm-row align-items-end align-items-sm-center gap-2">
            <!-- Period Selector -->
            <div class="period-selector" id="periodSelector">
              <button class="period-btn active" data-days="7">7 Hari</button>
              <button class="period-btn" data-days="14">14 Hari</button>
              <button class="period-btn" data-days="30">30 Hari</button>
            </div>
            <div class="d-flex gap-2">
              <button id="btnRefresh" class="btn-action" title="Muat ulang data">
                <i class="bi-arrow-clockwise"></i> Refresh
              </button>
              <a href="../index.php" class="btn-back" title="Kembali ke halaman utama">
                <i class="bi-arrow-left"></i> Kembali
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- ===== Summary Cards ===== -->
      <div class="row g-3 mb-4">

        <!-- Total Antrian -->
        <div class="col-6 col-lg-3 fade-up">
          <div class="summary-card h-100">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div class="card-icon orange">
                <i class="bi-people-fill"></i>
              </div>
              <span class="today-badge" id="todayBadge" style="display:none;">
                <i class="bi-circle-fill" style="font-size:0.4rem;"></i> Hari ini
              </span>
            </div>
            <div class="card-label">Total Antrian</div>
            <div class="card-value" id="kpiTotalQueue">
              <div class="skeleton-pulse"></div>
            </div>
            <div class="card-hint">Jumlah seluruh antrian dalam periode yang dipilih</div>
          </div>
        </div>

        <!-- Rata-rata per Hari -->
        <div class="col-6 col-lg-3 fade-up">
          <div class="summary-card h-100">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div class="card-icon blue">
                <i class="bi-bar-chart-line-fill"></i>
              </div>
            </div>
            <div class="card-label">Rata-rata / Hari</div>
            <div class="card-value" id="kpiAvgWait">
              <div class="skeleton-pulse"></div>
            </div>
            <div class="card-hint">Rata-rata jumlah antrian setiap harinya</div>
          </div>
        </div>

        <!-- Bagian Aktif -->
        <div class="col-6 col-lg-3 fade-up">
          <div class="summary-card h-100">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div class="card-icon teal">
                <i class="bi-grid-3x3-gap-fill"></i>
              </div>
            </div>
            <div class="card-label">Jenis Layanan</div>
            <div class="card-value" id="kpiActiveSections">
              <div class="skeleton-pulse"></div>
            </div>
            <div class="card-hint">Jumlah bagian layanan yang aktif melayani nasabah</div>
          </div>
        </div>

        <!-- Jam Tersibuk -->
        <div class="col-6 col-lg-3 fade-up">
          <div class="summary-card h-100">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div class="card-icon purple">
                <i class="bi-clock-fill"></i>
              </div>
            </div>
            <div class="card-label">Jam Tersibuk</div>
            <div class="card-value" id="kpiPeakHour">
              <div class="skeleton-pulse"></div>
            </div>
            <div class="card-hint">Jam dengan antrian paling banyak — siapkan staf lebih!</div>
          </div>
        </div>
      </div>

      <!-- ===== Charts Row 1: Trend + Layanan ===== -->
      <div class="row g-3 mb-4">
        <!-- Trend Antrian -->
        <div class="col-lg-8 fade-up">
          <div class="chart-panel h-100" style="min-height: 420px;">
            <div class="chart-panel-title">
              <i class="bi-graph-up"></i> Tren Antrian Harian
              <span class="info-tip" title="Grafik ini menunjukkan berapa banyak nasabah yang mengambil nomor antrian setiap harinya.">?</span>
            </div>
            <div class="chart-panel-desc">
              Lihat pola kunjungan nasabah dari hari ke hari. Garis naik berarti antrian semakin ramai.
            </div>
            <div style="position: relative; flex: 1; min-height: 310px;">
              <canvas id="chartThroughput"></canvas>
            </div>
          </div>
        </div>

        <!-- Pembagian Layanan -->
        <div class="col-lg-4 fade-up">
          <div class="chart-panel h-100" style="min-height: 420px;">
            <div class="chart-panel-title">
              <i class="bi-pie-chart-fill"></i> Pembagian Layanan
              <span class="info-tip" title="Persentase antrian dari masing-masing bagian layanan (CS, Teller, Kredit).">?</span>
            </div>
            <div class="chart-panel-desc">
              Seberapa sibuk masing-masing loket?
            </div>
            <div style="position: relative; min-height: 270px;">
              <canvas id="chartPerStaff"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- ===== Charts Row 2: Waktu Tunggu + Jam Sibuk ===== -->
      <div class="row g-3 mb-4">
        <!-- Waktu Tunggu -->
        <div class="col-lg-6 fade-up">
          <div class="chart-panel">
            <div class="chart-panel-title">
              <i class="bi-hourglass-split"></i> Waktu Tunggu Nasabah
              <span class="info-tip" title="Rata-rata berapa menit nasabah menunggu sebelum dilayani setiap harinya.">?</span>
            </div>
            <div class="chart-panel-desc">
              Semakin pendek batangnya, semakin cepat nasabah dilayani. Target: di bawah 10 menit.
            </div>
            <canvas id="chartAvgWait" height="100"></canvas>
          </div>
        </div>

        <!-- Jam Sibuk -->
        <div class="col-lg-6 fade-up">
          <div class="chart-panel">
            <div class="chart-panel-title">
              <i class="bi-alarm-fill"></i> Jam Sibuk Sepanjang Hari
              <span class="info-tip" title="Menunjukkan jam berapa saja nasabah paling banyak datang, dari pagi sampai sore.">?</span>
            </div>
            <div class="chart-panel-desc">
              Grafik ini membantu Anda mengetahui kapan perlu menambah petugas di loket.
            </div>
            <canvas id="chartHourlyDist" height="100"></canvas>
          </div>
        </div>
      </div>

      <!-- ===== Chart Row 3: Perbandingan Layanan ===== -->
      <div class="row g-3 mb-4">
        <div class="col-12 fade-up">
          <div class="chart-panel">
            <div class="chart-panel-title">
              <i class="bi-bar-chart-steps"></i> Perbandingan Jenis Layanan per Hari
              <span class="info-tip" title="Membandingkan jumlah antrian CS, Teller, dan Kredit setiap harinya secara bertumpuk.">?</span>
            </div>
            <div class="chart-panel-desc">
              Grafik bertumpuk menunjukkan kontribusi masing-masing layanan terhadap total antrian harian.
            </div>
            <canvas id="chartServiceComparison" height="70"></canvas>
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

    // ===== Fetch Analytics =====
    async function fetchAnalytics(days = 7) {
      const res = await fetch(`api/analytics.php?days=${days}`);
      if (!res.ok) throw new Error('Gagal memuat data');
      return res.json();
    }

    // ===== Update KPI Cards =====
    function updateKPIs(data) {
      const total = data.throughput.reduce((a, b) => a + b, 0);
      const avgQueue = data.avg_queue || 0;

      animateValue('kpiTotalQueue', total, ' antrian');
      animateValue('kpiAvgWait', avgQueue, ' / hari');
      animateValue('kpiActiveSections', data.per_staff.length, ' bagian');

      const peakEl = document.getElementById('kpiPeakHour');
      peakEl.textContent = data.peak_hour || '-';

      // Show "today" badge if 7 days
      const badge = document.getElementById('todayBadge');
      if (badge && currentDays <= 7) badge.style.display = 'inline-flex';
      else if (badge) badge.style.display = 'none';
    }

    // ===== Animate Counter =====
    function animateValue(id, target, suffix = '') {
      const el = document.getElementById(id);
      if (!el) return;
      const duration = 800;
      const start = performance.now();
      const from = 0;

      function step(now) {
        const progress = Math.min((now - start) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3); // ease-out cubic
        const current = Math.round(from + (target - from) * eased);
        el.textContent = current.toLocaleString('id-ID') + suffix;
        if (progress < 1) requestAnimationFrame(step);
      }
      requestAnimationFrame(step);
    }

    // ===== Create or Update Chart =====
    function createOrUpdateChart(id, config) {
      if (charts[id]) charts[id].destroy();
      const ctx = document.getElementById(id).getContext('2d');
      charts[id] = new Chart(ctx, config);
    }

    // ===== Format date labels =====
    function formatDate(dateStr) {
      const d = new Date(dateStr);
      const day = d.getDate();
      const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agt','Sep','Okt','Nov','Des'];
      return `${day} ${months[d.getMonth()]}`;
    }

    // ===== Render All Charts =====
    function renderCharts(data) {
      const formattedDates = data.dates.map(formatDate);

      const gridColor = 'rgba(255,255,255,0.06)';
      const tickColor = 'rgba(255,255,255,0.4)';
      const tickFont = { size: 11, family: 'Inter', weight: '500' };

      const baseScales = {
        y: {
          beginAtZero: true,
          ticks: { color: tickColor, font: tickFont, padding: 8 },
          grid: { color: gridColor, drawBorder: false }
        },
        x: {
          ticks: { color: tickColor, font: tickFont, padding: 8 },
          grid: { color: 'transparent' }
        }
      };

      const tooltipStyle = {
        backgroundColor: 'rgba(17,34,78,0.95)',
        titleColor: '#fff',
        bodyColor: 'rgba(255,255,255,0.8)',
        padding: 14,
        cornerRadius: 10,
        titleFont: { size: 13, weight: '600', family: 'Inter' },
        bodyFont: { size: 12, family: 'Inter' },
        borderColor: 'rgba(255,255,255,0.08)',
        borderWidth: 1,
        displayColors: true,
        boxPadding: 4
      };

      // ── Throughput (Line) ──
      createOrUpdateChart('chartThroughput', {
        type: 'line',
        data: {
          labels: formattedDates,
          datasets: [{
            label: 'Jumlah Antrian',
            data: data.throughput,
            borderColor: '#F87B1B',
            backgroundColor: (ctx) => {
              const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 320);
              gradient.addColorStop(0, 'rgba(248,123,27,0.25)');
              gradient.addColorStop(1, 'rgba(248,123,27,0.02)');
              return gradient;
            },
            tension: 0.4,
            fill: true,
            borderWidth: 3,
            pointRadius: 5,
            pointBackgroundColor: '#F87B1B',
            pointBorderColor: '#122556',
            pointBorderWidth: 3,
            pointHoverRadius: 8,
            pointHoverBorderWidth: 3,
            pointHoverBackgroundColor: '#fff',
            pointHoverBorderColor: '#F87B1B'
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: { mode: 'index', intersect: false },
          plugins: {
            legend: { display: false },
            tooltip: { ...tooltipStyle, mode: 'index', intersect: false }
          },
          scales: baseScales
        }
      });

      // ── Avg Wait Time (Bar) ──
      createOrUpdateChart('chartAvgWait', {
        type: 'bar',
        data: {
          labels: formattedDates,
          datasets: [{
            label: 'Waktu Tunggu (menit)',
            data: data.avg_wait,
            backgroundColor: (ctx) => {
              const val = ctx.raw;
              if (val > 15) return 'rgba(239,68,68,0.8)';
              if (val > 10) return 'rgba(245,158,11,0.8)';
              return 'rgba(16,185,129,0.8)';
            },
            borderRadius: 8,
            barThickness: 20,
            maxBarThickness: 28
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: true,
          plugins: {
            legend: { display: false },
            tooltip: tooltipStyle
          },
          scales: baseScales
        }
      });

      // ── Color map for services ──
      const serviceColors = {
        'Customer Service': { bg: '#F87B1B', border: '#e56c10' },
        'CS': { bg: '#F87B1B', border: '#e56c10' },
        'Teller': { bg: '#2563eb', border: '#1d4ed8' },
        'Teller A': { bg: '#3b82f6', border: '#2563eb' },
        'Teller B': { bg: '#60a5fa', border: '#3b82f6' },
        'Kredit': { bg: '#0d9488', border: '#0f766e' }
      };

      // ── Per Staff (Doughnut) ──
      const staffLabels = data.per_staff.map(i => i.label);
      const staffData = data.per_staff.map(i => i.value);
      const staffBg = staffLabels.map(l => (serviceColors[l] || { bg: '#6b7280' }).bg);

      createOrUpdateChart('chartPerStaff', {
        type: 'doughnut',
        data: {
          labels: staffLabels,
          datasets: [{
            data: staffData,
            backgroundColor: staffBg,
            borderWidth: 0,
            hoverOffset: 8,
            spacing: 3
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: '62%',
          plugins: {
            legend: {
              position: 'bottom',
              labels: {
                color: 'rgba(255,255,255,0.7)',
                padding: 16,
                font: { size: 12, family: 'Inter', weight: '500' },
                usePointStyle: true,
                pointStyle: 'rectRounded'
              }
            },
            tooltip: tooltipStyle
          }
        }
      });

      // ── Hourly Distribution (Bar) ──
      createOrUpdateChart('chartHourlyDist', {
        type: 'bar',
        data: {
          labels: data.hourly_dist?.hours || [],
          datasets: [{
            label: 'Antrian per Jam',
            data: data.hourly_dist?.counts || [],
            backgroundColor: (ctx) => {
              const maxVal = Math.max(...(data.hourly_dist?.counts || [0]));
              return ctx.raw === maxVal
                ? 'rgba(248,123,27,0.9)'
                : 'rgba(96,165,250,0.6)';
            },
            borderRadius: 8,
            barThickness: 18,
            maxBarThickness: 24
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: true,
          plugins: {
            legend: { display: false },
            tooltip: tooltipStyle
          },
          scales: baseScales
        }
      });

      // ── Service Comparison (Stacked Bar) ──
      (function() {
        const sb = data.service_breakdown || {};
        const datasets = [];

        datasets.push({
          label: 'Customer Service',
          data: sb.cs || [],
          backgroundColor: 'rgba(248,123,27,0.85)',
          borderRadius: 4,
          barThickness: 18
        });

        if (Array.isArray(sb.teller_a) && Array.isArray(sb.teller_b)) {
          datasets.push({
            label: 'Teller A',
            data: sb.teller_a,
            backgroundColor: 'rgba(59,130,246,0.85)',
            borderRadius: 4,
            barThickness: 18
          });
          datasets.push({
            label: 'Teller B',
            data: sb.teller_b,
            backgroundColor: 'rgba(96,165,250,0.7)',
            borderRadius: 4,
            barThickness: 18
          });
        } else {
          datasets.push({
            label: 'Teller',
            data: sb.teller || [],
            backgroundColor: 'rgba(37,99,235,0.85)',
            borderRadius: 4,
            barThickness: 18
          });
        }

        datasets.push({
          label: 'Kredit',
          data: sb.kredit || [],
          backgroundColor: 'rgba(13,148,136,0.85)',
          borderRadius: 4,
          barThickness: 18
        });

        createOrUpdateChart('chartServiceComparison', {
          type: 'bar',
          data: { labels: formattedDates, datasets },
          options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
              legend: {
                position: 'top',
                labels: {
                  color: 'rgba(255,255,255,0.7)',
                  padding: 16,
                  font: { size: 12, family: 'Inter', weight: '500' },
                  usePointStyle: true,
                  pointStyle: 'rectRounded'
                }
              },
              tooltip: tooltipStyle
            },
            scales: {
              y: { stacked: true, ...baseScales.y },
              x: { stacked: true, ...baseScales.x }
            }
          }
        });
      })();
    }

    // ===== Load Dashboard =====
    async function loadDashboard(days = 7) {
      try {
        const data = await fetchAnalytics(days);
        updateKPIs(data);
        renderCharts(data);
      } catch (err) {
        console.error(err);
        // Show a friendly inline error instead of alert
        const cards = document.querySelectorAll('.card-value');
        cards.forEach(el => {
          el.innerHTML = '<span style="color: rgba(239,68,68,0.8); font-size: 0.85rem;">Gagal memuat</span>';
        });
      }
    }

    // ===== Period Selector =====
    document.querySelectorAll('#periodSelector .period-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        document.querySelectorAll('#periodSelector .period-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        currentDays = parseInt(this.dataset.days);
        loadDashboard(currentDays);
      });
    });

    // ===== Refresh Button =====
    document.getElementById('btnRefresh').addEventListener('click', () => {
      loadDashboard(currentDays);
    });

    // ===== Initialize =====
    loadDashboard();
  </script>

  <!-- Popper and Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
</body>
</html>
