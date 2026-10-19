@php
    $theme = request()->cookie('theme');
    if (! in_array($theme, ['light', 'dark'])) {
        $theme = 'light';
    }
    $appName = \App\Support\Branding::appName();
    $tagline = \App\Support\Branding::tagline() ?: 'Lembaga Pendidikan Islam & Pondok Pesantren Modern Tahfidzul Qur\'an';
    $companyName = \App\Support\Branding::companyName();

    $contact = \App\Support\LandingPageData::contact();
    $phone = $contact['phone'];
    $cleanPhone = $contact['clean_phone'];
    $address = $contact['address'];
    $email = $contact['email'];
    $officeHours = $contact['office_hours'];
    $youtubeUrl = $contact['youtube'];
    $instagramUrl = $contact['instagram'];
    $facebookUrl = $contact['facebook'];
    $tiktokUrl = $contact['tiktok'];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth {{ $theme }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- PWA Web App Meta & Manifest -->
    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="theme-color" content="#047857">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ $appName }}">

    <title>{{ $title ?? $appName . ' — ' . $tagline }}</title>
    <meta name="description" content="Pondok Pesantren & Sekolah Islam Modern Terpadu dengan kurikulum Tahfidz Al-Qur'an 30 Juz, Sains, Dirasah Islamiyah, dan Pembinaan Karakter Qur'ani Berprestasi Global.">

    <link rel="icon" href="{{ \App\Support\Branding::iconUrl() }}">
    <link rel="apple-touch-icon" href="{{ \App\Support\Branding::iconUrl() }}">

    <!-- Google Fonts: Inter, Plus Jakarta Sans, & Amiri (Arabic Typography) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400&family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Anti-FOUC Theme Initializer -->
    <script>
        (function() {
            try {
                var theme = localStorage.getItem('theme');
                if (theme === 'dark') {
                    document.documentElement.classList.add('dark');
                    document.documentElement.classList.remove('light');
                    document.cookie = 'theme=dark; path=/; max-age=31536000; SameSite=Lax';
                } else if (theme === 'light') {
                    document.documentElement.classList.add('light');
                    document.documentElement.classList.remove('dark');
                    document.cookie = 'theme=light; path=/; max-age=31536000; SameSite=Lax';
                }
            } catch (e) {}
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        html, body {
            max-width: 100%;
            overflow-x: hidden;
            position: relative;
        }
        .font-arabic {
            font-family: 'Amiri', serif;
        }
        .font-display {
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
        }
        /* Subtle Islamic Geometric Lattice Pattern */
        .bg-islamic-pattern {
            background-image: radial-gradient(rgba(16, 185, 129, 0.12) 1px, transparent 1px),
                              radial-gradient(rgba(245, 158, 11, 0.08) 1px, transparent 1px);
            background-size: 32px 32px;
            background-position: 0 0, 16px 16px;
        }
        .dark .bg-islamic-pattern {
            background-image: radial-gradient(rgba(52, 211, 153, 0.15) 1px, transparent 1px),
                              radial-gradient(rgba(251, 191, 36, 0.08) 1px, transparent 1px);
            background-size: 32px 32px;
            background-position: 0 0, 16px 16px;
        }
    </style>
</head>
<body
    x-data="{
        scrolled: false,
        mobileSheetOpen: false,
        isDark: document.documentElement.classList.contains('dark'),
        toggleTheme() {
            this.isDark = !this.isDark;
            if (this.isDark) {
                document.documentElement.classList.add('dark');
                document.documentElement.classList.remove('light');
                localStorage.setItem('theme', 'dark');
                document.cookie = 'theme=dark; path=/; max-age=31536000; SameSite=Lax';
            } else {
                document.documentElement.classList.remove('dark');
                document.documentElement.classList.add('light');
                localStorage.setItem('theme', 'light');
                document.cookie = 'theme=light; path=/; max-age=31536000; SameSite=Lax';
            }
        }
    }"
    @scroll.window="scrolled = (window.pageYOffset > 20)"
    class="min-h-screen w-full max-w-full overflow-x-hidden font-display antialiased bg-slate-950 text-slate-200 selection:bg-emerald-500 selection:text-white transition-colors duration-200"
>
    <!-- TOP ANNOUNCEMENT BAR -->
    <div class="relative z-50 bg-gradient-to-r from-emerald-900 via-emerald-800 to-teal-900 text-white text-[11px] sm:text-xs py-2 px-3 sm:px-4 shadow-xs border-b border-emerald-700/40">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-2 overflow-hidden">
            <div class="flex items-center gap-2 min-w-0 flex-1">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-400 text-slate-950 font-black text-[9px] sm:text-[10px] tracking-wide uppercase shrink-0">
                    <i data-lucide="sparkles" class="w-3 h-3"></i>
                    PPDB 2026/2027
                </span>
                <span class="font-medium text-emerald-100 truncate text-[11px] sm:text-xs">
                    Penerimaan Santri Baru Gelombang 1 Dibuka · Dapatkan Keringanan Registrasi Awal.
                </span>
            </div>

            <div class="hidden sm:flex items-center gap-4 text-emerald-100 shrink-0 text-xs">
                <a href="https://wa.me/{{ $cleanPhone }}?text={{ urlencode('Assalamu\'alaikum Panitia PPDB, saya ingin bertanya tentang informasi pendaftaran santri baru.') }}" target="_blank" class="hover:text-white flex items-center gap-1.5 transition">
                    <i data-lucide="phone-call" class="w-3.5 h-3.5 text-emerald-300"></i>
                    <span class="font-semibold">{{ $phone }}</span>
                </a>
                <span class="text-emerald-400/50">|</span>
                <span class="flex items-center gap-1 text-emerald-200">
                    <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                    {{ $officeHours }}
                </span>
            </div>
        </div>
    </div>

    <!-- MAIN NAVBAR (STREAMLINED & RESPONSIVE) -->
    <header
        class="sticky top-0 z-40 transition-all duration-200"
        :class="scrolled ? 'bg-slate-900/95 backdrop-blur-md shadow-sm border-b border-slate-800 py-2.5' : 'bg-slate-900/90 backdrop-blur-sm border-b border-slate-800/70 py-3'"
    >
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between gap-3 sm:gap-6">
                <!-- LOGO & BRAND IDENTITY (COMPACT & CLEAN) -->
                <a href="#beranda" class="flex items-center gap-2.5 sm:gap-3 group shrink-0 min-w-0">
                    <div class="relative flex h-9 w-9 sm:h-11 sm:w-11 items-center justify-center rounded-2xl bg-gradient-to-tr from-emerald-700 via-emerald-600 to-teal-500 text-white shadow-sm shadow-emerald-500/25 ring-2 ring-emerald-500/30 group-hover:scale-105 transition-transform shrink-0">
                        <i data-lucide="graduation-cap" class="w-5 h-5 sm:w-6 sm:h-6 text-white drop-shadow"></i>
                    </div>

                    <div class="flex flex-col min-w-0">
                        <div class="flex items-center gap-1.5">
                            <span class="font-black text-sm sm:text-base tracking-tight text-slate-100 leading-none group-hover:text-emerald-600 transition-colors truncate">
                                {{ $appName }}
                            </span>
                            <span class="hidden md:inline-flex rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-bold text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 shrink-0">
                                Akreditasi A
                            </span>
                        </div>
                        <span class="text-[10px] sm:text-[11px] text-slate-400 mt-0.5 sm:mt-1 truncate">
                            Islamic Boarding School & Madrasah
                        </span>
                    </div>
                </a>

                <!-- DESKTOP NAVIGATION LINKS (CONCISE & SPACIOUS) -->
                <nav class="hidden xl:flex items-center gap-1">
                    <a href="#beranda" class="px-3 py-2 text-xs xl:text-sm font-semibold rounded-xl text-slate-300 hover:text-emerald-600 hover:bg-slate-800 transition">
                        Beranda
                    </a>
                    <a href="#keunggulan" class="px-3 py-2 text-xs xl:text-sm font-semibold rounded-xl text-slate-300 hover:text-emerald-600 hover:bg-slate-800 transition">
                        Keunggulan
                    </a>
                    <a href="#program" class="px-3 py-2 text-xs xl:text-sm font-semibold rounded-xl text-slate-300 hover:text-emerald-600 hover:bg-slate-800 transition">
                        Program
                    </a>
                    <a href="#tahfidz" class="px-3 py-2 text-xs xl:text-sm font-semibold rounded-xl text-slate-300 hover:text-emerald-600 hover:bg-slate-800 transition">
                        Tahfidz
                    </a>
                    <a href="#keasramaan" class="px-3 py-2 text-xs xl:text-sm font-semibold rounded-xl text-slate-300 hover:text-emerald-600 hover:bg-slate-800 transition">
                        Keasramaan
                    </a>
                    <a href="#fasilitas" class="px-3 py-2 text-xs xl:text-sm font-semibold rounded-xl text-slate-300 hover:text-emerald-600 hover:bg-slate-800 transition">
                        Fasilitas
                    </a>
                    <a href="#faq" class="px-3 py-2 text-xs xl:text-sm font-semibold rounded-xl text-slate-300 hover:text-emerald-600 hover:bg-slate-800 transition">
                        FAQ
                    </a>
                </nav>

                <!-- RIGHT ACTIONS (THEME TOGGLE, PORTAL, & PPDB) -->
                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    <!-- Theme Toggle Button (Inline SVGs, Instant Render) -->
                    <button
                        type="button"
                        @click="toggleTheme()"
                        class="h-9 w-9 sm:h-10 sm:w-10 flex items-center justify-center rounded-xl border border-slate-700 bg-slate-800 text-slate-300 hover:text-emerald-600 transition shadow-xs cursor-pointer"
                        :title="isDark ? 'Ganti ke Mode Terang' : 'Ganti ke Mode Gelap'"
                        aria-label="Toggle tema gelap/terang"
                    >
                        <span x-show="isDark" class="inline-flex items-center justify-center text-amber-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="5"></circle>
                                <path stroke-linecap="round" d="M12 1v2m0 18v2M4.22 4.22l1.42 1.42m12.72 12.72l1.42 1.42M1 12h2m18 0h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"></path>
                            </svg>
                        </span>
                        <span x-show="!isDark" class="inline-flex items-center justify-center text-slate-700">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"></path>
                            </svg>
                        </span>
                    </button>

                    <!-- Portal Santri / Login Staf Button -->
                    <a
                        href="{{ route('login') }}"
                        class="inline-flex items-center gap-1.5 px-3 py-2 sm:px-3.5 sm:py-2 rounded-xl border border-slate-700 bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-200 transition shadow-xs"
                        title="Masuk Portal SIAKAD / Staf"
                    >
                        <i data-lucide="lock" class="w-3.5 h-3.5 text-emerald-500"></i>
                        <span class="hidden sm:inline">Masuk Portal</span>
                        <span class="sm:hidden">Portal</span>
                    </a>

                    <!-- PPDB Online CTA Button (Visible on sm+ screens; on mobile handled natively by Bottom Dock) -->
                    <a
                        href="{{ route('ppdb.register') }}"
                        class="hidden sm:inline-flex items-center gap-1.5 px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white text-xs sm:text-sm font-bold shadow-md shadow-emerald-600/25 active:scale-95 transition-all"
                    >
                        <span>Daftar PPDB</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- CONTENT BODY -->
    <main class="relative w-full max-w-full overflow-x-hidden">
        {{ $slot }}
    </main>

    <!-- FOOTER WITH RICH LINKS & CONTACT -->
    <footer id="kontak" class="bg-slate-900 border-t border-slate-800 pt-16 pb-28 xl:pb-12 text-slate-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8">
                <!-- Col 1: Identity & Legal (2 Cols on lg) -->
                <div class="lg:col-span-2 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-600 text-white shadow-md">
                            <i data-lucide="graduation-cap" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <span class="font-extrabold text-base tracking-tight text-slate-100 block">
                                {{ $appName }}
                            </span>
                            <span class="text-[11px] text-slate-400 block">
                                {{ $companyName }}
                            </span>
                        </div>
                    </div>

                    <p class="text-xs text-slate-400 leading-relaxed max-w-sm">
                        Lembaga Pendidikan Islam dan Pondok Pesantren Modern yang mengintegrasikan kurikulum Tahfidzul Qur'an 30 Juz Bersanad, pendalaman Kitab Turats Salaf, kecakapan bahasa internasional, dan sains modern.
                    </p>

                    <!-- Legal Chips -->
                    <div class="flex flex-wrap items-center gap-2 text-[11px]">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-slate-800 border border-slate-700 text-slate-300">
                            <i data-lucide="award" class="w-3.5 h-3.5 text-emerald-500"></i>
                            Terakreditasi "A" BAN-SM
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-slate-800 border border-slate-700 text-slate-300">
                            <i data-lucide="check-circle" class="w-3.5 h-3.5 text-teal-500"></i>
                            NPSN: 69982310
                        </span>
                    </div>

                    <!-- Social Media Links (Using crisp SVG icons) -->
                    <div class="pt-2 flex items-center gap-3">
                        @if ($youtubeUrl)
                            <a href="{{ $youtubeUrl }}" target="_blank" class="h-9 w-9 flex items-center justify-center rounded-xl bg-slate-800 hover:bg-red-600 text-slate-300 hover:text-white transition" aria-label="YouTube">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                            </a>
                        @endif
                        @if ($instagramUrl)
                            <a href="{{ $instagramUrl }}" target="_blank" class="h-9 w-9 flex items-center justify-center rounded-xl bg-slate-800 hover:bg-pink-600 text-slate-300 hover:text-white transition" aria-label="Instagram">
                                <svg class="w-4 h-4 fill-none stroke-current" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>
                            </a>
                        @endif
                        @if ($facebookUrl)
                            <a href="{{ $facebookUrl }}" target="_blank" class="h-9 w-9 flex items-center justify-center rounded-xl bg-slate-800 hover:bg-blue-600 text-slate-300 hover:text-white transition" aria-label="Facebook">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            </a>
                        @endif
                        @if ($tiktokUrl)
                            <a href="{{ $tiktokUrl }}" target="_blank" class="h-9 w-9 flex items-center justify-center rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition" aria-label="TikTok">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.24 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
                            </a>
                        @endif
                        <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" class="h-9 w-9 flex items-center justify-center rounded-xl bg-slate-800 hover:bg-emerald-600 text-slate-300 hover:text-white transition" aria-label="WhatsApp">
                            <i data-lucide="message-circle" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>

                <!-- Col 2: Program Pendidikan -->
                <div class="space-y-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Program Jenjang</h4>
                    <ul class="space-y-2 text-xs text-slate-400">
                        <li><a href="#program" class="hover:text-emerald-600 transition">MTs Unggulan Sains & Riset</a></li>
                        <li><a href="#program" class="hover:text-emerald-600 transition">Madrasah Aliyah (MA) Plus</a></li>
                        <li><a href="#tahfidz" class="hover:text-emerald-600 transition">Takhassus Tahfidz 30 Juz Bersanad</a></li>
                        <li><a href="#program" class="hover:text-emerald-600 transition">Dirasah Islamiyah & Kitab Kuning</a></li>
                        <li><a href="#keasramaan" class="hover:text-emerald-600 transition">Lingkungan Bahasa Aktif (Arab & Inggris)</a></li>
                        <li><a href="#program" class="hover:text-emerald-600 transition">Persiapan Masuk PTN & Timur Tengah</a></li>
                    </ul>
                </div>

                <!-- Col 3: Layanan & Akses Digital -->
                <div class="space-y-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Layanan Santri & Wali</h4>
                    <ul class="space-y-2 text-xs text-slate-400">
                        <li><a href="{{ route('ppdb.register') }}" class="hover:text-emerald-600 font-semibold text-emerald-600 dark:text-emerald-300 transition">Pendaftaran PPDB Online</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-emerald-600 transition">Portal Mandiri Wali Santri</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-emerald-600 transition">Sistem Informasi Akademik (SIAKAD)</a></li>
                        <li><a href="#tahfidz" class="hover:text-emerald-600 transition">Mutaba'ah & E-Tahfidz Qur'an</a></li>
                        <li><a href="#faq" class="hover:text-emerald-600 transition">Panduan Biaya & Kalender Masuk</a></li>
                        <li><a href="#fasilitas" class="hover:text-emerald-600 transition">Fasilitas Kampus</a></li>
                    </ul>
                </div>

                <!-- Col 4: Kontak & Sekretariat -->
                <div class="space-y-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Sekretariat & Lokasi</h4>
                    <ul class="space-y-2.5 text-xs text-slate-400">
                        <li class="flex items-start gap-2">
                            <i data-lucide="map-pin" class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5"></i>
                            <span>{{ $address }}</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="phone" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                            <span>{{ $phone }}</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="mail" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                            <span>{{ $email }}</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="clock" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                            <span>{{ $officeHours }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Copyright & Quranic Quote -->
            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-center sm:text-left text-xs text-slate-400 border-t border-slate-800">
                <div class="flex items-center gap-2">
                    <span>&copy; {{ date('Y') }} <strong>{{ $appName }}</strong>. Seluruh Hak Cipta Dilindungi.</span>
                </div>
                <div class="font-arabic text-sm text-slate-400 flex items-center gap-2">
                    <span>طَلَبُ الْعِلْمِ فَرِيضَةٌ عَلَى كُلِّ مُسْلِمٍ</span>
                    <span class="text-[11px] font-sans text-slate-400">· "Menuntut ilmu adalah kewajiban atas setiap Muslim" (HR. Ibnu Majah)</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- FLOATING WHATSAPP BUTTON WITH PULSE (Sits comfortably above the mobile bottom dock) -->
    <a
        href="https://wa.me/{{ $cleanPhone }}?text={{ urlencode('Assalamu\'alaikum Panitia PPDB, saya ingin bertanya tentang informasi pendaftaran santri baru.') }}"
        target="_blank"
        class="fixed bottom-20 right-4 sm:bottom-6 sm:right-6 z-40 flex items-center gap-2.5 px-3.5 py-2.5 sm:px-4 sm:py-3 rounded-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-xl shadow-emerald-950/40 hover:scale-105 transition-all group cursor-pointer"
        aria-label="Konsultasi Panitia PPDB WhatsApp"
    >
        <span class="relative flex h-3 w-3">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-300 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3 w-3 bg-white"></span>
        </span>
        <i data-lucide="message-circle" class="w-5 h-5 text-white"></i>
        <span class="hidden sm:inline">Tanya Panitia PPDB</span>
    </a>

    <!-- ========================================================================= -->
    <!-- NATIVE MOBILE BOTTOM NAVIGATION BAR (FIXED DOCK)                          -->
    <!-- ========================================================================= -->
    <nav
        class="fixed bottom-0 inset-x-0 z-50 xl:hidden bg-slate-900/95 backdrop-blur-xl border-t border-slate-800 shadow-[0_-8px_30px_rgba(0,0,0,0.3)] transition-all duration-200"
        style="padding-bottom: max(0.5rem, env(safe-area-inset-bottom));"
        aria-label="Navigasi Seluler"
    >
        <div class="max-w-md mx-auto px-3 pt-1.5 flex items-center justify-around">
            <!-- 1. Beranda -->
            <a
                href="#beranda"
                class="flex flex-col items-center justify-center flex-1 py-1 text-slate-400 hover:text-emerald-500 active:text-emerald-500 transition-colors group cursor-pointer"
            >
                <div class="flex items-center justify-center h-6 w-6 group-active:scale-90 transition-transform">
                    <i data-lucide="home" class="w-5 h-5"></i>
                </div>
                <span class="text-[10px] font-semibold tracking-tight mt-0.5">Beranda</span>
            </a>

            <!-- 2. Program -->
            <a
                href="#program"
                class="flex flex-col items-center justify-center flex-1 py-1 text-slate-400 hover:text-emerald-500 active:text-emerald-500 transition-colors group cursor-pointer"
            >
                <div class="flex items-center justify-center h-6 w-6 group-active:scale-90 transition-transform">
                    <i data-lucide="graduation-cap" class="w-5 h-5"></i>
                </div>
                <span class="text-[10px] font-semibold tracking-tight mt-0.5">Program</span>
            </a>

            <!-- 3. CENTER HIGHLIGHT: Daftar PPDB -->
            <a
                href="{{ route('ppdb.register') }}"
                class="flex flex-col items-center justify-center flex-1 -mt-4 group cursor-pointer"
                aria-label="Daftar Santri Baru PPDB Online"
            >
                <div class="flex items-center justify-center h-12 w-12 rounded-full bg-gradient-to-tr from-emerald-600 to-teal-500 text-white shadow-lg shadow-emerald-600/35 ring-4 ring-slate-900 group-hover:scale-105 group-active:scale-95 transition-all">
                    <i data-lucide="sparkles" class="w-6 h-6 animate-pulse"></i>
                </div>
                <span class="text-[10px] font-bold text-emerald-500 tracking-tight mt-1">PPDB</span>
            </a>

            <!-- 4. Tahfidz -->
            <a
                href="#tahfidz"
                class="flex flex-col items-center justify-center flex-1 py-1 text-slate-400 hover:text-emerald-500 active:text-emerald-500 transition-colors group cursor-pointer"
            >
                <div class="flex items-center justify-center h-6 w-6 group-active:scale-90 transition-transform">
                    <i data-lucide="book-open" class="w-5 h-5"></i>
                </div>
                <span class="text-[10px] font-semibold tracking-tight mt-0.5">Tahfidz</span>
            </a>

            <!-- 5. Menu / Lainnya (Opens Full Bottom Sheet) -->
            <button
                type="button"
                @click="mobileSheetOpen = true"
                class="flex flex-col items-center justify-center flex-1 py-1 text-slate-400 hover:text-emerald-500 active:text-emerald-500 transition-colors group cursor-pointer"
                aria-label="Buka Menu Lengkap"
            >
                <div class="flex items-center justify-center h-6 w-6 group-active:scale-90 transition-transform">
                    <i data-lucide="layout-grid" class="w-5 h-5"></i>
                </div>
                <span class="text-[10px] font-semibold tracking-tight mt-0.5">Menu</span>
            </button>
        </div>
    </nav>

    <!-- ========================================================================= -->
    <!-- NATIVE MOBILE BOTTOM SHEET: MENU LENGKAP                                   -->
    <!-- ========================================================================= -->
    <!-- Backdrop Overlay -->
    <div
        x-show="mobileSheetOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm xl:hidden"
        @click="mobileSheetOpen = false"
        @keydown.escape.window="mobileSheetOpen = false"
        aria-hidden="true"
    ></div>

    <!-- Bottom Sheet Container -->
    <div
        x-show="mobileSheetOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="translate-y-full"
        x-transition:enter-end="translate-y-0"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="translate-y-0"
        x-transition:leave-end="translate-y-full"
        class="fixed inset-x-0 bottom-0 z-50 max-h-[88vh] xl:hidden bg-slate-900 border-t border-slate-800 rounded-t-3xl shadow-2xl flex flex-col overflow-hidden"
        style="padding-bottom: max(1.25rem, env(safe-area-inset-bottom));"
        role="dialog"
        aria-modal="true"
        aria-label="Menu Lengkap Ma'had"
    >
        <!-- Drag Handle Indicator -->
        <div class="w-12 h-1.5 rounded-full bg-slate-700/80 mx-auto mt-3 mb-1 shrink-0"></div>

        <!-- Sheet Header -->
        <div class="px-5 py-3 flex items-center justify-between border-b border-slate-800/80 shrink-0">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 shrink-0">
                    <i data-lucide="layout-grid" class="w-4 h-4"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="text-sm font-bold text-slate-100 leading-tight">Menu Lengkap</h3>
                    <p class="text-[11px] text-slate-400 truncate">{{ $appName }}</p>
                </div>
            </div>
            <button
                type="button"
                @click="mobileSheetOpen = false"
                class="p-2 rounded-xl text-slate-400 hover:text-slate-200 hover:bg-slate-800 transition cursor-pointer shrink-0"
                aria-label="Tutup menu"
            >
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Scrollable Navigation Grid & Action Buttons (Eksklusif menu yang belum ada di Bottom Nav) -->
        <div class="p-4 sm:p-5 overflow-y-auto space-y-4 text-left">
            <!-- Nav Section Header -->
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block px-1">
                Menu & Informasi Lainnya
            </span>

            <!-- 2-Column Menu Grid (Tanpa item yang sudah ada di bottom nav) -->
            <div class="grid grid-cols-2 gap-2 text-xs font-semibold">
                <a
                    href="#keunggulan"
                    @click="mobileSheetOpen = false"
                    class="p-3 rounded-2xl bg-slate-800/70 hover:bg-slate-800 border border-slate-700/60 flex items-center gap-2.5 text-slate-200 transition active:scale-98"
                >
                    <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-amber-500/15 text-amber-500 shrink-0">
                        <i data-lucide="sparkles" class="w-4 h-4"></i>
                    </div>
                    <span class="truncate">Keunggulan</span>
                </a>

                <a
                    href="#keasramaan"
                    @click="mobileSheetOpen = false"
                    class="p-3 rounded-2xl bg-slate-800/70 hover:bg-slate-800 border border-slate-700/60 flex items-center gap-2.5 text-slate-200 transition active:scale-98"
                >
                    <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-sky-500/15 text-sky-500 shrink-0">
                        <i data-lucide="clock" class="w-4 h-4"></i>
                    </div>
                    <span class="truncate">Keasramaan</span>
                </a>

                <a
                    href="#fasilitas"
                    @click="mobileSheetOpen = false"
                    class="p-3 rounded-2xl bg-slate-800/70 hover:bg-slate-800 border border-slate-700/60 flex items-center gap-2.5 text-slate-200 transition active:scale-98"
                >
                    <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-indigo-500/15 text-indigo-500 shrink-0">
                        <i data-lucide="building-2" class="w-4 h-4"></i>
                    </div>
                    <span class="truncate">Fasilitas</span>
                </a>

                <a
                    href="#prestasi"
                    @click="mobileSheetOpen = false"
                    class="p-3 rounded-2xl bg-slate-800/70 hover:bg-slate-800 border border-slate-700/60 flex items-center gap-2.5 text-slate-200 transition active:scale-98"
                >
                    <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-amber-500/15 text-amber-500 shrink-0">
                        <i data-lucide="award" class="w-4 h-4"></i>
                    </div>
                    <span class="truncate">Prestasi</span>
                </a>

                <a
                    href="#testimoni"
                    @click="mobileSheetOpen = false"
                    class="p-3 rounded-2xl bg-slate-800/70 hover:bg-slate-800 border border-slate-700/60 flex items-center gap-2.5 text-slate-200 transition active:scale-98"
                >
                    <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-pink-500/15 text-pink-500 shrink-0">
                        <i data-lucide="message-square" class="w-4 h-4"></i>
                    </div>
                    <span class="truncate">Testimoni</span>
                </a>

                <a
                    href="#agenda"
                    @click="mobileSheetOpen = false"
                    class="p-3 rounded-2xl bg-slate-800/70 hover:bg-slate-800 border border-slate-700/60 flex items-center gap-2.5 text-slate-200 transition active:scale-98"
                >
                    <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-500/15 text-emerald-500 shrink-0">
                        <i data-lucide="calendar" class="w-4 h-4"></i>
                    </div>
                    <span class="truncate">Agenda Acara</span>
                </a>

                <a
                    href="#faq"
                    @click="mobileSheetOpen = false"
                    class="p-3 rounded-2xl bg-slate-800/70 hover:bg-slate-800 border border-slate-700/60 flex items-center gap-2.5 text-slate-200 transition active:scale-98"
                >
                    <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-purple-500/15 text-purple-500 shrink-0">
                        <i data-lucide="help-circle" class="w-4 h-4"></i>
                    </div>
                    <span class="truncate">Tanya Jawab (FAQ)</span>
                </a>

                <a
                    href="#kontak"
                    @click="mobileSheetOpen = false"
                    class="p-3 rounded-2xl bg-slate-800/70 hover:bg-slate-800 border border-slate-700/60 flex items-center gap-2.5 text-slate-200 transition active:scale-98"
                >
                    <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-rose-500/15 text-rose-500 shrink-0">
                        <i data-lucide="map-pin" class="w-4 h-4"></i>
                    </div>
                    <span class="truncate">Lokasi & Kontak</span>
                </a>
            </div>

            <!-- Action Buttons Section -->
            <div class="pt-3 border-t border-slate-800">
                <div class="grid grid-cols-2 gap-2">
                    <a
                        href="{{ route('login') }}"
                        class="flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl border border-slate-700 bg-slate-800 text-slate-200 font-semibold text-xs hover:bg-slate-700 transition active:scale-98 truncate"
                    >
                        <i data-lucide="lock" class="w-3.5 h-3.5 text-emerald-500 shrink-0"></i>
                        <span class="truncate">Portal SIAKAD</span>
                    </a>

                    <a
                        href="https://wa.me/{{ $cleanPhone }}?text={{ urlencode('Assalamu\'alaikum Panitia PPDB, saya ingin bertanya tentang informasi ma\'had.') }}"
                        target="_blank"
                        class="flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-semibold text-xs hover:bg-emerald-500/20 transition active:scale-98 truncate"
                    >
                        <i data-lucide="message-circle" class="w-3.5 h-3.5 shrink-0"></i>
                        <span class="truncate">WhatsApp</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    @livewireScripts
</body>
</html>
