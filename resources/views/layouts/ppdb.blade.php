@php
    $theme = request()->cookie('theme');
    if (! in_array($theme, ['light', 'dark'])) {
        $theme = 'light';
    }
    $appName = \App\Support\Branding::appName();
    $tagline = \App\Support\Branding::tagline() ?: 'Lembaga Pendidikan & Pondok Pesantren Modern Tahfidzul Qur\'an';
    $companyName = \App\Support\Branding::companyName();

    $contact = \App\Support\LandingPageData::contact();
    $phone = $contact['phone'] ?? \App\Models\Setting::get('company_phone');
    $cleanPhone = $contact['clean_phone'] ?? preg_replace('/[^0-9]/', '', (string) $phone);
    $address = $contact['address'] ?? \App\Models\Setting::get('company_address');
    $email = $contact['email'] ?? \App\Models\Setting::get('company_email');
    $officeHours = $contact['office_hours'] ?? '07.30 - 16.00 WIB';
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
        <meta name="apple-mobile-web-app-title" content="{{ $appName }} - PPDB Online">

        <title>{{ $title ?? 'Pendaftaran Santri Baru (PPDB Online)' }} — {{ $appName }}</title>
        <meta name="description" content="Pendaftaran Calon Santri Baru (PPDB Online) {{ $appName }}. Kurikulum Tahfidz Al-Qur'an 30 Juz, Sains, Dirasah Islamiyah, dan Karakter Islami.">

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

        <!-- Scripts & Styles -->
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
        class="min-h-screen w-full max-w-full overflow-x-hidden font-display antialiased bg-slate-950 text-slate-200 selection:bg-emerald-500 selection:text-white transition-colors duration-200 flex flex-col justify-between"
    >
        <!-- TOP ANNOUNCEMENT BAR -->
        <div class="relative z-50 bg-gradient-to-r from-emerald-900 via-emerald-800 to-teal-900 text-white text-[11px] sm:text-xs py-2 px-3 sm:px-4 shadow-xs border-b border-emerald-700/40">
            <div class="max-w-7xl mx-auto flex items-center justify-between gap-3 overflow-hidden">
                <div class="flex items-center gap-2 min-w-0 flex-1">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-400 text-slate-950 font-black text-[9px] sm:text-[10px] tracking-wide uppercase shrink-0">
                        <i data-lucide="sparkles" class="w-3 h-3"></i>
                        PPDB 2026/2027
                    </span>
                    <span class="font-medium text-emerald-100 truncate text-[11px] sm:text-xs">
                        Portal Resmi Penerimaan Santri Baru · Tahun Ajaran 2026/2027
                    </span>
                </div>

                <div class="hidden md:flex items-center gap-4 text-emerald-100 shrink-0 text-xs">
                    @if($phone)
                        <a
                            href="https://wa.me/{{ $cleanPhone }}?text={{ urlencode('Assalamu\'alaikum Panitia PPDB, saya ingin bertanya tentang informasi pendaftaran santri baru.') }}"
                            target="_blank"
                            class="hover:text-white flex items-center gap-1.5 transition"
                        >
                            <i data-lucide="phone-call" class="w-3.5 h-3.5 text-emerald-300"></i>
                            <span class="font-semibold">{{ $phone }}</span>
                        </a>
                    @endif
                    <span class="text-emerald-400/50">|</span>
                    <span class="flex items-center gap-1 text-emerald-200">
                        <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                        {{ $officeHours }}
                    </span>
                </div>
            </div>
        </div>

        <!-- MAIN NAVBAR (CLEAN, PROPORTIONAL & RESPONSIVE) -->
        <header
            class="sticky top-0 z-40 transition-all duration-200"
            :class="scrolled ? 'bg-slate-900/95 backdrop-blur-md shadow-sm border-b border-slate-800 py-2.5' : 'bg-slate-900/90 backdrop-blur-sm border-b border-slate-800/70 py-3'"
        >
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between gap-3 sm:gap-6">
                    <!-- LEFT: SINGLE UNIFIED BACK BUTTON & BRAND IDENTITY -->
                    <div class="flex items-center gap-2.5 sm:gap-3.5 min-w-0">
                        <!-- Tombol Kembali ke Beranda Utama (Responsif: Icon di mobile, Icon+Label di Desktop) -->
                        <a
                            href="{{ route('home') }}"
                            class="inline-flex items-center justify-center gap-1.5 h-9 px-2.5 sm:h-10 sm:px-3 rounded-xl border border-slate-700 bg-slate-800/80 hover:bg-slate-800 hover:border-emerald-500/50 text-slate-300 hover:text-emerald-400 text-xs font-semibold transition shrink-0 group shadow-xs"
                            title="Kembali ke Beranda Utama"
                            aria-label="Kembali ke Beranda Utama"
                        >
                            <i data-lucide="arrow-left" class="w-4 h-4 text-emerald-400 group-hover:-translate-x-0.5 transition-transform"></i>
                            <span class="hidden sm:inline">Beranda</span>
                        </a>

                        <!-- Separator Vertikal Halus -->
                        <div class="h-6 w-px bg-slate-800 hidden sm:block shrink-0"></div>

                        <!-- Brand Logo & Nama Lembaga -->
                        <a
                            href="{{ route('home') }}"
                            class="flex items-center gap-2 sm:gap-2.5 group min-w-0"
                            title="{{ $appName }}"
                        >
                            <div class="relative flex h-9 w-9 sm:h-10 sm:w-10 items-center justify-center rounded-2xl bg-gradient-to-tr from-emerald-700 via-emerald-600 to-teal-500 text-white shadow-sm ring-2 ring-emerald-500/30 group-hover:scale-105 transition-transform shrink-0">
                                <i data-lucide="graduation-cap" class="w-5 h-5 text-white drop-shadow"></i>
                            </div>

                            <div class="flex flex-col min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-black text-xs sm:text-sm md:text-base tracking-tight text-slate-100 leading-none group-hover:text-emerald-400 transition-colors truncate">
                                        {{ $appName }}
                                    </span>
                                    <span class="hidden sm:inline-block rounded-full bg-emerald-500/15 px-2 py-0.5 text-[10px] font-bold text-emerald-400 border border-emerald-500/30 shrink-0">
                                        PPDB Online
                                    </span>
                                </div>
                                <span class="text-[10px] sm:text-[11px] text-slate-400 mt-0.5 truncate hidden md:block">
                                    {{ $tagline }}
                                </span>
                            </div>
                        </a>
                    </div>

                    <!-- DESKTOP NAVIGATION LINKS (QUICK JUMPS TO LANDING SECTIONS) -->
                    <nav class="hidden lg:flex items-center gap-1">
                        <a href="{{ route('home') }}#program" class="px-3 py-1.5 text-xs font-semibold rounded-xl text-slate-300 hover:text-emerald-400 hover:bg-slate-800/70 transition">
                            Program Jenjang
                        </a>
                        <a href="{{ route('home') }}#tahfidz" class="px-3 py-1.5 text-xs font-semibold rounded-xl text-slate-300 hover:text-emerald-400 hover:bg-slate-800/70 transition">
                            Tahfidz 30 Juz
                        </a>
                        <a href="{{ route('home') }}#fasilitas" class="px-3 py-1.5 text-xs font-semibold rounded-xl text-slate-300 hover:text-emerald-400 hover:bg-slate-800/70 transition">
                            Fasilitas
                        </a>
                        <a href="{{ route('home') }}#prestasi" class="px-3 py-1.5 text-xs font-semibold rounded-xl text-slate-300 hover:text-emerald-400 hover:bg-slate-800/70 transition">
                            Prestasi Santri
                        </a>
                        <a href="{{ route('home') }}#faq" class="px-3 py-1.5 text-xs font-semibold rounded-xl text-slate-300 hover:text-emerald-400 hover:bg-slate-800/70 transition">
                            Tanya Jawab
                        </a>
                    </nav>

                    <!-- RIGHT ACTIONS (WHATSAPP, THEME TOGGLE, PORTAL LOGIN) -->
                    <div class="flex items-center gap-2 sm:gap-2.5 shrink-0">
                        <!-- WhatsApp Hotline (Tampil pada layar desktop lebar) -->
                        @if($phone)
                            <a
                                href="https://wa.me/{{ $cleanPhone }}?text={{ urlencode('Assalamu\'alaikum Panitia PPDB, saya ingin bertanya seputar pendaftaran santri baru.') }}"
                                target="_blank"
                                class="hidden xl:inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20 text-xs font-semibold transition"
                            >
                                <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                <span>Bantuan WhatsApp</span>
                            </a>
                        @endif

                        <!-- Theme Toggle Button -->
                        <button
                            type="button"
                            @click="toggleTheme()"
                            class="h-9 w-9 sm:h-10 sm:w-10 flex items-center justify-center rounded-xl border border-slate-700 bg-slate-800/80 hover:bg-slate-800 text-slate-300 hover:text-emerald-400 transition shadow-xs cursor-pointer shrink-0"
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

                        <!-- Portal Login Internal Staf / Santri -->
                        <a
                            href="{{ route('login') }}"
                            class="hidden sm:inline-flex items-center gap-1.5 px-3 py-2 sm:px-3.5 sm:py-2 rounded-xl border border-slate-700 bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-200 transition shadow-xs shrink-0"
                            title="Masuk Portal SIAKAD / Staf"
                        >
                            <i data-lucide="lock" class="w-3.5 h-3.5 text-emerald-500"></i>
                            <span>Masuk Portal</span>
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- MAIN CONTAINER -->
        <main class="relative flex-1 w-full max-w-full overflow-x-hidden bg-slate-950 bg-islamic-pattern py-6 sm:py-12 pb-24 xl:pb-12">
            <!-- Glowing Background Orbs for consistent rich atmosphere -->
            <div class="absolute top-10 -left-20 w-80 sm:w-96 h-80 sm:h-96 rounded-full bg-emerald-500/10 blur-3xl pointer-events-none"></div>
            <div class="absolute top-1/2 -right-20 w-80 sm:w-96 h-80 sm:h-96 rounded-full bg-amber-500/10 blur-3xl pointer-events-none"></div>

            <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                {{ $slot }}
            </div>
        </main>

        <!-- FOOTER (MATCHING LANDING PAGE VISUALS) -->
        <footer class="bg-slate-900 border-t border-slate-800 pt-12 pb-28 xl:pb-10 text-slate-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <!-- Col 1: Brand Info -->
                    <div class="lg:col-span-2 space-y-3">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-sm">
                                <i data-lucide="graduation-cap" class="w-5 h-5"></i>
                            </div>
                            <span class="font-extrabold text-base text-slate-100 tracking-tight">{{ $appName }}</span>
                        </div>
                        <p class="text-xs text-slate-400 max-w-md leading-relaxed">
                            {{ $tagline }}
                        </p>
                        <div class="pt-2 flex flex-wrap gap-2 text-xs">
                            <a
                                href="{{ route('home') }}"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-700 bg-slate-800 text-slate-200 hover:text-emerald-400 transition"
                            >
                                <i data-lucide="arrow-left" class="w-3.5 h-3.5 text-emerald-400"></i>
                                <span>Kembali ke Beranda Utama</span>
                            </a>
                            <a
                                href="{{ route('home') }}#faq"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-700 bg-slate-800 text-slate-200 hover:text-emerald-400 transition"
                            >
                                <i data-lucide="help-circle" class="w-3.5 h-3.5 text-emerald-400"></i>
                                <span>Tanya Jawab PPDB</span>
                            </a>
                        </div>
                    </div>

                    <!-- Col 2: Navigasi Cepat -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-400">Navigasi Utama</h4>
                        <ul class="space-y-2 text-xs text-slate-400">
                            <li><a href="{{ route('home') }}" class="hover:text-emerald-400 transition">Beranda Utama</a></li>
                            <li><a href="{{ route('home') }}#program" class="hover:text-emerald-400 transition">Program Pendidikan & Jenjang</a></li>
                            <li><a href="{{ route('home') }}#tahfidz" class="hover:text-emerald-400 transition">Tahapan Tahfidz 30 Juz</a></li>
                            <li><a href="{{ route('home') }}#fasilitas" class="hover:text-emerald-400 transition">Fasilitas & Keasramaan</a></li>
                            <li><a href="{{ route('home') }}#prestasi" class="hover:text-emerald-400 transition">Prestasi Santri</a></li>
                        </ul>
                    </div>

                    <!-- Col 3: Sekretariat PPDB -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-400">Sekretariat PPDB</h4>
                        <div class="space-y-2 text-xs text-slate-400">
                            <p class="leading-relaxed">{{ $address }}</p>
                            @if($phone)
                                <p class="font-semibold text-slate-200">Layanan Informasi: {{ $phone }}</p>
                            @endif
                            <p class="text-[11px] text-slate-500">Jam Layanan: {{ $officeHours }}</p>
                        </div>
                    </div>
                </div>

                <!-- Bottom Copyright -->
                <div class="border-t border-slate-800 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
                    <p>&copy; {{ date('Y') }} {{ $appName }}. Hak Cipta Dilindungi.</p>
                    <p class="text-[11px]">Sistem Terpadu Penerimaan Santri Baru (PPDB Online)</p>
                </div>
            </div>
        </footer>

        <!-- ========================================================================= -->
        <!-- NATIVE MOBILE BOTTOM NAVIGATION DOCK (FIXED ON SCREEN BOTTOM)             -->
        <!-- ========================================================================= -->
        <nav
            class="xl:hidden fixed bottom-0 inset-x-0 z-40 bg-slate-900/95 backdrop-blur-lg border-t border-slate-800 shadow-2xl transition-transform duration-200"
            style="padding-bottom: max(0.5rem, env(safe-area-inset-bottom));"
            aria-label="Navigasi Seluler PPDB"
        >
            <div class="max-w-md mx-auto px-3 pt-1.5 flex items-center justify-around">
                <!-- 1. Beranda (Direct return to Landing Page) -->
                <a
                    href="{{ route('home') }}"
                    class="flex flex-col items-center justify-center flex-1 py-1 text-slate-400 hover:text-emerald-400 active:text-emerald-400 transition-colors group cursor-pointer"
                >
                    <div class="flex items-center justify-center h-6 w-6 group-active:scale-90 transition-transform">
                        <i data-lucide="home" class="w-5 h-5"></i>
                    </div>
                    <span class="text-[10px] font-semibold tracking-tight mt-0.5">Beranda</span>
                </a>

                <!-- 2. Program -->
                <a
                    href="{{ route('home') }}#program"
                    class="flex flex-col items-center justify-center flex-1 py-1 text-slate-400 hover:text-emerald-400 active:text-emerald-400 transition-colors group cursor-pointer"
                >
                    <div class="flex items-center justify-center h-6 w-6 group-active:scale-90 transition-transform">
                        <i data-lucide="graduation-cap" class="w-5 h-5"></i>
                    </div>
                    <span class="text-[10px] font-semibold tracking-tight mt-0.5">Program</span>
                </a>

                <!-- 3. CENTER HIGHLIGHT: PPDB (Active) -->
                <a
                    href="{{ route('ppdb.register') }}"
                    class="flex flex-col items-center justify-center flex-1 -mt-4 group cursor-pointer"
                    aria-label="Formulir Pendaftaran PPDB"
                >
                    <div class="flex items-center justify-center h-12 w-12 rounded-full bg-gradient-to-tr from-emerald-600 to-teal-500 text-white shadow-lg shadow-emerald-600/35 ring-4 ring-slate-900 group-hover:scale-105 group-active:scale-95 transition-all">
                        <i data-lucide="sparkles" class="w-6 h-6 animate-pulse"></i>
                    </div>
                    <span class="text-[10px] font-bold text-emerald-400 tracking-tight mt-1">PPDB</span>
                </a>

                <!-- 4. Tahfidz -->
                <a
                    href="{{ route('home') }}#tahfidz"
                    class="flex flex-col items-center justify-center flex-1 py-1 text-slate-400 hover:text-emerald-400 active:text-emerald-400 transition-colors group cursor-pointer"
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
                    class="flex flex-col items-center justify-center flex-1 py-1 text-slate-400 hover:text-emerald-400 active:text-emerald-400 transition-colors group cursor-pointer"
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
        <!-- NATIVE MOBILE BOTTOM SHEET: MENU LENGKAP PPDB                             -->
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
            aria-label="Menu Lengkap PPDB"
        >
            <!-- Drag Handle Indicator -->
            <div class="w-12 h-1.5 rounded-full bg-slate-700/80 mx-auto mt-3 mb-1 shrink-0"></div>

            <!-- Sheet Header -->
            <div class="px-5 py-3 flex items-center justify-between border-b border-slate-800/80 shrink-0">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-500/15 text-emerald-400 shrink-0">
                        <i data-lucide="layout-grid" class="w-4 h-4"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-bold text-slate-100 leading-tight">Navigasi & Menu</h3>
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

            <!-- Scrollable Content (Menu tambahan di luar bottom nav) -->
            <div class="p-5 space-y-4 overflow-y-auto max-h-[calc(88vh-80px)]">
                <!-- Nav Section Header -->
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block px-1">
                    Menu & Informasi Lainnya
                </span>

                <!-- Quick Navigation Grid (Eksklusif menu yang belum ada di bottom nav) -->
                <div class="grid grid-cols-2 gap-2.5">
                    <a
                        href="{{ route('home') }}#keunggulan"
                        @click="mobileSheetOpen = false"
                        class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-800 bg-slate-800/60 hover:bg-slate-800 text-slate-200 hover:text-emerald-400 transition active:scale-98"
                    >
                        <i data-lucide="sparkles" class="w-4 h-4 text-amber-400 shrink-0"></i>
                        <span class="text-xs font-semibold truncate">Keunggulan</span>
                    </a>

                    <a
                        href="#alur-ppdb"
                        @click="mobileSheetOpen = false"
                        class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-800 bg-slate-800/60 hover:bg-slate-800 text-slate-200 hover:text-emerald-400 transition active:scale-98"
                    >
                        <i data-lucide="compass" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                        <span class="text-xs font-semibold truncate">Alur PPDB</span>
                    </a>

                    <a
                        href="{{ route('home') }}#keasramaan"
                        @click="mobileSheetOpen = false"
                        class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-800 bg-slate-800/60 hover:bg-slate-800 text-slate-200 hover:text-emerald-400 transition active:scale-98"
                    >
                        <i data-lucide="clock" class="w-4 h-4 text-sky-400 shrink-0"></i>
                        <span class="text-xs font-semibold truncate">Keasramaan</span>
                    </a>

                    <a
                        href="{{ route('home') }}#fasilitas"
                        @click="mobileSheetOpen = false"
                        class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-800 bg-slate-800/60 hover:bg-slate-800 text-slate-200 hover:text-emerald-400 transition active:scale-98"
                    >
                        <i data-lucide="building-2" class="w-4 h-4 text-indigo-400 shrink-0"></i>
                        <span class="text-xs font-semibold truncate">Fasilitas</span>
                    </a>

                    <a
                        href="{{ route('home') }}#prestasi"
                        @click="mobileSheetOpen = false"
                        class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-800 bg-slate-800/60 hover:bg-slate-800 text-slate-200 hover:text-emerald-400 transition active:scale-98"
                    >
                        <i data-lucide="trophy" class="w-4 h-4 text-amber-400 shrink-0"></i>
                        <span class="text-xs font-semibold truncate">Prestasi</span>
                    </a>

                    <a
                        href="{{ route('home') }}#testimoni"
                        @click="mobileSheetOpen = false"
                        class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-800 bg-slate-800/60 hover:bg-slate-800 text-slate-200 hover:text-emerald-400 transition active:scale-98"
                    >
                        <i data-lucide="message-square" class="w-4 h-4 text-pink-400 shrink-0"></i>
                        <span class="text-xs font-semibold truncate">Testimoni</span>
                    </a>

                    <a
                        href="{{ route('home') }}#agenda"
                        @click="mobileSheetOpen = false"
                        class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-800 bg-slate-800/60 hover:bg-slate-800 text-slate-200 hover:text-emerald-400 transition active:scale-98"
                    >
                        <i data-lucide="calendar" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                        <span class="text-xs font-semibold truncate">Agenda</span>
                    </a>

                    <a
                        href="{{ route('home') }}#faq"
                        @click="mobileSheetOpen = false"
                        class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-800 bg-slate-800/60 hover:bg-slate-800 text-slate-200 hover:text-emerald-400 transition active:scale-98"
                    >
                        <i data-lucide="help-circle" class="w-4 h-4 text-purple-400 shrink-0"></i>
                        <span class="text-xs font-semibold truncate">FAQ PPDB</span>
                    </a>
                </div>

                <!-- Action Buttons: WhatsApp Hotline & Portal Login -->
                <div class="pt-2 border-t border-slate-800/80 space-y-2">
                    @if($phone)
                        <a
                            href="https://wa.me/{{ $cleanPhone }}?text={{ urlencode('Assalamu\'alaikum Panitia PPDB, saya ingin bertanya tentang informasi pendaftaran.') }}"
                            target="_blank"
                            class="flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-400 font-semibold text-xs hover:bg-emerald-500/20 transition active:scale-98 truncate"
                        >
                            <i data-lucide="message-circle" class="w-4 h-4"></i>
                            <span>Chat WhatsApp Panitia PPDB</span>
                        </a>
                    @endif

                    <a
                        href="{{ route('login') }}"
                        class="flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl border border-slate-700 bg-slate-800 text-slate-200 font-semibold text-xs hover:bg-slate-700 transition active:scale-98"
                    >
                        <i data-lucide="lock" class="w-3.5 h-3.5 text-emerald-400"></i>
                        <span>Masuk Portal SIAKAD / Staf</span>
                    </a>
                </div>
            </div>
        </div>

        @livewireScripts
    </body>
</html>
