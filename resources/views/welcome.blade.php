@php
/**
 * Resolve a storage URL for Cloudinary-hosted files.
 * All files are uploaded as 'image' type to avoid Cloudinary's
 * raw file access restrictions (401 on free plan).
 */
function safeStorageUrl(?string $path, string $fallbackAsset = '', bool $download = false, string $downloadName = ''): string {
    if (!$path) {
        return $fallbackAsset ? asset($fallbackAsset) : '';
    }
    if (str_starts_with($path, 'assets/')) {
        return asset($path);
    }
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        if ($download && str_contains($path, 'res.cloudinary.com') && !str_contains($path, 'fl_attachment')) {
            $flag = 'fl_attachment' . ($downloadName ? ':' . str_replace([' ', "'"], ['_', ''], $downloadName) : '');
            return str_replace('/upload/', "/upload/{$flag}/", $path);
        }
        return $path;
    }
    // Prioritaskan disk lokal (public) jika file benar-benar ada di lokal (berguna untuk testing lokal)
    try {
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        if ($disk->exists($path)) {
            return $disk->url($path);
        }
    } catch (\Throwable $e) {
        // Abaikan error disk lokal
    }

    // Jika tidak ada di lokal, dan Cloudinary dikonfigurasi, gunakan Cloudinary
    $cloudName = config('filesystems.disks.cloudinary.cloud');
    if ($cloudName && config('filesystems.default') === 'cloudinary') {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico'];
        $videoExts = ['mp4', 'webm', 'mov', 'avi'];
        
        if (in_array($ext, $imageExts)) {
            $type = 'image';
        } elseif (in_array($ext, $videoExts)) {
            $type = 'video';
        } else {
            $type = 'raw';
        }

        $transform = '';
        if ($download) {
            $transform = 'fl_attachment' . ($downloadName ? ':' . str_replace([' ', "'"], ['_', ''], $downloadName) : '') . '/';
        }
        return "https://res.cloudinary.com/{$cloudName}/{$type}/upload/{$transform}{$path}";
    }
    
    // Jika masih gagal, kembalikan fallback
    return $fallbackAsset ? asset($fallbackAsset) : '';
}
@endphp
<!doctype html>
<html lang="id">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portofolio — {{ $settings->hero_title ?? "Tubagus Alwasi'i" }}</title>
    <meta name="description" content="Portofolio pribadi {{ $settings->hero_title ?? "Tubagus Alwasi'i" }}, seorang mahasiswa Teknik Informatika dengan minat pada UI/UX, Mobile Development, dan AI.">
    <meta property="og:title" content="Portofolio — {{ $settings->hero_title ?? "Tubagus Alwasi'i" }}">
    <meta property="og:description" content="Seorang mahasiswa Teknik Informatika dengan minat pada UI/UX, Mobile Development, dan AI.">
    <meta property="og:type" content="website">
    <meta name="theme-color" content="#1a1a2e">
    
    <link rel="icon" href="{{ ($settings->site_logo ?? null) ? safeStorageUrl($settings->site_logo) : asset('assets/favicon.svg') }}" type="image/svg+xml">
    
    {{-- Preload gambar profil di hero (LCP - above the fold) --}}
    @php $heroImg = $settings->hero_image ? safeStorageUrl($settings->hero_image, 'assets/profil2.jpeg') : asset('assets/profil2.jpeg'); @endphp
    <link rel="preload" as="image" href="{{ $heroImg }}">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css">
    <link rel="stylesheet" href="{{ asset('style.css') }}?v={{ config('app.version', '1.0') }}">
  </head>
  <body>
    <div class="bg-glow"></div>
    <header class="site-header">
      <div class="container nav">
        <a href="#hero" class="brand" aria-label="Beranda">
          <span>{{ $settings->hero_title ?? "Tubagus Alwasi'i" }}</span>
        </a>
        <button class="nav-toggle" aria-label="Buka navigasi" aria-expanded="false">
          <span></span>
          <span></span>
          <span></span>
        </button>
        <nav class="primary-nav" aria-label="Navigasi utama">
          <ul>
            <li><a href="#hero">Home</a></li>
            <li><a href="#about">About</a></li>
            <li><a href="#experience">Experience</a></li>
            <li><a href="#portfolio">Portfolio</a></li>
            <li><a href="#contact">Contact</a></li>
          </ul>
        </nav>
      </div>
    </header>

    <main>
      <section id="hero" class="section hero">
        <!-- Seluruh hero adalah permukaan meja kayu -->
        <div class="hero-desk-scene"
             style="background-image: linear-gradient(rgba(20,10,4,0.55), rgba(20,10,4,0.55)), url('{{ asset('assets/wood_desk.png') }}');"
        >
          <!-- Ambient overhead warm light -->
          <div class="hero-overhead-light" aria-hidden="true"></div>

          <div class="container hero-grid">
          <div class="hero-text" data-aos="fade-right">
            <p class="eyebrow">Hello, I'm</p>
            <h1>{{ $settings->hero_title ?? "Tubagus Alwasi'i" }}</h1>
            <p class="subtitle">
              <span id="typing-effect"></span>
            </p>
            <div class="hero-cta">
              <a class="button primary" href="/download-cv" download="TUBAGUS ALWASI'I CV.pdf">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 5px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                Download CV
              </a>
              <a class="button secondary" href="#portfolio">Lihat Portfolio</a>
            </div>
          </div>
          <!-- ====== Turntable Hero Media ====== -->
          <div class="turntable-deck" data-aos="fade-left" data-aos-delay="200" id="turntable-deck">
            
            <!-- Karpet/Mat gelap bawah deck -->
            <div class="desk-mat" aria-hidden="true"></div>
            <!-- Cahaya dari atas (lamp glow dari atas deck, menerangi cassette) -->
            <div class="deck-top-light" aria-hidden="true"></div>

            <!-- Kaset Pita (Cassette) Dekorasi Atas -->
            <div class="cassette-deco" id="cassette-deco" aria-hidden="true">
              <div class="cassette-body">
                <div class="cassette-window">
                  <div class="cassette-reel left" id="reel-left"></div>
                  <div class="cassette-tape-bridge"></div>
                  <div class="cassette-reel right" id="reel-right"></div>
                </div>
                <div class="cassette-label" style="justify-content: center;">
                  <span class="cassette-title">PORTFOLIO MIX</span>
                </div>
              </div>
            </div>

            <!-- Deck / Platter Area -->
            <div class="turntable-platter-area">

              <!-- Piringan Hitam (Vinyl Record) — berputar, foto sudah dipisah -->
              <button
                class="vinyl-record"
                id="vinyl-play-btn"
                aria-label="Putar / Jeda musik"
                title="Klik untuk memutar musik"
              >
                <!-- Groove rings + Label area vinyl (SVG) -->
                <svg class="vinyl-grooves" viewBox="0 0 300 300" aria-hidden="true">
                  <defs>
                    <!-- Gradient warna label vinyl -->
                    <radialGradient id="labelGrad" cx="38%" cy="32%" r="65%">
                      <stop offset="0%"   stop-color="#d49060"/>
                      <stop offset="45%"  stop-color="#8b4f2a"/>
                      <stop offset="100%" stop-color="#5c2e12"/>
                    </radialGradient>
                    <!-- Shine reflection di label -->
                    <radialGradient id="shineGrad" cx="30%" cy="22%" r="55%">
                      <stop offset="0%"   stop-color="rgba(255,255,255,0.22)"/>
                      <stop offset="70%"  stop-color="rgba(255,255,255,0.04)"/>
                      <stop offset="100%" stop-color="rgba(255,255,255,0)"/>
                    </radialGradient>
                  </defs>
                  <!-- Outer groove bands (lebih banyak = lebih realistis) -->
                  <circle cx="150" cy="150" r="140" fill="none" stroke="rgba(255,255,255,0.07)" stroke-width="1.2"/>
                  <circle cx="150" cy="150" r="133" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/>
                  <circle cx="150" cy="150" r="126" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="0.9"/>
                  <circle cx="150" cy="150" r="119" fill="none" stroke="rgba(255,255,255,0.04)" stroke-width="0.8"/>
                  <circle cx="150" cy="150" r="112" fill="none" stroke="rgba(255,255,255,0.04)" stroke-width="0.8"/>
                  <circle cx="150" cy="150" r="105" fill="none" stroke="rgba(255,255,255,0.04)" stroke-width="0.8"/>
                  <circle cx="150" cy="150" r="98"  fill="none" stroke="rgba(255,255,255,0.04)" stroke-width="0.8"/>
                  <!-- Area label (warm amber/brown) -->
                  <circle cx="150" cy="150" r="90" fill="url(#labelGrad)"/>
                  <!-- Border luar label -->
                  <circle cx="150" cy="150" r="90" fill="none" stroke="rgba(0,0,0,0.55)" stroke-width="2.5"/>
                  <!-- Rim detail dalam label -->
                  <circle cx="150" cy="150" r="87" fill="none" stroke="rgba(255,255,255,0.09)" stroke-width="0.7"/>
                  <circle cx="150" cy="150" r="84" fill="none" stroke="rgba(0,0,0,0.2)" stroke-width="0.5"/>
                  <!-- Shine overlay di label -->
                  <circle cx="150" cy="150" r="90" fill="url(#shineGrad)"/>
                </svg>
                <!-- Icon play/pause overlay -->
                <div class="vinyl-play-icon" id="vinyl-play-icon" aria-hidden="true">
                  <svg id="icon-play" xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
                    <polygon points="5 3 19 12 5 21 5 3"/>
                  </svg>
                  <svg id="icon-pause" xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="currentColor" style="display:none;">
                    <rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/>
                  </svg>
                </div>
              </button>

              <!-- ========================================
                   FOTO PROFIL — STATIC, tidak ikut berputar
                   ======================================== -->
              <div class="vinyl-photo-frame" id="vinyl-photo-frame">
                <!-- Ambient glow (intensif saat playing) -->
                <div class="vpf-glow" id="vpf-glow"></div>
                <!-- Outer metallic ring -->
                <div class="vpf-outer-ring"></div>
                <!-- Foto profil -->
                <img
                  src="{{ $heroImg }}"
                  alt="Foto profil {{ $settings->hero_title ?? "Tubagus Alwasi'i" }}"
                  class="vinyl-photo"
                  width="200"
                  height="200"
                />
                <!-- Glass shine overlay (efek 3D kaca) -->
                <div class="vpf-shine"></div>
              </div>

              <!-- Tonearm (Lengan Pemutar) -->
              <div class="tonearm-container" id="tonearm-container" aria-hidden="true">
                <div class="tonearm-pivot"></div>
                <div class="tonearm-arm" id="tonearm">
                  <div class="tonearm-headshell"></div>
                  <div class="tonearm-cartridge"></div>
                </div>
              </div>

            </div>

            <!-- Status bar bawah -->
            <div class="turntable-status">
              <span class="status-dot" id="status-dot"></span>
              <span class="status-label" id="status-label">STOPPED — Klik vinyl untuk play</span>
              <span class="status-rpm">33 <small>RPM</small></span>
            </div>

          </div><!-- /.turntable-deck -->
          </div><!-- /.hero-grid -->
        </div><!-- /.hero-desk-scene -->
      </section>

      <section id="about" class="section">
        <div class="section-header" data-aos="fade-up">
          <h2>Tentang Saya</h2>
          <p class="section-subtitle">♪ Perjalanan dan latar belakang saya ♪</p>
        </div>
        <div class="container two-col">
          <div data-aos="fade-right">
            <p style="text-align: justify;">{{ $settings->about_description ?? "Data tentang saya belum tersedia." }}</p>
            <div class="badges" style="margin-top: 1rem;">
              @php
                $badges = $settings->about_badges ?? ["Problem Solver", "Creative Thinker", "Team Player"];
                if (is_string($badges)) $badges = json_decode($badges, true) ?? [];
              @endphp
              @foreach($badges as $badge)
                <span class="badge">{{ $badge }}</span>
              @endforeach
            </div>
          </div>
          <div class="stats-grid" data-aos="fade-left" data-aos-delay="200">
            <a href="#portfolio" class="stat-card">
              <div class="stat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
              </div>
              <div class="stat-info">
                <span class="num">{{ $projects->count() }}</span>
                <h3>Proyek Selesai</h3>
                <p>Solusi digital inovatif yang telah dibuat</p>
              </div>
            </a>

            <a href="#portfolio" class="stat-card">
              <div class="stat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 15l5-5-5-5"></path><path d="M7 10h10"></path><path d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18z"></path></svg>
              </div>
              <div class="stat-info">
                <span class="num">{{ $certificates->count() }}</span>
                <h3>Sertifikat</h3>
                <p>Validasi keahlian profesional</p>
              </div>
            </a>

            <a href="#portfolio" class="stat-card">
              <div class="stat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
              </div>
              <div class="stat-info">
                <span class="num">1</span>
                <h3>Tahun Pengalaman</h3>
                <p>Perjalanan pembelajaran berkelanjutan</p>
              </div>
            </a>
          </div>
        </div>
      </section>
      
      <section id="experience" class="section">
        <div class="section-header" data-aos="fade-up">
          <h2>Pengalaman</h2>
          <p class="section-subtitle">♫ Perjalanan profesional dan pendidikan saya ♫</p>
        </div>
        <div class="container">
          <div class="timeline">
            @forelse($experiences as $index => $exp)
            <div class="timeline-item {{ $index % 2 == 0 ? 'left' : 'right' }}" data-aos="{{ $index % 2 == 0 ? 'fade-right' : 'fade-left' }}">
              <div class="timeline-dot">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="12" cy="12" r="10"></circle>
                  <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
              </div>
              <div class="timeline-content">
                <div class="timeline-header">
                  <span class="timeline-date">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    {{ $exp->start_date->format('M Y') }} — {{ $exp->is_current ? 'Sekarang' : ($exp->end_date ? $exp->end_date->format('M Y') : '') }}
                  </span>
                  @if($exp->is_current)
                  <span class="timeline-badge active">Aktif</span>
                  @endif
                </div>
                <h3>{{ $exp->title }}</h3>
                <h4>
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                  {{ $exp->company }} {{ $exp->location ? '• ' . $exp->location : '' }}
                </h4>
                <p>{{ $exp->description }}</p>
              </div>
            </div>
            @empty
            <p style="text-align: center; opacity: 0.7;">Data pengalaman belum tersedia.</p>
            @endforelse
          </div>
        </div>
      </section>

      <section id="portfolio" class="section">
        <div class="section-header" data-aos="fade-up">
          <h2>Portfolio Showcase</h2>
          <p class="section-subtitle">♪ Jelajahi perjalanan saya melalui proyek, sertifikasi, dan keahlian teknis ♪</p>
        </div>
      
        <div class="container" data-aos="fade-up" data-aos-delay="100">
         <div class="portfolio-tabs">
          <button class="tab-button active" data-tab="projects">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            Projects
          </button>
          <button class="tab-button" data-tab="certificates">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 10.5l-4-4-4 4"></path><path d="M20 18.5l-4-4-4 4"></path><path d="M8 6.5l4 4 4-4"></path><path d="M12 14.5l4 4 4-4"></path></svg>
            Certificates
          </button>
          <button class="tab-button" data-tab="skills">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v2"></path><path d="M12 20v2"></path><path d="m4.93 4.93 1.41 1.41"></path><path d="m17.66 17.66 1.41 1.41"></path><path d="M2 12h2"></path><path d="M20 12h2"></path><path d="m4.93 19.07 1.41-1.41"></path><path d="m17.66 6.34 1.41-1.41"></path><circle cx="12" cy="12" r="4"></circle><path d="M12 12a2.95 2.95 0 0 1-2.95-2.95c0-1.63 1.32-2.95 2.95-2.95s2.95 1.32 2.95 2.95A2.95 2.95 0 0 1 12 12z"></path></svg>
            Skills
          </button>
        </div>
          <div class="tab-content">
            <div id="tab-projects" class="tab-panel active">
              <div class="grid cards">
                @foreach($projects as $index => $project)
                <article class="card {{ $index >= 3 ? 'hidden-project' : '' }}" data-aos="fade-up" style="cursor: pointer;" onclick="openProjectPopup({{ $project->id }})">
                  <div class="card-media swiper card-swiper-{{ $project->id }}">
                    <div class="swiper-wrapper">
                      @php
                        $images = $project->images;
                        if (empty($images)) $images = [''];
                      @endphp
                      @foreach($images as $img)
                        <div class="swiper-slide">
                          <div style="background-image: url('{{ $img ? safeStorageUrl($img) : '' }}'); background-size: cover; background-position: center; width: 100%; height: 100%;"></div>
                        </div>
                      @endforeach
                    </div>
                    @if(count($images) > 1)
                      <div class="swiper-pagination"></div>
                    @endif
                  </div>
                  <div class="card-body">
                    <h3>{{ $project->name }}</h3>
                    @if(strlen($project->description) > 100)
                      <p>
                        {{ Str::limit($project->description, 100, '') }}...
                        <span style="color: var(--brand); font-weight: 500; cursor: pointer; text-decoration: underline; margin-left: 2px;" onclick="event.stopPropagation(); openProjectPopup({{ $project->id }});">Lihat Selengkapnya</span>
                      </p>
                    @else
                      <p>{{ $project->description }}</p>
                    @endif
                    <div class="tags">
                      <span>{{ $project->category->name }}</span>
                    </div>
                    <div class="card-actions">
                      <button class="button dark-ghost small" onclick="event.stopPropagation(); openProjectPopup({{ $project->id }});">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6"></path><path d="M9 21H3v-6"></path><path d="M21 3l-7 7"></path><path d="M3 21l7-7"></path></svg>
                        Lihat Detail
                      </button> 
                    </div>
                  </div>
                </article>
                @endforeach
              </div>

              @if($projects->count() > 3)
              <div class="show-more-container">
                <button id="show-more-projects-btn" class="button secondary">Lihat Semua Proyek</button>
              </div>
              @endif
            </div>
            <div id="tab-certificates" class="tab-panel">
            <div class="certificates-grid">
              @foreach($certificates as $index => $cert)
              <div class="certificate-item {{ $index >= 6 ? 'hidden-item' : '' }}" data-aos="fade-up">
                <div class="cert-img-container">
                  <img src="{{ $cert->image ? safeStorageUrl($cert->image) : '' }}" alt="{{ $cert->title }}" class="cert-img" loading="lazy" width="400" height="280">
                </div>
                <div class="certificate-body">
                  <h4>{{ $cert->title }}</h4>
                </div>
              </div>
              @endforeach
            </div>

            @if($certificates->count() > 6)
            <div class="show-more-container">
              <button id="show-more-certs-btn" class="button secondary">Tampilkan Lebih Banyak</button>
            </div>
            @endif
          </div>
          <div id="tab-skills" class="tab-panel">
            <div class="skills-knob-grid">
              @forelse($skills as $index => $skill)
                @php
                  // Rotasi berdasarkan level: Dasar (-45deg), Menengah (45deg), Ahli (135deg)
                  $rotation = -45; // Dasar
                  if ($skill->level == 'Menengah') $rotation = 45;
                  if ($skill->level == 'Ahli') $rotation = 135;
                @endphp
                <div class="skill-knob-wrapper" data-aos="fade-up" data-aos-delay="{{ ($index % 5) * 50 }}">
                  <div class="vintage-knob level-{{ strtolower($skill->level) }}">
                    <div class="knob-dial" style="transform: rotate({{ $rotation }}deg);">
                      <div class="knob-indicator"></div>
                    </div>
                    <div class="knob-center">
                      @if($skill->icon_url)
                        <img src="{{ safeStorageUrl($skill->icon_url) }}" alt="{{ $skill->name }}" loading="lazy" width="40" height="40">
                      @else
                        <span>{{ substr($skill->name, 0, 2) }}</span>
                      @endif
                    </div>
                  </div>
                  <div class="skill-info">
                    <span class="skill-name">{{ $skill->name }}</span>
                    <span class="skill-level">{{ $skill->level }}</span>
                  </div>
                </div>
              @empty
                <p style="text-align: center; opacity: 0.7; grid-column: 1 / -1; width: 100%;">Belum ada skill yang ditambahkan.</p>
              @endforelse
            </div>
          </div>
          </div>
        </div>
      </section>
      <section id="contact" class="section">
      <div class="section-header" data-aos="fade-up" data-aos-offset="0">
        <h2>Kontak</h2>
        <p class="section-subtitle">♫ Mari terhubung dan berkolaborasi ♫</p>
      </div>
      <div class="container contact-wrapper">
        <!-- Sisi Kiri: Info & Dekorasi -->
        <div class="contact-info" data-aos="fade-right" data-aos-offset="0">
          <div class="contact-vinyl-deco">
            <svg viewBox="0 0 200 200" width="120" height="120">
              <circle cx="100" cy="100" r="95" fill="none" stroke="var(--brand)" stroke-width="1" opacity="0.3"/>
              <circle cx="100" cy="100" r="80" fill="none" stroke="var(--brand)" stroke-width="0.5" opacity="0.2"/>
              <circle cx="100" cy="100" r="65" fill="none" stroke="var(--brand)" stroke-width="0.5" opacity="0.15"/>
              <circle cx="100" cy="100" r="50" fill="none" stroke="var(--brand)" stroke-width="0.5" opacity="0.1"/>
              <circle cx="100" cy="100" r="20" fill="var(--brand)" opacity="0.15"/>
              <circle cx="100" cy="100" r="8" fill="var(--brand)" opacity="0.4"/>
              <circle cx="100" cy="100" r="3" fill="var(--brand)"/>
            </svg>
          </div>
          <h3 class="contact-heading">Punya ide atau proyek? <br>Ayo ngobrol! ☕</h3>
          <p class="contact-desc">Saya selalu terbuka untuk diskusi tentang proyek baru, ide kreatif, atau kesempatan kolaborasi.</p>
          
          <div class="contact-cards">
            <a href="mailto:tubagusalwasiii@gmail.com" class="contact-card" aria-label="Kirim email">
              <div class="contact-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
              </div>
              <div>
                <span class="contact-card-label">Email</span>
                <span class="contact-card-value">tubagusalwasiii@gmail.com</span>
              </div>
            </a>
          </div>

          <div class="contact-social-links">
            <a href="https://www.linkedin.com/in/tubagus-alwasi-i-727200295/" target="_blank" rel="noreferrer" class="social-link" aria-label="LinkedIn">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 110-4.125 2.062 2.062 0 010 4.125zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.225 0z"/></svg>
              <span>LinkedIn</span>
            </a>
            <a href="https://github.com/tubagusalwasii" target="_blank" rel="noreferrer" class="social-link" aria-label="GitHub">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
              <span>GitHub</span>
            </a>
            <a href="https://www.instagram.com/tbagusz/" target="_blank" rel="noreferrer" class="social-link" aria-label="Instagram">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.85s-.011 3.584-.069 4.85c-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07s-3.584-.012-4.85-.07c-3.252-.148-4.771-1.691-4.919-4.919-.058-1.265-.069-1.645-.069-4.85s.011-3.584.069-4.85c.149-3.225 1.664-4.771 4.919-4.919 1.266-.057 1.644-.069 4.85-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948s.014 3.667.072 4.947c.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072s3.667-.014 4.947-.072c4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.947s-.014-3.667-.072-4.947c-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.689-.073-4.948-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.162 6.162 6.162 6.162-2.759 6.162-6.162-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4s1.791-4 4-4 4 1.79 4 4-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44 1.441-.645 1.441-1.44c0-.795-.645-1.44-1.441-1.44z"/></svg>
              <span>Instagram</span>
            </a>
          </div>
        </div>

        <!-- Sisi Kanan: Form -->
        <div class="contact-form-card" data-aos="fade-left" data-aos-offset="0">
          <div class="form-card-header">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
            <span>Kirim Pesan</span>
          </div>
          <form id="contact-form" class="contact-form" name="contact">
            @csrf
            <div class="form-group">
              <input type="text" name="name" id="contact-name" placeholder=" " required>
              <label for="contact-name">Nama Lengkap</label>
            </div>
            <div class="form-group">
              <input type="email" name="email" id="contact-email" placeholder=" " required>
              <label for="contact-email">Alamat Email</label>
            </div>
            <div class="form-group">
              <textarea name="message" id="contact-message" rows="4" placeholder=" " required></textarea>
              <label for="contact-message">Pesan Kamu</label>
            </div>
            <button class="button primary contact-submit" type="submit">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
              Kirim Pesan
            </button>
          </form>
        </div>
      </div>
    </section>
    </main>

    <footer class="site-footer">
      <div class="container">
        <small>© <span id="year"></span> {{ $settings->hero_title ?? "Tubagus Alwasi'i" }}. Semua hak dilindungi.</small>
      </div>
    </footer>

    <!-- Project Modal - LinkedIn Style -->
    <div id="project-modal" class="lightbox" style="z-index: 9999;">
      <div class="pm-backdrop" id="close-project-modal-backdrop"></div>
      <div class="pm-dialog">
        <!-- Header dengan gradient -->
        <div class="pm-header">
          <div id="pm-slider" class="swiper pm-swiper">
            <div class="swiper-wrapper" id="pm-wrapper"></div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-pagination"></div>
          </div>
          <button class="pm-close" id="close-project-modal" aria-label="Tutup">&times;</button>
        </div>

        <!-- Body -->
        <div class="pm-body">
          <!-- Judul + Kategori -->
          <div class="pm-meta">
            <h2 id="pm-title" class="pm-title"></h2>
            <div class="pm-category-row">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
              <span id="pm-category" class="pm-cat-label"></span>
            </div>
          </div>

          <!-- Divider -->
          <hr class="pm-divider">

          <!-- Deskripsi -->
          <div class="pm-section">
            <h4 class="pm-section-title">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
              Deskripsi
            </h4>
            <p id="pm-desc" class="pm-desc"></p>
          </div>

          <!-- Tech Stack -->
          <div id="pm-skills-section" class="pm-section" style="display:none;">
            <h4 class="pm-section-title">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
              Skills & Tech Stack
            </h4>
            <div id="pm-skills" class="pm-skills-list"></div>
          </div>

          <!-- Link Proyek -->
          <div id="pm-link-container" class="pm-link-row">
            <a id="pm-link" href="#" target="_blank" class="button primary pm-visit-btn">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
              Kunjungi Proyek
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Audio player tersembunyi -->
    <audio id="bg-audio" loop preload="none">
      <source src="{{ asset('assets/Get You (feat. Kali Uchis).mp3') }}" type="audio/mpeg">
    </audio>

    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script src="https://unpkg.com/typed.js@2.0.16/dist/typed.umd.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="{{ asset('script.js') }}?v={{ config('app.version', '1.0') }}" defer></script>
    <script>
      // Data projects for modal
      const projectsData = [
        @foreach($projects as $p)
        {
          id: {{ $p->id }},
          name: {!! json_encode($p->name) !!},
          category: {!! json_encode($p->category->name) !!},
          description: {!! json_encode($p->description) !!},
          url_link: {!! json_encode($p->url_link) !!},
          tech_stack: {!! json_encode($p->tech_stack) !!},
          images: [
            @foreach($p->images as $img)
              {!! json_encode($img ? safeStorageUrl($img) : '') !!},
            @endforeach
          ]
        },
        @endforeach
      ];

      let modalSwiper = null;

      function openProjectPopup(id) {
        const project = projectsData.find(p => p.id === id);
        if (!project) return;
        
        document.getElementById('pm-title').innerText = project.name;
        document.getElementById('pm-category').innerText = project.category;
        
        // Description
        document.getElementById('pm-desc').innerHTML = project.description
          ? project.description.replace(/\n/g, '<br>')
          : '<em style="opacity:0.5;">Tidak ada deskripsi.</em>';
        
        // Tech stack skills
        const skillsSection = document.getElementById('pm-skills-section');
        const skillsList = document.getElementById('pm-skills');
        if (project.tech_stack && project.tech_stack.length > 0) {
          skillsList.innerHTML = project.tech_stack
            .map(skill => `<span class="pm-skill-badge">${skill}</span>`)
            .join('');
          skillsSection.style.display = 'block';
        } else {
          skillsSection.style.display = 'none';
        }

        // Link
        const linkBtn = document.getElementById('pm-link');
        const linkContainer = document.getElementById('pm-link-container');
        if (project.url_link) {
          linkBtn.href = project.url_link;
          linkContainer.style.display = 'flex';
        } else {
          linkContainer.style.display = 'none';
        }

        // Images for slider
        const wrapper = document.getElementById('pm-wrapper');
        wrapper.innerHTML = '';
        const validImages = project.images.filter(img => img);
        if (validImages.length === 0) {
          // show placeholder if no images
          wrapper.innerHTML = `<div class="swiper-slide"><div class="pm-no-img"><svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" opacity="0.3"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg><p style="opacity:0.4;margin-top:0.5rem;font-size:0.85rem;">Tidak ada gambar</p></div></div>`;
        } else {
          validImages.forEach(img => {
            wrapper.innerHTML += `<div class="swiper-slide"><div class="pm-img-slide" style="background-image: url('${img}');"></div></div>`;
          });
        }

        document.getElementById('project-modal').style.display = 'flex';
        document.body.classList.add('no-scroll');

        // Init modal swiper
        if (modalSwiper) { try { modalSwiper.destroy(true, true); } catch(e){} modalSwiper = null; }
        modalSwiper = new Swiper('#pm-slider', {
          loop: validImages.length > 1,
          navigation: { nextEl: '#pm-slider .swiper-button-next', prevEl: '#pm-slider .swiper-button-prev' },
          pagination: { el: '#pm-slider .swiper-pagination', clickable: true },
        });
      }

      document.getElementById('close-project-modal').addEventListener('click', () => {
        document.getElementById('project-modal').style.display = 'none';
        document.body.classList.remove('no-scroll');
      });
      document.getElementById('close-project-modal-backdrop').addEventListener('click', () => {
        document.getElementById('project-modal').style.display = 'none';
        document.body.classList.remove('no-scroll');
      });

      // Init card swipers
      document.addEventListener('DOMContentLoaded', () => {
        const swipers = document.querySelectorAll('.card-media.swiper');
        swipers.forEach(el => {
          new Swiper(el, {
            loop: true,
            autoplay: {
              delay: 3000,
              disableOnInteraction: false,
            },
            pagination: {
              el: el.querySelector('.swiper-pagination'),
              clickable: true,
            },
            on: {
              click: function(swiper, event) {
                // Biarkan onclick container yang bekerja
              }
            }
          });
        });
      });

      // Efek Mengetik dinamis dari database
      const heroTyping = @json($settings->hero_typing ?? ["UI/UX Designer", "Mobile Developer", "Machine Learning Enthusiast"]);
      new Typed('#typing-effect', {
        strings: heroTyping,
        typeSpeed: 50,
        backSpeed: 30,
        backDelay: 1500,
        loop: true,
      });
    </script>
  </body>
</html>
