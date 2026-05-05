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
    
    $cloudName = config('filesystems.disks.cloudinary.cloud');
    if ($cloudName) {
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
    
    try {
        return Storage::url($path);
    } catch (\Throwable $e) {
        return $fallbackAsset ? asset($fallbackAsset) : '';
    }
}
@endphp
<!doctype html>
<html lang="id">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portofolio — {{ $settings->hero_title ?? "Tubagus Alwasi'i" }}</title>
    <meta name="description" content="Portofolio pribadi {{ $settings->hero_title ?? "Tubagus Alwasi'i" }}, seorang mahasiswa Teknik Informatika dengan minat pada UI/UX, Mobile Development, dan AI.">
    
    <link rel="icon" href="{{ $settings->site_logo ? safeStorageUrl($settings->site_logo) : asset('assets/favicon.svg') }}" type="image/svg+xml">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css">
    <link rel="stylesheet" href="{{ asset('style.css') }}">
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
        <div class="container hero-grid">
          <div class="hero-text" data-aos="fade-right">
            <p class="eyebrow">Hello, I'm</p>
            <h1>{{ $settings->hero_title ?? "Tubagus Alwasi'i" }}</h1>
            <p class="subtitle">
              <span id="typing-effect"></span>
            </p>
            <div class="hero-cta">
              <a class="button primary" href="{{ $settings->cv_link ? safeStorageUrl($settings->cv_link, 'assets/TubagusAlwasiCV.pdf', true, 'TUBAGUS ALWASI I CV') : asset('assets/TubagusAlwasiCV.pdf') }}" download="TUBAGUS ALWASI'I CV.pdf">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 5px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                Download CV
              </a>
              <a class="button secondary" href="#portfolio">Lihat Portfolio</a>
            </div>
          </div>
          <div class="hero-media" data-aos="fade-left" data-aos-delay="200">
            <img src="{{ $settings->hero_image ? safeStorageUrl($settings->hero_image, 'assets/profil2.jpeg') : asset('assets/profil2.jpeg') }}" alt="Foto profil {{ $settings->hero_title ?? "Tubagus Alwasi'i" }}" />
          </div>
        </div>
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
          <button class="tab-button" data-tab="tech-stack">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v2"></path><path d="M12 20v2"></path><path d="m4.93 4.93 1.41 1.41"></path><path d="m17.66 17.66 1.41 1.41"></path><path d="M2 12h2"></path><path d="M20 12h2"></path><path d="m4.93 19.07 1.41-1.41"></path><path d="m17.66 6.34 1.41-1.41"></path><circle cx="12" cy="12" r="4"></circle><path d="M12 12a2.95 2.95 0 0 1-2.95-2.95c0-1.63 1.32-2.95 2.95-2.95s2.95 1.32 2.95 2.95A2.95 2.95 0 0 1 12 12z"></path></svg>
            Tech Stack
          </button>
        </div>
          <div class="tab-content">
            <div id="tab-projects" class="tab-panel active">
              <div class="grid cards">
                @foreach($projects as $project)
                <article class="card" data-aos="fade-up">
                  <div class="card-media" style="background-image: url('{{ $project->image ? safeStorageUrl($project->image) : '' }}')"></div>
                  <div class="card-body">
                    <h3>{{ $project->name }}</h3>
                    <p>{{ $project->description }}</p>
                    <div class="tags">
                      <span>{{ $project->category->name }}</span>
                    </div>
                    <div class="card-actions">
                      @if($project->url_link)
                      <a href="{{ $project->url_link }}" target="_blank" class="button dark-ghost small">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                        Lihat Proyek
                      </a> 
                      @endif
                    </div>
                  </div>
                </article>
                @endforeach
              </div>
            </div>
            <div id="tab-certificates" class="tab-panel">
            <div class="certificates-grid">
              @foreach($certificates as $index => $cert)
              <div class="certificate-item {{ $index >= 6 ? 'hidden-item' : '' }}" data-aos="fade-up">
                <div class="cert-img-container">
                  <img src="{{ $cert->image ? safeStorageUrl($cert->image) : '' }}" alt="{{ $cert->title }}" class="cert-img">
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
            <div id="tab-tech-stack" class="tab-panel">
            <div class="tech-stack-grid">
              <div class="tech-item" data-aos="zoom-in-up">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 48 48"><path fill="#e65100" d="M41,5h-34l3,34l14,4l14-4l3-34z"/><path fill="#ff6d00" d="M24,8v31.9l11.2-3.2l2.5-28.7z"/><path fill="#ffffff" d="M24,25v-4h8.6l-0.7,11.5l-7.9,2.6v-4.2l4.1-1.4l0.3-4.5zM32.9,17l0.3-4h-9.2v4z"/><path fill="#eeeeee" d="M24,30.9v4.2l-7.9-2.6l-0.4-5.5h4l0.2,2.5zM19.1,17h4.9v-4h-9.1l0.7,12h8.4v-4h-4.6z"/></svg>
                <span>HTML5</span>
              </div>
              <div class="tech-item" data-aos="zoom-in-up" data-aos-delay="50">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 48 48"><path fill="#0277BD" d="M41,5H7l3,34l14,4l14-4L41,5z"/><path fill="#039BE5" d="M24 8L24 39.9 35.2 36.7 37.7 8z"/><path fill="#FFF" d="M33.1 13L24 13 24 17 28.9 17 28.6 21 24 21 24 25 28.4 25 28.1 29.5 24 30.9 24 35.1 31.9 32.5 32.6 21 32.6 21z"/><path fill="#EEE" d="M24,13v4h-8.9l-0.3-4H24z M19.4,21l0.2,4H24v-4H19.4z M19.8,27h-4l0.3,5.5l7.9,2.6v-4.2l-4.1-1.4L19.8,27z"/></svg>
                <span>CSS3</span>
              </div>
              <div class="tech-item" data-aos="zoom-in-up" data-aos-delay="100">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 48 48"><path fill="#FFD600" d="M6 6h36v36H6z"/><path d="M29.5 32c.5 1.7 1.5 3.1 3.2 4.1 1.7 1 3.5 1.4 5.3 1.4 1.8 0 3.6-.4 5.3-1.4 1.7-1 2.7-2.4 3.2-4.1l-4.2-.7c-.3 1.1-.9 1.9-1.8 2.5-.9.6-2 .9-3 .9s-2.1-.3-3-.9c-.9-.6-1.5-1.4-1.8-2.5l-4.2.7z"/><path d="M44.5 32.5l.8 2c.3.7.8 1.2 1.4 1.4.6.2 1.3.2 2-.1.7-.3 1.2-.8 1.4-1.4l.7-2h-6.3z"/></svg>
                <span>JavaScript</span>
              </div>
              <div class="tech-item" data-aos="zoom-in-up" data-aos-delay="150">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 48 48"><path fill="#777BB4" d="M30 4H18c-1.1 0-2 .9-2 2v36c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2z"/><path fill="#FFFFFF" d="M24 10c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 8c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 8c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 8c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
                <span>PHP</span>
              </div>
              <div class="tech-item" data-aos="zoom-in-up" data-aos-delay="200">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 48 48"><path fill="#FF2D20" d="M38.5 4L24 12.5 9.5 4 4 7.2v24.6l5.5 3.2 14.5-8.5 14.5 8.5 5.5-3.2V7.2L38.5 4z"/></svg>
                <span>Laravel</span>
              </div>
              <div class="tech-item" data-aos="zoom-in-up" data-aos-delay="250">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 48 48"><path fill="#0095D5" d="M24 4L4 24l20 20 20-20L24 4zm0 8c2.2 0 4 1.8 4 4s-1.8 4-4 4-4-1.8-4-4 1.8-4 4-4zm0 24c-6.6 0-12-5.4-12-12s5.4-12 12-12 12 5.4 12 12-5.4 12-12 12z"/></svg>
                <span>Kotlin</span>
              </div>
              <div class="tech-item" data-aos="zoom-in-up" data-aos-delay="300">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 48 48"><path fill="#3776AB" d="M24 4c-5.5 0-10 4.5-10 10v4h10v2h-14v-6c0-5.5 4.5-10 10-10s10 4.5 10 10v2h-2v-2c0-4.4-3.6-8-8-8z"/><path fill="#FFD43B" d="M24 44c5.5 0 10-4.5 10-10v-4H24v-2h14v6c0 5.5-4.5 10-10 10s-10-4.5-10-10v-2h2v2c0 4.4 3.6 8 8 8z"/></svg>
                <span>Python</span>
              </div>
              <div class="tech-item" data-aos="zoom-in-up" data-aos-delay="350">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 48 48"><path fill="#F05032" d="M44.5 22L26 3.5c-.8-.8-2.1-.8-2.9 0L19 7.6l5.4 5.4c.6-.2 1.3-.2 1.9.1.6.3 1 1 1.1 1.7.1.7-.1 1.4-.6 1.9l5.4 5.4c.5-.1 1.1-.1 1.6.1.9.3 1.5 1.2 1.5 2.1s-.6 1.8-1.5 2.1c-.9.3-1.8.1-2.4-.5l-5.4-5.4c.1-.5.1-1.1-.1-1.6-.3-.9-1.2-1.5-2.1-1.5s-1.8.6-2.1 1.5c-.3.9-.1 1.8.5 2.4l5.4 5.4c-.1.5-.1 1.1.1 1.6.3.9 1.2 1.5 2.1 1.5s1.8-.6 2.1-1.5c.3-.9.1-1.8-.5-2.4l-5.4-5.4c.1-.5.1-1.1-.1-1.6-.3-.9-1.2-1.5-2.1-1.5s-1.8.6-2.1 1.5c-.3.9-.1 1.8.5 2.4l5.4 5.4c-.1.5-.1 1.1.1 1.6.3.9 1.2 1.5 2.1 1.5s1.8-.6 2.1-1.5c.3-.9.1-1.8-.5-2.4l-5.4-5.4c.1-.5.1-1.1-.1-1.6-.3-.9-1.2-1.5-2.1-1.5s-1.8.6-2.1 1.5c-.3.9-.1 1.8.5 2.4l5.4 5.4z"/></svg>
                <span>Git</span>
              </div>
              <div class="tech-item" data-aos="zoom-in-up" data-aos-delay="400">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 48 48"><path fill="#F24E1E" d="M14 10a4 4 0 1 1 8 0 4 4 0 0 1-8 0zm0 10a4 4 0 1 1 8 0 4 4 0 0 1-8 0zm0 10a4 4 0 1 1 8 0 4 4 0 0 1-8 0z"/><path fill="#FF7262" d="M26 10a4 4 0 1 1 8 0 4 4 0 0 1-8 0z"/><path fill="#1ABCFE" d="M26 20a4 4 0 1 1 8 0 4 4 0 0 1-8 0z"/><path fill="#0ACF83" d="M26 30a4 4 0 1 1 8 0 4 4 0 0 1-8 0zm0-10a4 4 0 0 1 4 4v4a4 4 0 0 1-4 4 4 4 0 0 1-4-4v-4a4 4 0 0 1 4-4z"/><path fill="#A259FF" d="M18 20a4 4 0 0 1 4 4v4a4 4 0 0 1-4 4 4 4 0 0 1-4-4v-4a4 4 0 0 1 4-4z"/></svg>
                <span>Figma</span>
              </div>
              <div class="tech-item" data-aos="zoom-in-up" data-aos-delay="450">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 48 48"><path fill="#00758F" d="M24 4C12.95 4 4 8.93 4 15v18c0 6.07 8.95 11 20 11s20-4.93 20-11V15c0-6.07-8.95-11-20-11zm0 4c8.84 0 16 3.58 16 8s-7.16 8-16 8-16-3.58-16-8 7.16-8 16-8z"/></svg>
                <span>MySQL</span>
              </div>
            </div>
          </div>
          </div>
        </div>
      </section>
      <section id="contact" class="section">
      <div class="section-header" data-aos="fade-up">
        <h2>Kontak</h2>
        <p class="section-subtitle">♫ Mari terhubung dan berkolaborasi ♫</p>
      </div>
      <div class="container contact-wrapper">
        <!-- Sisi Kiri: Info & Dekorasi -->
        <div class="contact-info" data-aos="fade-right">
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
        <div class="contact-form-card" data-aos="fade-left">
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

    <div id="lightbox" class="lightbox">
      <span class="close">&times;</span>
      <img class="lightbox-content" id="lightbox-img" alt="Tampilan diperbesar">
    </div>

    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script src="https://unpkg.com/typed.js@2.0.16/dist/typed.umd.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/@emailjs/browser@3/dist/email.min.js"></script> 
    <script src="{{ asset('script.js') }}"></script>
    <script>
      // Efek Mengetik dinamis dari database
      const heroTyping = {!! $settings->hero_typing ?? '["UI/UX Designer", "Mobile Developer", "Machine Learning Enthusiast"]' !!};
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
