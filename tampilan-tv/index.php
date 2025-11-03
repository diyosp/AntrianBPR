<?php
session_start();

// Optional: require login like other pages
if (!isset($_SESSION['user_id'])) {
  header("Location: ../login.php");
  exit;
}

include "../header.php";
?>

<body class="d-flex flex-column h-100">
  <main class="flex-grow-1">
    <div class="container-fluid px-0 tv-root h-100">
      <div class="row g-0 align-items-stretch h-100 tv-row">
        <!-- Left column: stacked queues -->
  <div class="col-12 col-lg-5 d-grid h-100 left-col">
          <!-- CS Queue -->
          <div class="card border-0 shadow-sm queue-card">
            <div class="card-body p-4">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <h5 class="mb-0">Antrian Customer Service</h5>
                <i class="bi-people text-success fs-4"></i>
              </div>
              <div class="display-1 fw-bold text-center" id="queue-cs">-</div>
            </div>
          </div>
          <!-- Teller Queue -->
          <div class="card border-0 shadow-sm queue-card">
            <div class="card-body p-4">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <h5 class="mb-0">Antrian Teller</h5>
                <i class="bi-mic text-success fs-4"></i>
              </div>
              <div class="display-1 fw-bold text-center" id="queue-teller">-</div>
            </div>
          </div>
          <!-- Kredit Queue -->
          <div class="card border-0 shadow-sm queue-card">
            <div class="card-body p-4">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <h5 class="mb-0">Antrian Admin Kredit</h5>
                <i class="bi-credit-card text-success fs-4"></i>
              </div>
              <div class="display-1 fw-bold text-center" id="queue-kredit">-</div>
            </div>
          </div>
        </div>
        <!-- Right column: video + company info -->
  <div class="col-12 col-lg-7 right-col h-100">
          <!-- Video Placeholder -->
          <div class="card border-0 shadow-sm flex-grow-1 mb-0">
            <div class="card-body p-0 d-flex align-items-center justify-content-center bg-dark text-white video-area" style="min-height: 360px;">
              <?php
                // Auto-detect first video file in common folders
                $videoWebPath = null;
                $candidates = [
                  ['fs' => __DIR__ . '/../assets/video', 'web' => '../assets/video'],
                  ['fs' => __DIR__ . '/../video',        'web' => '../video'],
                  ['fs' => __DIR__ . '/video',           'web' => 'video'],
                ];
                foreach ($candidates as $cand) {
                  if (is_dir($cand['fs'])) {
                    $files = glob($cand['fs'] . '/*.{mp4,webm,ogg}', GLOB_BRACE);
                    if ($files && count($files) > 0) {
                      sort($files);
                      $file = basename($files[0]);
                      $videoWebPath = rtrim($cand['web'], '/') . '/' . $file;
                      break;
                    }
                  }
                }
              ?>
              <?php if ($videoWebPath): ?>
                <div id="videoWrapper" class="position-relative w-100 h-100">
                  <video id="tvVideo" class="w-100 h-100" autoplay muted loop playsinline>
                    <source src="<?= htmlspecialchars($videoWebPath) ?>" type="video/mp4">
                    <source src="<?= htmlspecialchars($videoWebPath) ?>" type="video/webm">
                    <source src="<?= htmlspecialchars($videoWebPath) ?>" type="video/ogg">
                    Browser Anda tidak mendukung pemutaran video.
                  </video>
                  <!-- Persistent control bar -->
                  <div id="tvControls" class="position-absolute bottom-0 start-50 translate-middle-x mb-3 d-flex align-items-center gap-2 bg-dark bg-opacity-50 rounded-pill px-2 py-1 shadow">
                    <button id="playBtn" type="button" class="btn btn-light btn-sm rounded-circle p-2 d-flex align-items-center justify-content-center ctrl-btn" aria-label="Putar/Jeda" title="Putar/Jeda">
                      <i class="bi-pause-fill fs-5"></i>
                    </button>
                    <button id="muteBtn" type="button" class="btn btn-light btn-sm rounded-circle p-2 d-flex align-items-center justify-content-center ctrl-btn" aria-label="Bisu/Suara" title="Bisu/Suara">
                      <i class="bi-volume-mute fs-5"></i>
                    </button>
                    <button id="fsBtn" type="button" class="btn btn-light btn-sm rounded-circle p-2 d-flex align-items-center justify-content-center ctrl-btn" aria-label="Layar Penuh" title="Layar Penuh">
                      <i class="bi-arrows-fullscreen fs-5"></i>
                    </button>
                  </div>
                </div>
              <?php else: ?>
                <div class="text-center p-4">
                  <i class="bi-tv display-1 d-block mb-2"></i>
                  <div class="h4 mb-1">Video Placeholder</div>
                  <div class="text-muted">Letakkan file video di folder <code>assets/video</code> atau <code>video</code>.</div>
                </div>
              <?php endif; ?>
            </div>
          </div>
          <!-- Company Info -->
          <div class="card border-0 shadow-sm">
            <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
              <div>
                <div class="fw-semibold">BPR Sukabumi</div>
                <div class="small text-muted">Layanan cepat, aman, dan terpercaya.</div>
              </div>
              <div class="text-end">
                <div class="small text-muted">Tanggal & Waktu</div>
                <div class="fw-semibold" id="clock">-</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

   <style>
    html, body { height: 100%; width: 100%; }
    body { margin: 0 !important; padding: 0 !important; overflow: hidden; }
    .tv-root { position: fixed; inset: 0; }
    .tv-row { height: 100%; }
    .left-col { grid-template-rows: repeat(3, 1fr); gap: 0; }
    .right-col { display: grid; grid-template-rows: 1fr auto; gap: 0; }
    .left-col .queue-card, .right-col > .card { height: 100%; border-radius: 0; }
    .queue-card .display-1 { font-size: clamp(3rem, 8vw, 6rem); }
    .queue-card h5 { font-weight: 600; }
    .video-area { height: 100%; min-height: 0; }
    /* Show whole video without cropping (no sound bars filled by background) */
    .video-area video, .video-area img { width: 100%; height: 100%; object-fit: contain; background:#fff; display: block; }
  #tvControls { z-index: 6; opacity: 1; transition: opacity .3s ease; }
  #tvControls .ctrl-btn { width: 40px; height: 40px; border-radius: 50%; opacity: 0.95; }
  #tvControls .ctrl-btn:hover { opacity: 1; }
  #tvControls.hide { opacity: 0; pointer-events: none; }
  #videoWrapper.controls-hidden { cursor: none; }
    @media (min-width: 992px) {
      .queue-card .display-1 { font-size: 6rem; }
    }
  </style>

  <script>
    function updateClock() {
      const el = document.getElementById('clock');
      if (!el) return;
      const now = new Date();
      const opts = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
      const dateStr = now.toLocaleDateString('id-ID', opts);
      const timeStr = now.toLocaleTimeString('id-ID', { hour12: false });
      el.textContent = `${dateStr} ${timeStr}`;
    }

    async function fetchQueue(url, targetId) {
      try {
        const cacheBuster = Date.now();
        const res = await fetch(`${url}?_=${cacheBuster}` , {
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          credentials: 'same-origin',
          cache: 'no-store'
        });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const text = await res.text();
        const el = document.getElementById(targetId);
        if (el) el.textContent = text && text.trim() ? text : '-';
      } catch (e) {
        const el = document.getElementById(targetId);
        if (el) el.textContent = '-';
      }
    }

    function pollQueues() {
      fetchQueue('./api/get_last_cs.php', 'queue-cs');
      fetchQueue('./api/get_last_teller.php', 'queue-teller');
      fetchQueue('./api/get_last_kredit.php', 'queue-kredit');
    }

    // Enable audio after user interaction
    document.addEventListener('DOMContentLoaded', () => {
      const v = document.getElementById('tvVideo');
      const playBtn = document.getElementById('playBtn');
      const muteBtn = document.getElementById('muteBtn');
      const fsBtn = document.getElementById('fsBtn');
      const wrapper = document.getElementById('videoWrapper');
      const controls = document.getElementById('tvControls');
      const fsRoot = document.querySelector('.tv-root') || document.documentElement;
      if (!v) return;

      const syncControls = () => {
        if (playBtn) {
          const playing = !v.paused && !v.ended;
          playBtn.innerHTML = playing ? '<i class="bi-pause-fill fs-5"></i>' : '<i class="bi-play-fill fs-5"></i>';
          playBtn.title = playing ? 'Jeda' : 'Putar';
          playBtn.setAttribute('aria-label', playBtn.title);
        }
        if (muteBtn) {
          const muted = v.muted || v.volume === 0;
          muteBtn.innerHTML = muted ? '<i class="bi-volume-mute fs-5"></i>' : '<i class="bi-volume-up fs-5"></i>';
          muteBtn.title = muted ? 'Suara Mati' : 'Suara Menyala';
          muteBtn.setAttribute('aria-label', muteBtn.title);
        }
        if (fsBtn) {
          const inFs = !!document.fullscreenElement;
          fsBtn.innerHTML = inFs ? '<i class="bi-fullscreen-exit fs-5"></i>' : '<i class="bi-arrows-fullscreen fs-5"></i>';
          fsBtn.title = inFs ? 'Keluar Layar Penuh' : 'Layar Penuh';
          fsBtn.setAttribute('aria-label', fsBtn.title);
        }
      };

      // Event handlers
      if (playBtn) {
        playBtn.addEventListener('click', async () => {
          try {
            if (v.paused) {
              await v.play();
            } else {
              v.pause();
            }
          } catch (e) { /* no-op */ }
          syncControls();
          scheduleHide();
        });
      }

      if (muteBtn) {
        muteBtn.addEventListener('click', () => {
          v.muted = !v.muted;
          if (!v.muted && v.volume === 0) v.volume = 1.0;
          // If starting audio, ensure playback continues
          v.play().catch(() => {});
          syncControls();
          scheduleHide();
        });
      }

      const toggleFs = async () => {
        try {
          if (document.fullscreenElement) {
            await document.exitFullscreen();
          } else {
            await fsRoot.requestFullscreen();
          }
        } catch (e) { /* no-op */ }
        syncControls();
      };
      if (fsBtn) {
        fsBtn.addEventListener('click', toggleFs);
        document.addEventListener('fullscreenchange', syncControls);
      }

      // Also allow clicking video to toggle play/pause
      v.addEventListener('click', async () => {
        try {
          if (v.paused) {
            await v.play();
          } else {
            v.pause();
          }
        } catch (e) { /* no-op */ }
        syncControls();
        scheduleHide();
      });

      // Keep controls in sync with video events
      v.addEventListener('play', () => { syncControls(); scheduleHide(); });
      v.addEventListener('pause', () => { syncControls(); showControls(); });
      v.addEventListener('volumechange', syncControls);

      // Auto-hide controls after inactivity when playing
      let hideTimer;
      const hideControls = () => {
        if (!controls || !wrapper) return;
        if (!v.paused && !v.ended) {
          controls.classList.add('hide');
          wrapper.classList.add('controls-hidden');
        }
      };
      const showControls = () => {
        if (!controls || !wrapper) return;
        controls.classList.remove('hide');
        wrapper.classList.remove('controls-hidden');
      };
      const scheduleHide = () => {
        if (!controls || !wrapper) return;
        clearTimeout(hideTimer);
        if (!v.paused && !v.ended) hideTimer = setTimeout(hideControls, 3000);
      };
      if (wrapper) {
        ['mousemove','touchstart','touchmove'].forEach(evt => wrapper.addEventListener(evt, () => { showControls(); scheduleHide(); }));
      }

      // Initial sync and hide schedule
      syncControls();
      scheduleHide();
    });

    // initial
    updateClock();
    pollQueues();
    // intervals
    setInterval(updateClock, 1000);
    setInterval(pollQueues, 3000);
  </script>
