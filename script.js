// ===== Inisialisasi AOS (Animate On Scroll) =====
AOS.init({
  duration: 800,
  once: false, // Animasi akan berjalan setiap kali di-scroll
  offset: 120,
});

// ===== Efek Mengetik di Hero Section (Typed.js) =====
const typed = new Typed('#typing-effect', {
  strings: ["UI/UX Designer", "Mobile Developer", "Machine Learning Enthusiast"],
  typeSpeed: 50,
  backSpeed: 30,
  backDelay: 1500,
  loop: true,
});

// ===== Interaksi Navigasi Mobile =====
const navToggle = document.querySelector('.nav-toggle');
const navMenu = document.querySelector('.primary-nav ul');
navToggle?.addEventListener('click', () => {
  const isExpanded = navToggle.getAttribute('aria-expanded') === 'true';
  navToggle.setAttribute('aria-expanded', !isExpanded);
  navMenu.classList.toggle('open');
});

// ===== Sorot Link Aktif Saat Scroll (Scrollspy) - DIPERBAIKI =====
const sections = document.querySelectorAll('#hero, #about, #portfolio, #contact');
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
    lightbox.style.display = "none"; 
};

closeBtn?.addEventListener("click", closeLightbox);

window.addEventListener("click", (e) => { 
    if (e.target === lightbox) {
        closeLightbox(); 
    }
});

document.getElementById('year').textContent = new Date().getFullYear();

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
    // Menyembunyikan item kembali
    hiddenCertItems.forEach(item => {
      item.classList.remove('visible-item');
      item.classList.add('hidden-item'); // Tambahkan kelas untuk menyembunyikan
    });
    showMoreBtn.textContent = 'Tampilkan Lebih Banyak';
  }
});

// ===== Logika untuk Formulir Kontak dengan EmailJS =====
const contactForm = document.getElementById('contact-form');
const submitBtn = contactForm.querySelector('button[type="submit"]');

contactForm?.addEventListener('submit', function(event) {
  event.preventDefault(); // Mencegah form refresh halaman

  // Ganti dengan ID dan Key Anda dari EmailJS
  const serviceID = 'service_y10xhjb';
  const templateID = 'template_vpz37de';
  const publicKey = 'R0_eQUjSrQlI35F3R';

  // Mengubah teks tombol untuk feedback
  submitBtn.textContent = 'Mengirim...';

  emailjs.sendForm(serviceID, templateID, this, publicKey)
    .then(() => {
      // Jika berhasil
      submitBtn.textContent = 'Pesan Terkirim!';
      contactForm.reset(); // Mengosongkan form
      setTimeout(() => {
        submitBtn.textContent = 'Kirim Pesan'; // Kembalikan teks tombol setelah beberapa detik
      }, 3000);
    }, (err) => {
      // Jika gagal
      submitBtn.textContent = 'Gagal Mengirim';
      alert(JSON.stringify(err));
      setTimeout(() => {
        submitBtn.textContent = 'Kirim Pesan';
      }, 3000);
    });
});