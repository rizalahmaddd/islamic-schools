@php
    $appName = \App\Support\Branding::appName();
    $tagline = \App\Support\Branding::tagline() ?: 'Lembaga Pendidikan Islam & Pondok Pesantren Modern Tahfidzul Qur\'an';
    $companyName = \App\Support\Branding::companyName();

    // Data Dinamis dari LandingPageData & Setting (100% CMS-Managed)
    $hero = \App\Support\LandingPageData::hero();
    $mudir = \App\Support\LandingPageData::mudir();
    $stats = \App\Support\LandingPageData::stats();
    $pillars = \App\Support\LandingPageData::pillars();
    $programs = \App\Support\LandingPageData::programs();
    $tracks = \App\Support\LandingPageData::tracks();
    $tahfidzSteps = \App\Support\LandingPageData::tahfidzRoadmap();
    $dailySchedules = \App\Support\LandingPageData::dailySchedule();
    $facilities = \App\Support\LandingPageData::facilities();
    $achievements = \App\Support\LandingPageData::achievements();
    $testimonials = \App\Support\LandingPageData::testimonials();
    $agenda = \App\Support\LandingPageData::agenda();
    $faqs = \App\Support\LandingPageData::faqs();
    $contact = \App\Support\LandingPageData::contact();
    $cta = \App\Support\LandingPageData::cta();
    $sec = \App\Support\LandingPageData::sectionHeadings();
    $videoEmbed = \App\Support\LandingPageData::youtubeEmbedUrl($hero['video_url'] ?? null);

    $cleanPhone = $contact['clean_phone'];
    $phone = $contact['phone'];
@endphp

<x-layouts.public :title="$appName . ' — ' . $tagline">

    <div x-data="{ videoModalOpen: false, selectedTrack: 'tahfidz' }" class="w-full max-w-full overflow-x-hidden">

        <!-- ========================================================================= -->
        <!-- SECTION 1: HERO SECTION DENGAN AKSEN ISLAMI MODERN & LIVE METRICS         -->
        <!-- ========================================================================= -->
        <section id="beranda" class="relative pt-6 pb-16 sm:pt-12 sm:pb-24 lg:pt-16 lg:pb-32 bg-slate-950 bg-islamic-pattern overflow-hidden">
            <!-- Glowing Background Orbs -->
            <div class="absolute top-1/4 -left-20 w-80 sm:w-96 h-80 sm:h-96 rounded-full bg-emerald-500/10 dark:bg-emerald-500/20 blur-3xl pointer-events-none"></div>
            <div class="absolute top-1/3 -right-20 w-80 sm:w-96 h-80 sm:h-96 rounded-full bg-amber-500/10 dark:bg-amber-500/15 blur-3xl pointer-events-none"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-8 items-center">
                    
                    <!-- SISI KIRI: Headline & CTA (7 Cols) -->
                    <div class="lg:col-span-7 space-y-6 sm:space-y-7 text-center lg:text-left">
                        
                        <!-- Pulsing Status Badge -->
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-xs sm:text-sm font-semibold shadow-xs">
                            <span class="flex h-2.5 w-2.5 rounded-full bg-emerald-500 animate-ping shrink-0"></span>
                            <span class="font-bold">{{ $hero['badge'] }}</span>
                            <span class="text-slate-400">·</span>
                            <span class="text-slate-300">Pendaftaran Online</span>
                            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                        </div>

                        <!-- Main H1 Headline -->
                        <h1 class="text-3xl sm:text-5xl lg:text-5.5xl font-black tracking-tight text-slate-100 leading-[1.14]">
                            {{ $hero['title'] }}
                        </h1>

                        @if (!empty($hero['quran_arabic']))
                            <!-- Ayat Al-Qur'an & Makna -->
                            <div class="p-4 sm:p-5 rounded-2xl bg-slate-900 border border-emerald-500/20 shadow-xs">
                                <div class="font-arabic text-xl sm:text-2xl text-emerald-600 dark:text-emerald-400 font-bold leading-loose text-right dir-rtl">
                                    {{ $hero['quran_arabic'] }}
                                </div>
                                <p class="text-xs sm:text-sm text-slate-300 italic mt-2 text-left">
                                    "{{ $hero['quran_translation'] }}"
                                    @if (!empty($hero['quran_surah']))
                                        <span class="font-bold not-italic text-emerald-600 dark:text-emerald-400">({{ $hero['quran_surah'] }})</span>
                                    @endif
                                </p>
                            </div>
                        @endif

                        <!-- Subtitle -->
                        <p class="text-sm sm:text-base lg:text-lg text-slate-300 leading-relaxed font-normal max-w-2xl mx-auto lg:mx-0">
                            {{ $hero['subtitle'] }}
                        </p>

                        <!-- Call To Actions -->
                        <div class="pt-2 flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3.5">
                            <a
                                href="{{ route('ppdb.register') }}"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-7 py-4 rounded-2xl bg-gradient-to-r from-emerald-600 via-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold text-sm sm:text-base shadow-xl shadow-emerald-600/25 hover:shadow-emerald-600/35 transform active:scale-95 transition-all cursor-pointer"
                            >
                                <span>{{ $hero['cta_primary'] }}</span>
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </a>

                            <button
                                type="button"
                                @click="videoModalOpen = true"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-6 py-4 rounded-2xl border border-slate-700 bg-slate-900 hover:bg-slate-800 text-slate-200 font-semibold text-sm sm:text-base shadow-xs transition cursor-pointer"
                            >
                                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-500/15 text-emerald-600 dark:text-emerald-400">
                                    <i data-lucide="play" class="w-3.5 h-3.5 fill-current"></i>
                                </span>
                                <span>{{ $hero['cta_secondary'] ?: 'Video Profil Pesantren' }}</span>
                            </button>
                        </div>

                        <!-- Status & Progress Kuota PPDB Realtime -->
                        <div class="pt-3 max-w-xl mx-auto lg:mx-0">
                            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm">
                                <div class="flex items-center justify-between text-xs sm:text-sm font-semibold mb-2">
                                    <span class="text-slate-200 flex items-center gap-1.5">
                                        <i data-lucide="calendar" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                                        {{ $hero['batch_name'] }}
                                    </span>
                                    <span class="text-emerald-600 dark:text-emerald-400 font-bold">
                                        {{ $hero['batch_quota'] }}
                                    </span>
                                </div>
                                <div class="w-full bg-slate-800 h-2.5 rounded-full overflow-hidden">
                                    <div class="bg-gradient-to-r from-emerald-500 to-teal-400 h-2.5 rounded-full transition-all duration-1000" style="width: {{ $hero['batch_progress'] }}%"></div>
                                </div>
                                <div class="flex justify-between items-center text-[11px] text-slate-400 mt-2">
                                    <span>Kuota Terisi: <strong>{{ $hero['batch_progress'] }}%</strong></span>
                                    <span class="text-emerald-600 dark:text-emerald-400 font-medium">Tes Observasi Tiap Akhir Pekan</span>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- SISI KANAN: Visual Composition Kampus & Floating Pills (5 Cols) -->
                    <div class="lg:col-span-5 relative">
                        <div class="relative mx-auto max-w-md lg:max-w-none">
                            <!-- Background Accent Glow -->
                            <div class="absolute -inset-4 rounded-3xl bg-gradient-to-tr from-emerald-600/25 to-amber-500/15 blur-2xl opacity-70 transform rotate-1"></div>

                            <!-- Frame Gambar Utama (Dignified Islamic Campus Photography) -->
                            <div class="relative rounded-3xl overflow-hidden border-2 border-slate-700 shadow-2xl bg-slate-900 aspect-[4/4.5] group">
                                <img
                                    src="{{ $hero['hero_image'] ?: 'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?auto=format&fit=crop&w=1200&q=80' }}"
                                    alt="Kampus Pondok Pesantren Modern & Madrasah Unggulan"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 filter brightness-95"
                                >
                                <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/25 to-transparent"></div>

                                <!-- Center Video Play Overlay Button -->
                                <button
                                    type="button"
                                    @click="videoModalOpen = true"
                                    class="absolute inset-0 flex items-center justify-center group-hover:scale-110 transition-transform cursor-pointer"
                                    aria-label="Tonton Video Profil Pesantren"
                                >
                                    <div class="relative flex h-16 w-16 sm:h-20 sm:w-20 items-center justify-center rounded-full bg-emerald-600/90 text-white shadow-2xl backdrop-blur-sm ring-4 ring-white/30">
                                        <i data-lucide="play" class="w-7 h-7 sm:w-8 sm:h-8 fill-current translate-x-0.5"></i>
                                    </div>
                                </button>

                                <!-- Caption Bawah Foto -->
                                <div class="absolute bottom-4 left-4 right-4 text-white">
                                    <p class="text-xs uppercase tracking-wider font-bold text-emerald-400">{{ $hero['hero_caption_title'] ?: 'Pondok Pesantren Modern Terpadu' }}</p>
                                    <p class="text-sm font-semibold text-slate-100">{{ $hero['hero_caption_sub'] ?: 'Kombinasi Sempurna Tahfidz Al-Qur\'an, Adab Salaf & Sains Global' }}</p>
                                </div>
                            </div>

                            <!-- Floating Badge 1: Akreditasi A Unggul (Top-Left) -->
                            <div class="absolute -top-3 left-2 sm:-top-4 sm:-left-6 p-2.5 sm:p-3 rounded-2xl bg-slate-900 border border-slate-700 shadow-xl flex items-center gap-2 sm:gap-2.5 transform -rotate-1 hover:rotate-0 transition max-w-[calc(100%-1rem)]">
                                <div class="flex h-9 w-9 sm:h-10 sm:w-10 items-center justify-center rounded-xl bg-amber-500/10 text-amber-500 shrink-0">
                                    <i data-lucide="award" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                                </div>
                                <div class="text-left min-w-0">
                                    <span class="block text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-slate-400 truncate">{{ $hero['badge_1_label'] ?: 'Akreditasi BAN-SM' }}</span>
                                    <span class="block text-xs font-black text-slate-100 truncate">{{ $hero['badge_1_value'] ?: 'Terakreditasi "A" Unggul' }}</span>
                                </div>
                            </div>

                            <!-- Floating Badge 2: Sanad Tahfidz (Bottom-Right) -->
                            <div class="absolute -bottom-3 right-2 sm:-bottom-5 sm:-right-6 p-2.5 sm:p-3 rounded-2xl bg-slate-900 border border-slate-700 shadow-xl flex items-center gap-2 sm:gap-2.5 transform rotate-2 hover:rotate-0 transition max-w-[calc(100%-1rem)]">
                                <div class="flex h-9 w-9 sm:h-10 sm:w-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 shrink-0">
                                    <i data-lucide="book-open-check" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                                </div>
                                <div class="text-left min-w-0">
                                    <span class="block text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-slate-400 truncate">{{ $hero['badge_2_label'] ?: 'Metode Syathibiyyah' }}</span>
                                    <span class="block text-xs font-black text-slate-100 truncate">{{ $hero['badge_2_value'] ?: 'Sanad Tahfidz 30 Juz' }}</span>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- ========================================================================= -->
        <!-- SECTION 2: FLOATING QUICK STATS STRIP                                     -->
        <!-- ========================================================================= -->
        <section class="relative -mt-8 sm:-mt-12 z-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 p-4 sm:p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl">
                @foreach ($stats as $stat)
                    <div class="p-3 sm:p-4 rounded-2xl bg-slate-800/60 border border-slate-700/60 text-center sm:text-left flex flex-col sm:flex-row items-center sm:items-start gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 shrink-0">
                            <i data-lucide="{{ $stat['icon'] ?? 'check-circle' }}" class="w-5 h-5"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="block text-2xl sm:text-3xl font-black text-slate-100 tracking-tight leading-none">
                                {{ $stat['number'] }}
                            </span>
                            <span class="block text-xs font-bold text-slate-200 mt-1 truncate">
                                {{ $stat['label'] }}
                            </span>
                            <span class="block text-[11px] text-slate-400 mt-0.5 truncate">
                                {{ $stat['sub'] }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- ========================================================================= -->
        <!-- SECTION 3: 4 PILAR KEUNGGULAN PENDIDIKAN (BENTO SHOWCASE)                 -->
        <!-- ========================================================================= -->
        <section id="keunggulan" class="py-16 sm:py-24 bg-slate-950">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                <div class="text-center max-w-3xl mx-auto space-y-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 uppercase tracking-wider">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                        {{ $sec['pillars']['badge'] ?: 'Pilar Pendidikan Pesantren' }}
                    </span>
                    <h2 class="text-2.5xl sm:text-4xl font-extrabold text-slate-100 tracking-tight">
                        {{ $sec['pillars']['title'] }}
                    </h2>
                    <p class="text-sm sm:text-base text-slate-300 leading-relaxed">
                        {{ $sec['pillars']['sub'] }}
                    </p>
                </div>

                <!-- Bento Grid Layout -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach ($pillars as $index => $pillar)
                        <div class="relative p-6 sm:p-7 rounded-3xl bg-slate-900 border border-slate-800 shadow-sm hover:shadow-xl transition-all duration-300 group flex flex-col justify-between">
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white shadow-md shadow-emerald-500/20 group-hover:scale-105 transition-transform">
                                        <i data-lucide="{{ $pillar['icon'] ?? 'check' }}" class="w-6 h-6"></i>
                                    </div>
                                    <span class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-slate-800 text-slate-300 border border-slate-700">
                                        {{ $pillar['badge'] }}
                                    </span>
                                </div>

                                <h3 class="text-lg font-bold text-slate-100 group-hover:text-emerald-600 transition-colors">
                                    {{ $pillar['title'] }}
                                </h3>

                                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                                    {{ $pillar['desc'] }}
                                </p>
                            </div>

                            @if (!empty($pillar['points']) && is_array($pillar['points']))
                                <ul class="mt-6 pt-5 border-t border-slate-800 space-y-2 text-xs text-slate-200 font-medium">
                                    @foreach ($pillar['points'] as $pt)
                                        <li class="flex items-center gap-2">
                                            <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-500 shrink-0"></i>
                                            <span>{{ $pt }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ========================================================================= -->
        <!-- SECTION 4: SAMBUTAN MUDIRUL MA'HAD                                        -->
        <!-- ========================================================================= -->
        <section class="py-16 sm:py-24 bg-slate-950 relative overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
                <div class="relative p-6 sm:p-10 lg:p-14 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl overflow-hidden">
                    <!-- Ambient Glow Orbs -->
                    <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full bg-emerald-500/10 dark:bg-emerald-500/5 blur-3xl pointer-events-none"></div>
                    <div class="absolute -bottom-24 -right-24 w-96 h-96 rounded-full bg-amber-500/10 dark:bg-amber-500/5 blur-3xl pointer-events-none"></div>

                    <!-- Background Watermark Arab -->
                    <div class="absolute -right-6 -bottom-6 font-arabic text-8xl sm:text-9xl text-slate-300/30 dark:text-slate-800/40 select-none pointer-events-none font-bold">
                        اقْرَأْ
                    </div>

                    <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-8 sm:gap-12 items-center">
                        <!-- Mudir Photo with Frame -->
                        <div class="lg:col-span-4 flex justify-center">
                            <div class="relative">
                                <div class="absolute -inset-2 rounded-3xl bg-gradient-to-tr from-emerald-600/30 to-amber-500/20 blur-lg opacity-70"></div>
                                <div class="relative w-52 h-52 sm:w-64 sm:h-64 rounded-3xl overflow-hidden border-2 border-emerald-500/30 dark:border-emerald-500/30 shadow-2xl bg-slate-800">
                                    <img
                                        src="{{ $mudir['photo_url'] ?: '/images/mudir-portrait.jpg' }}"
                                        alt="{{ $mudir['name'] }}"
                                        class="w-full h-full object-cover"
                                    >
                                </div>
                                <div class="absolute -bottom-3 left-1/2 -translate-x-1/2 px-3.5 py-1 rounded-full bg-emerald-600 text-white text-[11px] font-bold tracking-wide whitespace-nowrap shadow-md border border-emerald-400/40">
                                    {{ $mudir['title'] ?: 'Mudirul Ma\'had' }}
                                </div>
                            </div>
                        </div>

                        <!-- Mudir Statement & Profile -->
                        <div class="lg:col-span-8 space-y-6 text-center lg:text-left">
                            <div class="space-y-1.5">
                                <span class="inline-flex items-center gap-1.5 text-xs uppercase tracking-widest text-emerald-600 dark:text-emerald-400 font-bold">
                                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                                    {{ $mudir['badge'] ?: 'Nasihat & Amanat Mudirul Ma\'had' }}
                                </span>
                                <h3 class="text-xl sm:text-2xl lg:text-3xl font-black text-slate-100 tracking-tight leading-snug">
                                    "{{ $mudir['headline'] }}"
                                </h3>
                            </div>

                            <div class="relative pl-0 lg:pl-3">
                                <i data-lucide="quote" class="w-10 h-10 text-emerald-500/20 absolute -top-4 -left-4 -z-0"></i>
                                <p class="text-base sm:text-lg text-slate-200 leading-relaxed font-normal italic relative z-10">
                                    "{{ $mudir['quote'] }}"
                                </p>
                            </div>

                            <div class="pt-5 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
                                <div class="text-center sm:text-left">
                                    <h4 class="text-lg font-bold text-slate-100">{{ $mudir['name'] }}</h4>
                                    <p class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold">{{ $mudir['title'] }}</p>
                                    <p class="text-xs text-slate-400 mt-0.5">{{ $mudir['credentials'] }}</p>
                                </div>

                                <div class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-800 text-xs text-slate-200 border border-slate-700 shadow-xs">
                                    <i data-lucide="award" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                                    <span class="font-medium">Sanad Keilmuan Bersambung (Mu'tabar)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========================================================================= -->
        <!-- SECTION 5: PROGRAM AKADEMIK PENDIDIKAN                                    -->
        <!-- ========================================================================= -->
        <section id="program" class="py-16 sm:py-24 bg-slate-950">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                <div class="text-center max-w-3xl mx-auto space-y-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 uppercase tracking-wider">
                        <i data-lucide="graduation-cap" class="w-3.5 h-3.5"></i>
                        {{ $sec['programs']['badge'] ?: 'Jenjang & Kurikulum Pendidikan' }}
                    </span>
                    <h2 class="text-2.5xl sm:text-4xl font-extrabold text-slate-100 tracking-tight">
                        {{ $sec['programs']['title'] }}
                    </h2>
                    <p class="text-sm sm:text-base text-slate-300 leading-relaxed">
                        {{ $sec['programs']['sub'] }}
                    </p>
                </div>

                <div x-data="{ activeProg: 'mts' }" class="space-y-8">
                    <!-- Segmented Program Switcher: Responsive 4-Column Grid on Desktop, Balanced 2x2 Grid on Mobile -->
                    <div class="max-w-5xl mx-auto p-1.5 sm:p-2 rounded-2xl bg-slate-900/90 border border-slate-800 shadow-md">
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-1.5 sm:gap-2">
                            @foreach ($programs as $prog)
                                <button
                                    type="button"
                                    @click="activeProg = '{{ $prog['id'] }}'"
                                    :class="activeProg === '{{ $prog['id'] }}'
                                        ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-900/30 ring-1 ring-emerald-400/60 font-bold'
                                        : 'bg-slate-950/40 hover:bg-slate-800/80 text-slate-300 hover:text-white font-medium border border-slate-800/60'"
                                    class="flex flex-col items-center justify-center text-center p-3 sm:py-3.5 sm:px-4 rounded-xl transition-all duration-200 cursor-pointer min-h-[52px] group"
                                >
                                    <span class="text-xs sm:text-sm leading-snug">
                                        {{ $prog['name'] }}
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Program Content Cards -->
                    @foreach ($programs as $prog)
                        <div
                            x-show="activeProg === '{{ $prog['id'] }}'"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-y-3"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            class="p-6 sm:p-10 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-8"
                        >
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-800 pb-6">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                            {{ $prog['badge'] }}
                                        </span>
                                        <span class="text-xs text-slate-400 font-medium">
                                            {{ $prog['level'] }}
                                        </span>
                                    </div>
                                    <h3 class="text-2xl sm:text-3xl font-black text-slate-100 mt-2">
                                        {{ $prog['name'] }}
                                    </h3>
                                    <p class="text-xs sm:text-sm text-slate-400 mt-1">
                                        {{ $prog['age'] }}
                                    </p>
                                </div>

                                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 sm:gap-3 w-full md:w-auto">
                                    <a
                                        href="{{ route('ppdb.register') }}"
                                        class="w-full sm:w-auto text-center px-5 py-2.5 sm:py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs sm:text-sm shadow-md shadow-emerald-900/20 transition cursor-pointer"
                                    >
                                        Daftar Jenjang Ini
                                    </a>
                                    <a
                                        href="https://wa.me/{{ $cleanPhone }}?text={{ urlencode('Assalamu\'alaikum Panitia PPDB, saya ingin bertanya rincian jenjang ' . $prog['name']) }}"
                                        target="_blank"
                                        class="w-full sm:w-auto text-center px-5 py-2.5 sm:py-3 rounded-xl border border-slate-700 hover:bg-slate-800 text-slate-300 font-semibold text-xs sm:text-sm transition cursor-pointer"
                                    >
                                        Konsultasi WhatsApp
                                    </a>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                                <div class="lg:col-span-7 space-y-4">
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Deskripsi & Fokus Kurikulum</h4>
                                    <p class="text-sm sm:text-base text-slate-300 leading-relaxed">
                                        {{ $prog['desc'] }}
                                    </p>
                                    <div class="p-4 rounded-2xl bg-slate-800/80 border border-slate-700 text-xs sm:text-sm text-slate-200 italic border-l-4 border-l-emerald-500">
                                        "{{ $prog['quote'] }}"
                                    </div>
                                </div>

                                <div class="lg:col-span-5 space-y-4">
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Keunggulan & Capaian Lulusan</h4>
                                    @if (!empty($prog['features']) && is_array($prog['features']))
                                        <ul class="space-y-3">
                                            @foreach ($prog['features'] as $feat)
                                                <li class="flex items-start gap-3 p-3 rounded-xl bg-slate-800/60 border border-slate-700/60 text-xs sm:text-sm text-slate-200">
                                                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5"></i>
                                                    <span>{{ $feat }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ========================================================================= -->
        <!-- SECTION 6: INTERAKTIF: SIMULASI JALUR MASUK & BEASISWA PPDB              -->
        <!-- ========================================================================= -->
        <section class="py-16 sm:py-20 bg-slate-900 border-y border-slate-800 text-slate-100 relative overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10 relative z-10">
                <div class="text-center max-w-2xl mx-auto space-y-2">
                    <span class="text-xs uppercase font-bold tracking-widest text-emerald-600 dark:text-emerald-400">
                        {{ $sec['tracks']['badge'] ?: 'Seleksi Santri Baru' }}
                    </span>
                    <h2 class="text-2.5xl sm:text-3.5xl font-black text-slate-100">
                        {{ $sec['tracks']['title'] }}
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-400 font-normal">
                        {{ $sec['tracks']['sub'] }}
                    </p>
                </div>

                <!-- Track Selector Cards Dinamis dari CMS -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach ($tracks as $index => $t)
                        @php
                            $trackId = $t['id'] ?? ('track_' . $index);
                            $isFeatured = !empty($t['featured']);
                            $iconName = match ($index) {
                                0 => 'book-open',
                                1 => 'award',
                                default => 'users',
                            };
                        @endphp
                        <div
                            @click="selectedTrack = '{{ $trackId }}'"
                            :class="selectedTrack === '{{ $trackId }}' ? 'ring-2 ring-emerald-500 bg-slate-800/90 shadow-xl border-emerald-500/40' : 'bg-slate-800/40 hover:bg-slate-800/70 border-slate-700'"
                            class="p-6 rounded-3xl border transition-all cursor-pointer space-y-4 flex flex-col justify-between"
                        >
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span @class([
                                        'px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider',
                                        'bg-emerald-600 text-white shadow-xs' => $isFeatured,
                                        'bg-slate-700 text-slate-200' => ! $isFeatured,
                                    ])>
                                        {{ ($t['highlight'] ?? null) ?: ($t['badge'] ?? '') }}
                                    </span>
                                    <i data-lucide="{{ $iconName }}" class="w-5 h-5 text-emerald-500"></i>
                                </div>
                                <div class="flex items-baseline justify-between gap-2">
                                    <h3 class="text-lg font-bold text-slate-100">{{ $t['name'] ?? '' }}</h3>
                                    <span class="text-xs font-mono text-emerald-400 font-semibold">{{ $t['quota'] ?? '' }}</span>
                                </div>
                                <p class="text-xs text-slate-300 leading-relaxed">
                                    {{ $t['desc'] ?? '' }}
                                </p>
                                @if (!empty($t['benefits']) && is_array($t['benefits']))
                                    <ul class="text-xs text-slate-200 space-y-1.5 pt-2">
                                        @foreach ($t['benefits'] as $benefit)
                                            <li class="flex items-center gap-2">
                                                <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-500 shrink-0"></i>
                                                <span>{{ $benefit }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                            <a
                                href="{{ route('ppdb.register') }}"
                                @class([
                                    'w-full text-center py-2.5 rounded-xl font-bold text-xs transition block',
                                    'bg-emerald-600 hover:bg-emerald-500 text-white shadow-md' => $isFeatured,
                                    'bg-slate-700 hover:bg-slate-600 text-slate-100' => ! $isFeatured,
                                ])
                            >
                                {{ ($t['cta_text'] ?? null) ?: ($t['cta'] ?? 'Pilih Jalur Masuk') }}
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ========================================================================= -->
        <!-- SECTION 7: 5 TAHAPAN TERSTRUKTUR TAHFIDZ MUTQIN BERSANAD                  -->
        <!-- ========================================================================= -->
        <section id="tahfidz" class="py-16 sm:py-24 bg-slate-950">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                <div class="text-center max-w-3xl mx-auto space-y-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 uppercase tracking-wider">
                        <i data-lucide="book-open" class="w-3.5 h-3.5"></i>
                        {{ $sec['tahfidz']['badge'] ?: 'Tahapan Hifdzil Qur\'an' }}
                    </span>
                    <h2 class="text-2.5xl sm:text-4xl font-extrabold text-slate-100 tracking-tight">
                        {{ $sec['tahfidz']['title'] }}
                    </h2>
                    <p class="text-sm sm:text-base text-slate-300 leading-relaxed">
                        {{ $sec['tahfidz']['sub'] }}
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                    @foreach ($tahfidzSteps as $step)
                        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-xs hover:border-emerald-500/50 transition-all flex flex-col justify-between space-y-4">
                            <div class="space-y-3">
                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-600 text-white font-black text-xs">
                                    {{ $step['step'] }}
                                </span>
                                <h3 class="text-sm font-bold text-slate-100">
                                    {{ $step['title'] }}
                                </h3>
                                <p class="text-xs text-slate-300 leading-relaxed">
                                    {{ $step['desc'] }}
                                </p>
                            </div>
                            <div class="pt-3 border-t border-slate-800">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Target:</span>
                                <span class="block text-xs font-semibold text-slate-200 mt-0.5">{{ $step['target'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ========================================================================= -->
        <!-- SECTION 8: 24 JAM RITME KEHIDUPAN ASRAMA & DISIPLIN SANTRI                -->
        <!-- ========================================================================= -->
        <section id="keasramaan" class="py-16 sm:py-24 bg-slate-950">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                <div class="text-center max-w-3xl mx-auto space-y-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 uppercase tracking-wider">
                        <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                        {{ $sec['schedule']['badge'] ?: 'Kehidupan Asrama' }}
                    </span>
                    <h2 class="text-2.5xl sm:text-4xl font-extrabold text-slate-100 tracking-tight">
                        {{ $sec['schedule']['title'] }}
                    </h2>
                    <p class="text-sm sm:text-base text-slate-300 leading-relaxed">
                        {{ $sec['schedule']['sub'] }}
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach ($dailySchedules as $index => $sch)
                        <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-sm space-y-4 flex flex-col justify-between">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                        {{ $sch['period'] }}
                                    </span>
                                    <span class="text-[11px] font-mono font-semibold text-slate-400">
                                        {{ $sch['time'] }}
                                    </span>
                                </div>
                                <h3 class="text-base font-bold text-slate-100">
                                    {{ $sch['title'] }}
                                </h3>
                                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                                    {{ $sch['desc'] }}
                                </p>
                            </div>

                            <div class="pt-3 border-t border-slate-800 flex items-center gap-2 text-xs text-emerald-600 dark:text-emerald-400 font-semibold">
                                <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                <span>Terpantau Musyrif Asrama</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ========================================================================= -->
        <!-- SECTION 9: FASILITAS KAMPUS & ASRAMA SANTRI                               -->
        <!-- ========================================================================= -->
        <section id="fasilitas" class="py-16 sm:py-24 bg-slate-950">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                <div class="text-center max-w-3xl mx-auto space-y-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 uppercase tracking-wider">
                        <i data-lucide="building-2" class="w-3.5 h-3.5"></i>
                        {{ $sec['facilities']['badge'] ?: 'Sarana & Prasarana' }}
                    </span>
                    <h2 class="text-2.5xl sm:text-4xl font-extrabold text-slate-100 tracking-tight">
                        {{ $sec['facilities']['title'] }}
                    </h2>
                    <p class="text-sm sm:text-base text-slate-300 leading-relaxed">
                        {{ $sec['facilities']['sub'] }}
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach ($facilities as $fac)
                        <div class="group rounded-3xl overflow-hidden bg-slate-900 border border-slate-800 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col">
                            <div class="relative aspect-[16/10] overflow-hidden bg-slate-800">
                                <img
                                    src="{{ $fac['image'] }}"
                                    alt="{{ $fac['name'] }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 filter brightness-95"
                                >
                                <div class="absolute top-3 left-3 px-2.5 py-1 rounded-full bg-slate-900/80 backdrop-blur-md text-[10px] font-bold text-white border border-white/20">
                                    {{ $fac['category'] }}
                                </div>
                            </div>
                            <div class="p-5 flex-1 flex flex-col justify-between space-y-3">
                                <div>
                                    <h3 class="text-base font-bold text-slate-100 group-hover:text-emerald-600 transition-colors">
                                        {{ $fac['name'] }}
                                    </h3>
                                    <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                                        {{ $fac['desc'] }}
                                    </p>
                                </div>
                                <div class="pt-3 border-t border-slate-800 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">
                                    {{ $fac['tag'] }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ========================================================================= -->
        <!-- SECTION 10: PRESTASI & PENGHARGAAN SANTRI                                 -->
        <!-- ========================================================================= -->
        <section id="prestasi" class="py-16 sm:py-24 bg-slate-950">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                <div class="text-center max-w-3xl mx-auto space-y-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 uppercase tracking-wider">
                        <i data-lucide="award" class="w-3.5 h-3.5"></i>
                        {{ $sec['achievements']['badge'] ?: 'Prestasi Santri' }}
                    </span>
                    <h2 class="text-2.5xl sm:text-4xl font-extrabold text-slate-100 tracking-tight">
                        {{ $sec['achievements']['title'] }}
                    </h2>
                    <p class="text-sm sm:text-base text-slate-300 leading-relaxed">
                        {{ $sec['achievements']['sub'] }}
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($achievements as $ach)
                        <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-sm space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-black bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/30">
                                    {{ $ach['year'] }}
                                </span>
                                <span class="text-[11px] text-slate-400 font-medium">{{ $ach['category'] }}</span>
                            </div>
                            <h3 class="text-base font-bold text-slate-100 leading-snug">
                                {{ $ach['title'] }}
                            </h3>
                            <p class="text-xs text-slate-300">
                                {{ $ach['event'] }}
                            </p>
                            <div class="pt-3 border-t border-slate-800 flex items-center gap-2 text-xs font-semibold text-slate-200">
                                <i data-lucide="user" class="w-3.5 h-3.5 text-emerald-500"></i>
                                <span>{{ $ach['student'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ========================================================================= -->
        <!-- SECTION 11: KESAN & AMANAH WALI SANTRI & ALUMNI                           -->
        <!-- ========================================================================= -->
        <section id="testimoni" class="py-16 sm:py-24 bg-slate-950">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                <div class="text-center max-w-3xl mx-auto space-y-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 uppercase tracking-wider">
                        <i data-lucide="message-square" class="w-3.5 h-3.5"></i>
                        {{ $sec['testimonials']['badge'] ?: 'Kesan & Amanah Wali' }}
                    </span>
                    <h2 class="text-2.5xl sm:text-4xl font-extrabold text-slate-100 tracking-tight">
                        {{ $sec['testimonials']['title'] }}
                    </h2>
                    <p class="text-sm sm:text-base text-slate-300 leading-relaxed">
                        {{ $sec['testimonials']['sub'] }}
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach ($testimonials as $testi)
                        <div class="p-6 sm:p-8 rounded-3xl bg-slate-900 border border-slate-800 shadow-sm space-y-4 flex flex-col justify-between">
                            <div class="space-y-3">
                                <div class="flex items-center gap-1 text-amber-400">
                                    @for ($i = 0; $i < ($testi['rating'] ?? 5); $i++)
                                        <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                                    @endfor
                                </div>
                                <p class="text-xs sm:text-sm text-slate-200 leading-relaxed italic">
                                    "{{ $testi['quote'] }}"
                                </p>
                            </div>

                            <div class="pt-4 border-t border-slate-800 flex items-center gap-3">
                                <img
                                    src="{{ $testi['avatar'] }}"
                                    alt="{{ $testi['name'] }}"
                                    class="w-12 h-12 rounded-full object-cover border border-emerald-500/40 shrink-0"
                                >
                                <div>
                                    <h4 class="text-sm font-bold text-slate-100">{{ $testi['name'] }}</h4>
                                    <p class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">{{ $testi['role'] }}</p>
                                    <p class="text-[11px] text-slate-400">{{ $testi['child'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ========================================================================= -->
        <!-- SECTION 12: AGENDA & KEGIATAN MA'HAD                                      -->
        <!-- ========================================================================= -->
        <section id="agenda" class="py-16 sm:py-20 bg-slate-950">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                    <div class="space-y-2">
                        <span class="text-xs uppercase font-bold tracking-wider text-emerald-600 dark:text-emerald-400">
                            {{ $sec['agenda']['badge'] ?: 'Agenda & Syiar Ma\'had' }}
                        </span>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-100">
                            {{ $sec['agenda']['title'] }}
                        </h2>
                    </div>
                    <a href="{{ route('ppdb.register') }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-bold text-emerald-600 dark:text-emerald-400 hover:underline">
                        <span>Jadwal & Agenda Lengkap</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach ($agenda as $event)
                        <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-sm space-y-3">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                {{ $event['badge'] ?? 'Agenda' }}
                            </span>
                            <h3 class="text-base font-bold text-slate-100 leading-snug">
                                {{ $event['title'] }}
                            </h3>
                            <div class="flex items-center gap-2 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                <i data-lucide="calendar" class="w-4 h-4 shrink-0"></i>
                                <span>{{ $event['date'] }}</span>
                            </div>
                            <p class="text-xs text-slate-300 leading-relaxed">
                                {{ $event['desc'] }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ========================================================================= -->
        <!-- SECTION 13: TANYA JAWAB (FAQ ACCORDION)                                   -->
        <!-- ========================================================================= -->
        <section id="faq" class="py-16 sm:py-24 bg-slate-950">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
                <div class="text-center space-y-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 uppercase tracking-wider">
                        <i data-lucide="help-circle" class="w-3.5 h-3.5"></i>
                        {{ $sec['faq']['badge'] ?: 'Pertanyaan Umum' }}
                    </span>
                    <h2 class="text-2.5xl sm:text-4xl font-extrabold text-slate-100 tracking-tight">
                        {{ $sec['faq']['title'] }}
                    </h2>
                    <p class="text-sm sm:text-base text-slate-300">
                        {{ $sec['faq']['sub'] }}
                    </p>
                </div>

                <div x-data="{ openFaq: null }" class="space-y-3.5">
                    @foreach ($faqs as $index => $faq)
                        <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden">
                            <button
                                type="button"
                                @click="openFaq = (openFaq === {{ $index }} ? null : {{ $index }})"
                                class="w-full flex items-center justify-between p-4 sm:p-5 text-left text-xs sm:text-sm font-bold text-slate-100 hover:text-emerald-600 transition cursor-pointer"
                            >
                                <span class="pr-4">{{ $faq['q'] }}</span>
                                <i
                                    data-lucide="chevron-down"
                                    class="w-4 h-4 text-slate-400 shrink-0 transition-transform duration-200"
                                    :class="openFaq === {{ $index }} ? 'rotate-180 text-emerald-500' : ''"
                                ></i>
                            </button>
                            <div
                                x-show="openFaq === {{ $index }}"
                                x-collapse
                                class="px-4 pb-5 sm:px-5 sm:pb-6 text-xs sm:text-sm text-slate-300 leading-relaxed border-t border-slate-800 pt-3"
                            >
                                {{ $faq['a'] }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ========================================================================= -->
        <!-- SECTION 14: PENDAFTARAN & KONSULTASI SANTRI BARU (CTA)                    -->
        <!-- ========================================================================= -->
        <section class="py-16 sm:py-24 bg-gradient-to-tr from-emerald-900 via-emerald-800 to-teal-900 text-white relative overflow-hidden">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-6 relative z-10">
                <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-amber-400 text-slate-950 font-bold text-xs uppercase tracking-wider">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                    {{ $cta['badge'] }}
                </span>
                <h2 class="text-3xl sm:text-5xl font-black text-white tracking-tight leading-tight">
                    {{ $cta['title'] }}
                </h2>
                <p class="text-sm sm:text-base text-emerald-100 max-w-2xl mx-auto leading-relaxed">
                    {{ $cta['desc'] }}
                </p>

                <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a
                        href="{{ route('ppdb.register') }}"
                        class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-white text-emerald-900 hover:bg-slate-100 font-extrabold text-sm sm:text-base shadow-xl transition cursor-pointer"
                    >
                        {{ $cta['primary_text'] ?: 'Daftar Calon Santri Sekarang' }}
                    </a>
                    <a
                        href="https://wa.me/{{ $cleanPhone }}?text={{ urlencode('Assalamu\'alaikum Panitia PPDB, saya ingin bertanya tentang informasi pendaftaran santri baru.') }}"
                        target="_blank"
                        class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-emerald-700/80 hover:bg-emerald-700 text-white border border-emerald-500/50 font-bold text-sm sm:text-base transition cursor-pointer flex items-center justify-center gap-2"
                    >
                        <i data-lucide="message-circle" class="w-4 h-4"></i>
                        <span>{{ $cta['whatsapp_text'] ?: 'Konsultasi Panitia via WhatsApp' }}</span>
                    </a>
                </div>
            </div>
        </section>

        <!-- ========================================================================= -->
        <!-- LIGHTBOX MODAL: CAMPUS TOUR VIDEO                                         -->
        <!-- ========================================================================= -->
        <div
            x-show="videoModalOpen"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md"
            @keydown.escape.window="videoModalOpen = false"
        >
            <div
                @click.away="videoModalOpen = false"
                class="relative w-full max-w-4xl bg-slate-900 rounded-3xl overflow-hidden shadow-2xl border border-slate-800"
            >
                <div class="flex items-center justify-between p-4 border-b border-slate-800 text-white">
                    <span class="text-sm font-bold flex items-center gap-2">
                        <i data-lucide="play" class="w-4 h-4 text-emerald-400"></i>
                        Tur Kampus & Profil Pesantren
                    </span>
                    <button type="button" @click="videoModalOpen = false" class="text-slate-400 hover:text-white transition cursor-pointer">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                <div class="aspect-video w-full bg-black">
                    <iframe
                        class="w-full h-full"
                        src="{{ $videoEmbed }}"
                        title="Tur Kampus Video"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen
                    ></iframe>
                </div>
            </div>
        </div>

    </div>

</x-layouts.public>
