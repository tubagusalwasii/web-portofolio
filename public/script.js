// ===== Inisialisasi AOS (Animate On Scroll) =====
AOS.init({
  duration: 800,
  once: true, // Animasi hanya berjalan sekali — lebih ringan & profesional
  offset: 120,
});

// ===== Efek Mengetik di Hero Section (Diinisialisasi secara dinamis di welcome.blade.php) =====

// ===== Interaksi Navigasi Mobile =====
const navToggle = document.querySelector('.nav-toggle');
const navMenu = document.querySelector('.primary-nav ul');
navToggle?.addEventListener('click', () => {
  const isExpanded = navToggle.getAttribute('aria-expanded') === 'true';
  navToggle.setAttribute('aria-expanded', !isExpanded);
  navMenu.classList.toggle('open');
  
  // Kunci scroll body saat menu terbuka
  document.body.classList.toggle('no-scroll');
});

// Menutup menu saat link diklik (Mobile)
const allNavLinks = document.querySelectorAll('.primary-nav a, .brand');
allNavLinks.forEach(link => {
  link.addEventListener('click', () => {
    if (navMenu.classList.contains('open')) {
      navMenu.classList.remove('open');
      navToggle.setAttribute('aria-expanded', 'false');
      document.body.classList.remove('no-scroll');
    }
  });
});

// ===== Sorot Link Aktif Saat Scroll (Scrollspy) - DIPERBAIKI =====
const sections = document.querySelectorAll('#hero, #about, #experience, #portfolio, #contact');
const navLinks = document.querySelectorAll('.primary-nav a');

const observerOptions = {
  root: null, // Menggunakan viewport sebagai root
  rootMargin: "0px",
  threshold: 0.3 // Memicu saat 30% dari section terlihat
};

const sectionObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      const id = entry.target.getAttribute('id');
      
      navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === `#${id}`) {
          link.classList.add('active');
        }
      });
    }
  });
}, observerOptions);

sections.forEach(section => {
  sectionObserver.observe(section);
});


// ===== Logika untuk Portfolio Tabs =====
const tabs = document.querySelectorAll('.tab-button');
const panels = document.querySelectorAll('.tab-panel');

tabs.forEach(tab => {
  tab.addEventListener('click', () => {
    const targetPanelId = tab.dataset.tab;
    const targetPanel = document.getElementById(`tab-${targetPanelId}`);
    
    tabs.forEach(t => t.classList.remove('active'));
    panels.forEach(p => p.classList.remove('active'));
    
    tab.classList.add('active');
    if (targetPanel) {
      targetPanel.classList.add('active');
      // Refresh AOS untuk animasi di dalam tab baru
      setTimeout(() => {
        AOS.refresh();
      }, 100);
    }
  });
});

// ===== Fungsionalitas Lightbox & Footer =====
const lightbox = document.getElementById("lightbox");
const lightboxImg = document.getElementById("lightbox-img");
const closeBtn = document.querySelector(".close");
const certImgs = document.querySelectorAll(".cert-img");

certImgs.forEach(img => {
  img.addEventListener("click", () => {
    lightbox.style.display = "block";
    lightboxImg.src = img.src;
  });
});

const closeLightbox = () => { 
    if (lightbox) lightbox.style.display = "none"; 
};

closeBtn?.addEventListener("click", closeLightbox);

window.addEventListener("click", (e) => { 
    if (e.target === lightbox) {
        closeLightbox(); 
    }
});

const yearEl = document.getElementById('year');
if (yearEl) yearEl.textContent = new Date().getFullYear();

// ===== Logika untuk Tombol "Show More" Sertifikat - DIPERBAIKI =====
const showMoreBtn = document.getElementById('show-more-certs-btn');
const hiddenCertItems = document.querySelectorAll('.certificate-item.hidden-item');

showMoreBtn?.addEventListener('click', () => {
  const isShowingMore = showMoreBtn.textContent === 'Tampilkan Lebih Sedikit'; // Cek state saat ini

  if (!isShowingMore) {
    // Menampilkan semua item tersembunyi
    hiddenCertItems.forEach(item => {
      item.classList.remove('hidden-item');
      item.classList.add('visible-item'); // Tambahkan kelas untuk animasi
    });
    showMoreBtn.textContent = 'Tampilkan Lebih Sedikit';
  } else {
    // Sembunyikan item terlebih dahulu
    hiddenCertItems.forEach(item => {
      item.classList.remove('visible-item');
      item.classList.add('hidden-item');
    });
    showMoreBtn.textContent = 'Tampilkan Lebih Banyak';

    // Langsung pindahkan scroll ke atas seksi portfolio tanpa animasi
    const portfolioSection = document.getElementById('portfolio');
    if (portfolioSection) {
      const y = portfolioSection.getBoundingClientRect().top + window.scrollY - 80;
      window.scrollTo({ top: y, behavior: 'auto' });
    }
    
    // Paksa AOS untuk menghitung ulang koordinat elemen setelah DOM berubah drastis
    setTimeout(() => AOS.refresh(), 100);
  }
});

// ===== Logika untuk Tombol "Show More" Proyek =====
const showMoreProjectsBtn = document.getElementById('show-more-projects-btn');
const hiddenProjectItems = document.querySelectorAll('.card.hidden-project');

showMoreProjectsBtn?.addEventListener('click', () => {
  const isShowingMore = showMoreProjectsBtn.textContent === 'Tampilkan Lebih Sedikit';

  if (!isShowingMore) {
    hiddenProjectItems.forEach(item => {
      item.classList.remove('hidden-project');
      item.classList.add('visible-project');
    });
    showMoreProjectsBtn.textContent = 'Tampilkan Lebih Sedikit';
    setTimeout(() => AOS.refresh(), 100);
  } else {
    // Sembunyikan item terlebih dahulu
    hiddenProjectItems.forEach(item => {
      item.classList.remove('visible-project');
      item.classList.add('hidden-project');
    });
    showMoreProjectsBtn.textContent = 'Lihat Semua Proyek';

    // Langsung pindahkan scroll ke atas seksi portfolio tanpa animasi (mencegah bug layar kosong)
    const portfolioSection = document.getElementById('portfolio');
    if (portfolioSection) {
      const y = portfolioSection.getBoundingClientRect().top + window.scrollY - 80; // 80px offset untuk header
      window.scrollTo({ top: y, behavior: 'auto' });
    }

    // Paksa AOS untuk menghitung ulang koordinat setelah tinggi halaman menyusut
    setTimeout(() => AOS.refresh(), 100);
  }
});

// ===== Logika untuk Formulir Kontak (Backend Laravel) =====
const contactForm = document.getElementById('contact-form');
if (contactForm) {
    const submitBtn = contactForm.querySelector('button[type="submit"]');

    contactForm.addEventListener('submit', function(event) {
      event.preventDefault(); // Mencegah form refresh halaman

      if (submitBtn) {
        submitBtn.textContent = 'Mengirim...';
        submitBtn.disabled = true;
      }

      // Ambil data dari form
      const formData = new FormData(this);
      
      // Ambil CSRF token dari elemen form
      const csrfToken = this.querySelector('input[name="_token"]')?.value;

      fetch('/send-message', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json'
        },
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          if (submitBtn) {
            submitBtn.textContent = 'Pesan Terkirim!';
            submitBtn.style.backgroundColor = '#10b981'; // Hijau sukses
          }
          contactForm.reset();
        } else {
          throw new Error(data.error || 'Terjadi kesalahan');
        }
      })
      .catch((err) => {
        if (submitBtn) {
          submitBtn.textContent = 'Gagal Mengirim';
          submitBtn.style.backgroundColor = '#ef4444'; // Merah error
        }
        console.error('Error:', err);
      })
      .finally(() => {
        setTimeout(() => {
          if (submitBtn) {
            submitBtn.textContent = 'Kirim Pesan';
            submitBtn.disabled = false;
            submitBtn.style.backgroundColor = '';
          }
        }, 3000);
      });
    });
}


// ================================================================
// ===== TURNTABLE AUDIO PLAYER — Hero Interactive Vinyl Deck =====
// ================================================================
(function () {
  'use strict';

  // --- State ---
  let isPlaying = false;

  // --- DOM References ---
  const audio        = document.getElementById('bg-audio');
  const vinylBtn     = document.getElementById('vinyl-play-btn');
  const tonearm      = document.getElementById('tonearm');
  const reelLeft     = document.getElementById('reel-left');
  const reelRight    = document.getElementById('reel-right');
  const statusDot    = document.getElementById('status-dot');
  const statusLabel  = document.getElementById('status-label');
  const iconPlay     = document.getElementById('icon-play');
  const iconPause    = document.getElementById('icon-pause');
  const glow         = document.getElementById('vpf-glow'); // Cache sekali di awal

  // Guard: kalau elemen tidak ada (halaman lain), hentikan
  if (!audio || !vinylBtn) return;

  /**
   * Update semua elemen visual berdasarkan state isPlaying.
   */
  function updateUI() {
    if (isPlaying) {
      // --- Vinyl: tambahkan kelas 'playing' untuk rotasi ---
      vinylBtn.classList.add('playing');

      // --- Tonearm: geser ke atas vinyl ---
      tonearm.classList.add('on-vinyl');

      // --- Cassette Reels: putar ---
      reelLeft.classList.add('spinning');
      reelRight.classList.add('spinning');

      // --- Ambient Glow: nyala ---
      if (glow) glow.classList.add('active');

      // --- Status Bar ---
      statusDot.classList.add('active');
      statusLabel.textContent = 'NOW PLAYING ♪ Get You — Daniel Caesar';

      // --- Icon: tampilkan pause ---
      iconPlay.style.display  = 'none';
      iconPause.style.display = 'block';
    } else {
      // --- Vinyl: hentikan rotasi ---
      vinylBtn.classList.remove('playing');

      // --- Tonearm: kembalikan ke posisi rest ---
      tonearm.classList.remove('on-vinyl');

      // --- Cassette Reels: berhenti ---
      reelLeft.classList.remove('spinning');
      reelRight.classList.remove('spinning');

      // --- Ambient Glow: mati ---
      if (glow) glow.classList.remove('active');

      // --- Status Bar ---
      statusDot.classList.remove('active');
      statusLabel.textContent = 'STOPPED — Klik vinyl untuk play';

      // --- Icon: tampilkan play ---
      iconPlay.style.display  = 'block';
      iconPause.style.display = 'none';
    }
  }

  /**
   * Toggle play / pause audio & semua animasi.
   */
  function togglePlay() {
    if (!isPlaying) {
      // Coba play audio; tangani autoplay policy browser
      const playPromise = audio.play();
      if (playPromise !== undefined) {
        playPromise
          .then(() => {
            isPlaying = true;
            updateUI();
          })
          .catch((err) => {
            // Autoplay diblokir browser — tampilkan pesan di status bar
            console.warn('Autoplay diblokir:', err);
            statusLabel.textContent = 'Interaksi diperlukan — coba lagi';
          });
      } else {
        isPlaying = true;
        updateUI();
      }
    } else {
      audio.pause();
      isPlaying = false;
      updateUI();
    }
  }

  // --- Event: Klik pada vinyl record ---
  vinylBtn.addEventListener('click', togglePlay);

  // --- Event: Keyboard accessibility (Space / Enter) ---
  vinylBtn.addEventListener('keydown', (e) => {
    if (e.key === ' ' || e.key === 'Enter') {
      e.preventDefault();
      togglePlay();
    }
  });

  // --- Event: Sinkronisasi saat audio berakhir / error ---
  audio.addEventListener('pause', () => {
    // Dipanggil juga saat audio selesai (loop=false)
    if (!audio.loop && audio.ended) {
      isPlaying = false;
      updateUI();
    }
  });
  audio.addEventListener('error', () => {
    isPlaying = false;
    updateUI();
    statusLabel.textContent = 'File audio tidak ditemukan';
  });

  // --- Inisialisasi awal ---
  updateUI();

})();
