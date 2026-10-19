@php
    $tabs = [
        'hero' => ['label' => 'Hero & Banner', 'icon' => 'sparkles', 'hint' => 'Headline, PPDB, Ayat & Media'],
        'mudir' => ['label' => 'Sambutan Mudir', 'icon' => 'user-check', 'hint' => 'Profil & pesan pimpinan'],
        'stats' => ['label' => 'Statistik', 'icon' => 'bar-chart-3', 'hint' => 'Angka & metrik capaian'],
        'pillars' => ['label' => 'Pilar Keunggulan', 'icon' => 'award', 'hint' => 'Nilai inti pendidikan'],
        'programs' => ['label' => 'Program Akademik', 'icon' => 'graduation-cap', 'hint' => 'Jenjang MTs, MA & Turats'],
        'tracks' => ['label' => 'Jalur Masuk PPDB', 'icon' => 'layers', 'hint' => 'Jalur seleksi & beasiswa'],
        'tahfidz_schedule' => ['label' => 'Tahfidz & Asrama', 'icon' => 'book-open', 'hint' => 'Tahapan tahfidz & jadwal 24 jam'],
        'facilities' => ['label' => 'Fasilitas Kampus', 'icon' => 'building-2', 'hint' => 'Sarana & laboratorium'],
        'social_proof' => ['label' => 'Prestasi & Testimoni', 'icon' => 'award', 'hint' => 'Prestasi, testimoni, agenda'],
        'faq_contact' => ['label' => 'FAQ & Kontak', 'icon' => 'help-circle', 'hint' => 'Tanya jawab & medsos'],
        'headings_cta' => ['label' => 'Judul Seksi & CTA', 'icon' => 'type', 'hint' => 'Semua judul & banner penutup'],
    ];
@endphp

<div class="space-y-4 sm:space-y-6">
    <!-- Header Banner CMS -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-4 sm:p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm">
        <div>
            <h2 class="text-base sm:text-lg font-bold text-slate-100 flex items-center gap-2">
                <i data-lucide="layout-template" class="w-5 h-5 text-emerald-500"></i>
                CMS Landing Page Lengkap
            </h2>
            <p class="text-xs sm:text-sm text-slate-400 mt-1 max-w-2xl">
                Kelola 100% seluruh konten yang tampil di halaman utama publik: Hero banner, sambutan mudir, pilar keunggulan, program akademik, tahfidz, asrama, fasilitas, testimoni, hingga kontak dan FAQ.
            </p>
        </div>

        <a
            href="{{ url('/') }}"
            target="_blank"
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs sm:text-sm shadow-md shadow-emerald-950/50 transition shrink-0"
        >
            <span>Lihat Website Live</span>
            <i data-lucide="external-link" class="w-4 h-4"></i>
        </a>
    </div>

    {{-- Tab Navigation Bar --}}
    <div role="tablist" aria-label="{{ __('Bagian CMS Landing Page') }}"
        class="flex gap-1.5 p-1.5 rounded-2xl bg-slate-900 border border-slate-800 overflow-x-auto custom-scrollbar">
        @foreach ($tabs as $key => $item)
            <button type="button" role="tab" wire:click="$set('tab', '{{ $key }}')"
                aria-selected="{{ $tab === $key ? 'true' : 'false' }}"
                @class([
                    'flex-1 min-w-[9rem] flex items-center gap-2 px-3 py-2 rounded-xl text-left min-h-[44px] transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 cursor-pointer',
                    'bg-slate-800 text-slate-100 shadow-xs border border-emerald-500/30' => $tab === $key,
                    'text-slate-400 hover:text-slate-200 hover:bg-slate-800/40' => $tab !== $key,
                ])>
                <i data-lucide="{{ $item['icon'] }}" @class(['w-4 h-4 shrink-0', 'text-emerald-400' => $tab === $key])></i>
                <span class="min-w-0">
                    <span class="block text-xs font-semibold leading-tight truncate">{{ $item['label'] }}</span>
                    <span class="hidden sm:block text-[10px] text-slate-400 truncate mt-0.5">{{ $item['hint'] }}</span>
                </span>
            </button>
        @endforeach
    </div>

    {{-- ==================== TAB 1: HERO & BANNER ==================== --}}
    @if ($tab === 'hero')
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6 items-start" wire:key="tab-hero">
            <form wire:submit="saveHero" class="lg:col-span-8 bg-slate-900 border border-slate-800 rounded-2xl p-5 sm:p-6 space-y-5">
                <div>
                    <h3 class="font-bold text-sm text-slate-100 flex items-center gap-2">
                        <i data-lucide="sparkles" class="w-4 h-4 text-emerald-400"></i>
                        Hero Headline & Banner PPDB
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Teks utama, judul H1, dan banner kuota santri baru yang langsung menyambut wali santri.</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <x-input-label for="landing_announcement_text" value="Teks Bar Pengumuman Atas (Top Announcement Bar) *" />
                        <x-text-input wire:model="landing_announcement_text" id="landing_announcement_text" type="text" class="w-full mt-1" />
                        <x-input-error :messages="$errors->get('landing_announcement_text')" class="mt-1" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="landing_hero_badge" value="Badge Status PPDB *" />
                            <x-text-input wire:model="landing_hero_badge" id="landing_hero_badge" type="text" class="w-full mt-1" />
                            <x-input-error :messages="$errors->get('landing_hero_badge')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="landing_hero_cta_primary" value="Label Tombol Utama (CTA 1) *" />
                            <x-text-input wire:model="landing_hero_cta_primary" id="landing_hero_cta_primary" type="text" class="w-full mt-1" />
                            <x-input-error :messages="$errors->get('landing_hero_cta_primary')" class="mt-1" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="landing_hero_title" value="Judul Utama Headline (H1) *" />
                        <x-text-input wire:model="landing_hero_title" id="landing_hero_title" type="text" class="w-full mt-1" />
                        <x-input-error :messages="$errors->get('landing_hero_title')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="landing_hero_subtitle" value="Deskripsi Singkat / Subheadline *" />
                        <x-textarea wire:model="landing_hero_subtitle" id="landing_hero_subtitle" rows="3" class="w-full mt-1 text-xs sm:text-sm" />
                        <x-input-error :messages="$errors->get('landing_hero_subtitle')" class="mt-1" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="landing_hero_cta_secondary" value="Label Tombol Sekunder (CTA 2)" />
                            <x-text-input wire:model="landing_hero_cta_secondary" id="landing_hero_cta_secondary" type="text" class="w-full mt-1" />
                            <x-input-error :messages="$errors->get('landing_hero_cta_secondary')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="landing_video_url" value="URL Video Profil / Tour Kampus (YouTube)" />
                            <x-text-input wire:model="landing_video_url" id="landing_video_url" type="text" class="w-full mt-1" />
                            <x-input-error :messages="$errors->get('landing_video_url')" class="mt-1" />
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-800 space-y-4">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-400">Ayat Suci Al-Qur'an (Kutipan Inspiratif Hero)</h4>
                        <div>
                            <x-input-label for="landing_hero_quran_arabic" value="Teks Ayat Al-Qur'an (Bahasa Arab)" />
                            <x-textarea wire:model="landing_hero_quran_arabic" id="landing_hero_quran_arabic" rows="2" dir="rtl" class="w-full mt-1 text-right font-serif text-sm leading-relaxed" />
                            <x-input-error :messages="$errors->get('landing_hero_quran_arabic')" class="mt-1" />
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="sm:col-span-2">
                                <x-input-label for="landing_hero_quran_translation" value="Terjemahan Bahasa Indonesia" />
                                <x-text-input wire:model="landing_hero_quran_translation" id="landing_hero_quran_translation" type="text" class="w-full mt-1" />
                                <x-input-error :messages="$errors->get('landing_hero_quran_translation')" class="mt-1" />
                            </div>
                            <div>
                                <x-input-label for="landing_hero_quran_surah" value="Nama Surat & Ayat" />
                                <x-text-input wire:model="landing_hero_quran_surah" id="landing_hero_quran_surah" type="text" placeholder="QS. Al-Mujadilah: 11" class="w-full mt-1" />
                                <x-input-error :messages="$errors->get('landing_hero_quran_surah')" class="mt-1" />
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-800 space-y-4">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-400">Foto Utama Hero & Floating Badge</h4>
                        <div>
                            <x-input-label for="landing_hero_image" value="URL Foto Gedung / Aktivitas Santri Utama" />
                            <x-text-input wire:model="landing_hero_image" id="landing_hero_image" type="text" class="w-full mt-1" />
                            <x-input-error :messages="$errors->get('landing_hero_image')" class="mt-1" />
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="landing_hero_caption_title" value="Judul Kapsul Foto (Pojok Bawah)" />
                                <x-text-input wire:model="landing_hero_caption_title" id="landing_hero_caption_title" type="text" class="w-full mt-1" />
                                <x-input-error :messages="$errors->get('landing_hero_caption_title')" class="mt-1" />
                            </div>
                            <div>
                                <x-input-label for="landing_hero_caption_sub" value="Sub Judul Kapsul Foto" />
                                <x-text-input wire:model="landing_hero_caption_sub" id="landing_hero_caption_sub" type="text" class="w-full mt-1" />
                                <x-input-error :messages="$errors->get('landing_hero_caption_sub')" class="mt-1" />
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 space-y-2">
                                <span class="text-[11px] font-bold text-slate-300">Floating Badge Kiri (1)</span>
                                <div>
                                    <x-input-label for="landing_hero_badge_1_label" value="Label Atas" />
                                    <x-text-input wire:model="landing_hero_badge_1_label" id="landing_hero_badge_1_label" type="text" class="w-full mt-1 text-xs" />
                                </div>
                                <div>
                                    <x-input-label for="landing_hero_badge_1_value" value="Nilai / Status Utama" />
                                    <x-text-input wire:model="landing_hero_badge_1_value" id="landing_hero_badge_1_value" type="text" class="w-full mt-1 text-xs" />
                                </div>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 space-y-2">
                                <span class="text-[11px] font-bold text-slate-300">Floating Badge Kanan (2)</span>
                                <div>
                                    <x-input-label for="landing_hero_badge_2_label" value="Label Atas" />
                                    <x-text-input wire:model="landing_hero_badge_2_label" id="landing_hero_badge_2_label" type="text" class="w-full mt-1 text-xs" />
                                </div>
                                <div>
                                    <x-input-label for="landing_hero_badge_2_value" value="Nilai / Status Utama" />
                                    <x-text-input wire:model="landing_hero_badge_2_value" id="landing_hero_badge_2_value" type="text" class="w-full mt-1 text-xs" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-800 space-y-4">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-400">Status & Kuota Gelombang PPDB</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <x-input-label for="landing_batch_name" value="Nama Gelombang *" />
                                <x-text-input wire:model="landing_batch_name" id="landing_batch_name" type="text" class="w-full mt-1" />
                                <x-input-error :messages="$errors->get('landing_batch_name')" class="mt-1" />
                            </div>
                            <div>
                                <x-input-label for="landing_batch_quota" value="Keterangan Kuota *" />
                                <x-text-input wire:model="landing_batch_quota" id="landing_batch_quota" type="text" class="w-full mt-1" />
                                <x-input-error :messages="$errors->get('landing_batch_quota')" class="mt-1" />
                            </div>
                            <div>
                                <x-input-label for="landing_batch_progress" value="Persentase Terisi (0-100)% *" />
                                <x-text-input wire:model="landing_batch_progress" id="landing_batch_progress" type="number" min="0" max="100" class="w-full mt-1" />
                                <x-input-error :messages="$errors->get('landing_batch_progress')" class="mt-1" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-4 flex justify-end">
                    <x-primary-button type="submit" wire:loading.attr="disabled">
                        <x-loading-label target="saveHero" label="Simpan Hero & Banner" busy="Menyimpan..." />
                    </x-primary-button>
                </div>
            </form>

            <div class="lg:col-span-4 space-y-4">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-400 flex items-center gap-1.5">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                        Preview Cepat Hero
                    </h4>
                    <div class="p-4 rounded-xl bg-slate-950 border border-slate-800/80 space-y-3 text-left">
                        <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                            {{ $landing_hero_badge ?: 'PPDB Dibuka' }}
                        </span>
                        <p class="text-sm font-extrabold text-slate-100 leading-snug line-clamp-3">
                            {{ $landing_hero_title ?: 'Judul Utama Website' }}
                        </p>
                        <p class="text-xs text-slate-400 line-clamp-3 leading-relaxed">
                            {{ $landing_hero_subtitle ?: 'Deskripsi singkat mengenai keunggulan lembaga...' }}
                        </p>
                        <div class="pt-2 border-t border-slate-800">
                            <div class="flex justify-between text-[11px] mb-1">
                                <span class="text-slate-300 font-semibold">{{ $landing_batch_name }}</span>
                                <span class="text-amber-400 font-bold">{{ $landing_batch_quota }}</span>
                            </div>
                            <div class="w-full bg-slate-800 h-2 rounded-full overflow-hidden">
                                <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ min(100, max(0, (int) $landing_batch_progress)) }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ==================== TAB 2: SAMBUTAN MUDIR ==================== --}}
    @if ($tab === 'mudir')
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6 items-start" wire:key="tab-mudir">
            <form wire:submit="saveMudir" class="lg:col-span-8 bg-slate-900 border border-slate-800 rounded-2xl p-5 sm:p-6 space-y-5">
                <div>
                    <h3 class="font-bold text-sm text-slate-100 flex items-center gap-2">
                        <i data-lucide="user-check" class="w-4 h-4 text-emerald-400"></i>
                        Profil & Sambutan Mudirul Ma'had
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Identitas pengasuh/pimpinan pondok, sanad keilmuan, dan pesan filosofi pendidikan.</p>
                </div>

                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="landing_mudir_name" value="Nama Lengkap & Gelar Pimpinan *" />
                            <x-text-input wire:model="landing_mudir_name" id="landing_mudir_name" type="text" class="w-full mt-1" />
                            <x-input-error :messages="$errors->get('landing_mudir_name')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="landing_mudir_title" value="Jabatan / Peran Pimpinan *" />
                            <x-text-input wire:model="landing_mudir_title" id="landing_mudir_title" type="text" class="w-full mt-1" />
                            <x-input-error :messages="$errors->get('landing_mudir_title')" class="mt-1" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="landing_mudir_credentials" value="Sanad Tahfidz / Almamater Pimpinan *" />
                        <x-text-input wire:model="landing_mudir_credentials" id="landing_mudir_credentials" type="text" class="w-full mt-1" />
                        <x-input-error :messages="$errors->get('landing_mudir_credentials')" class="mt-1" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="landing_mudir_badge" value="Badge Sambutan (Label Atas)" />
                            <x-text-input wire:model="landing_mudir_badge" id="landing_mudir_badge" type="text" placeholder="Amanah Pengasuh Pondok" class="w-full mt-1" />
                            <x-input-error :messages="$errors->get('landing_mudir_badge')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="landing_mudir_photo_url" value="URL Foto Potret Mudir" />
                            <x-text-input wire:model="landing_mudir_photo_url" id="landing_mudir_photo_url" type="text" class="w-full mt-1" />
                            <x-input-error :messages="$errors->get('landing_mudir_photo_url')" class="mt-1" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="landing_mudir_headline" value="Headline Kutipan Utama Mudir" />
                        <x-text-input wire:model="landing_mudir_headline" id="landing_mudir_headline" type="text" class="w-full mt-1 font-semibold" />
                        <x-input-error :messages="$errors->get('landing_mudir_headline')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="landing_mudir_quote" value="Pesan & Amanat Sambutan Mudir *" />
                        <x-textarea wire:model="landing_mudir_quote" id="landing_mudir_quote" rows="5" class="w-full mt-1 text-xs sm:text-sm" />
                        <x-input-error :messages="$errors->get('landing_mudir_quote')" class="mt-1" />
                    </div>
                </div>

                <div class="pt-4 flex justify-end">
                    <x-primary-button type="submit" wire:loading.attr="disabled">
                        <x-loading-label target="saveMudir" label="Simpan Profil Mudir" busy="Menyimpan..." />
                    </x-primary-button>
                </div>
            </form>

            <div class="lg:col-span-4 space-y-4">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-400 flex items-center gap-1.5">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                        Preview Sambutan
                    </h4>
                    <div class="p-4 rounded-xl bg-slate-950 border border-slate-800/80 space-y-3 text-left">
                        <div class="flex items-center gap-3">
                            <img src="{{ $landing_mudir_photo_url ?: '/images/mudir-portrait.jpg' }}" class="w-12 h-12 rounded-full object-cover border border-emerald-500/40">
                            <div>
                                <p class="text-xs font-bold text-slate-100">{{ $landing_mudir_name }}</p>
                                <p class="text-[11px] text-emerald-500">{{ $landing_mudir_title }}</p>
                            </div>
                        </div>
                        <p class="text-xs text-slate-300 italic leading-relaxed border-l-2 border-emerald-500 pl-3">
                            "{{ $landing_mudir_quote }}"
                        </p>
                        <p class="text-[10px] text-slate-400">
                            <i data-lucide="award" class="w-3 h-3 inline text-amber-400 mr-1"></i>
                            {{ $landing_mudir_credentials }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ==================== TAB 3: STATISTIK ==================== --}}
    @if ($tab === 'stats')
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 sm:p-6 space-y-6" wire:key="tab-stats">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h3 class="font-bold text-sm text-slate-100 flex items-center gap-2">
                        <i data-lucide="bar-chart-3" class="w-4 h-4 text-emerald-400"></i>
                        Statistik & Pencapaian Utama ({{ count($statsList) }})
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Angka kuantitatif yang tampil di pita statistik melayang di bawah banner hero. Bebas tambah atau hapus.</p>
                </div>
                <x-secondary-button size="xs" type="button" wire:click="addStat" class="gap-1.5 text-emerald-400 border-emerald-500/30 hover:bg-emerald-500/10 shrink-0">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Tambah Metrik</span>
                </x-secondary-button>
            </div>

            <form wire:submit="saveStats" class="space-y-6">
                @if (empty($statsList))
                    <div class="p-8 text-center rounded-xl bg-slate-950 border border-dashed border-slate-800 space-y-3">
                        <p class="text-xs text-slate-400">Belum ada metrik statistik yang dibuat.</p>
                        <x-secondary-button size="xs" type="button" wire:click="addStat" class="gap-1.5 text-emerald-400 border-emerald-500/30 hover:bg-emerald-500/10">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            <span>Tambah Metrik Pertama</span>
                        </x-secondary-button>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($statsList as $index => $stat)
                            <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-3" wire:key="stat-{{ $index }}">
                                <div class="flex items-center justify-between border-b border-slate-800/80 pb-2">
                                    <span class="text-xs font-bold text-emerald-400">Metrik #{{ $index + 1 }}</span>
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="{{ $stat['icon'] ?? 'award' }}" class="w-4 h-4 text-slate-500"></i>
                                        <button type="button" wire:click="removeStat({{ $index }})" class="p-1 text-rose-400 hover:text-rose-300 rounded hover:bg-slate-800 transition" title="Hapus Metrik">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <x-input-label :value="'Angka / Nilai *'" />
                                        <x-text-input wire:model="statsList.{{ $index }}.number" type="text" class="w-full mt-1" />
                                    </div>
                                    <div>
                                        <x-input-label :value="'Label Metrik *'" />
                                        <x-text-input wire:model="statsList.{{ $index }}.label" type="text" class="w-full mt-1" />
                                    </div>
                                </div>
                                <div>
                                    <x-input-label :value="'Keterangan Tambahan / Subtitle *'" />
                                    <x-text-input wire:model="statsList.{{ $index }}.sub" type="text" class="w-full mt-1" />
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="pt-4 flex justify-end">
                    <x-primary-button type="submit" wire:loading.attr="disabled">
                        <x-loading-label target="saveStats" label="Simpan Statistik" busy="Menyimpan..." />
                    </x-primary-button>
                </div>
            </form>
        </div>
    @endif

    {{-- ==================== TAB 4: PILAR KEUNGGULAN ==================== --}}
    @if ($tab === 'pillars')
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 sm:p-6 space-y-6" wire:key="tab-pillars">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h3 class="font-bold text-sm text-slate-100 flex items-center gap-2">
                        <i data-lucide="award" class="w-4 h-4 text-emerald-400"></i>
                        Pilar Keunggulan Pendidikan ({{ count($pillarsList) }})
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Nilai fundamental dan pilar keunggulan pendidikan pesantren. Bebas tambah atau hapus item.</p>
                </div>
                <x-secondary-button size="xs" type="button" wire:click="addPillar" class="gap-1.5 text-emerald-400 border-emerald-500/30 hover:bg-emerald-500/10 shrink-0">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Tambah Pilar</span>
                </x-secondary-button>
            </div>

            <form wire:submit="savePillars" class="space-y-6">
                @if (empty($pillarsList))
                    <div class="p-8 text-center rounded-xl bg-slate-950 border border-dashed border-slate-800 space-y-3">
                        <p class="text-xs text-slate-400">Belum ada pilar keunggulan yang dibuat.</p>
                        <x-secondary-button size="xs" type="button" wire:click="addPillar" class="gap-1.5 text-emerald-400 border-emerald-500/30 hover:bg-emerald-500/10">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            <span>Tambah Pilar Pertama</span>
                        </x-secondary-button>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($pillarsList as $index => $pillar)
                            <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-3" wire:key="pillar-{{ $index }}">
                                <div class="flex items-center justify-between border-b border-slate-800/80 pb-2">
                                    <span class="text-xs font-bold text-emerald-400">Pilar #{{ $index + 1 }}</span>
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="{{ $pillar['icon'] ?? 'star' }}" class="w-4 h-4 text-slate-500"></i>
                                        <button type="button" wire:click="removePillar({{ $index }})" class="p-1 text-rose-400 hover:text-rose-300 rounded hover:bg-slate-800 transition" title="Hapus Pilar">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <x-input-label :value="'Judul Pilar *'" />
                                        <x-text-input wire:model="pillarsList.{{ $index }}.title" type="text" class="w-full mt-1" />
                                    </div>
                                    <div>
                                        <x-input-label :value="'Badge Kategori *'" />
                                        <x-text-input wire:model="pillarsList.{{ $index }}.badge" type="text" class="w-full mt-1" />
                                    </div>
                                </div>
                                <div>
                                    <x-input-label :value="'Deskripsi Lengkap *'" />
                                    <x-textarea wire:model="pillarsList.{{ $index }}.desc" rows="3" class="w-full mt-1 text-xs" />
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="pt-4 flex justify-end">
                    <x-primary-button type="submit" wire:loading.attr="disabled">
                        <x-loading-label target="savePillars" label="Simpan Pilar Keunggulan" busy="Menyimpan..." />
                    </x-primary-button>
                </div>
            </form>
        </div>
    @endif

    {{-- ==================== TAB 5: PROGRAM AKADEMIK ==================== --}}
    @if ($tab === 'programs')
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 sm:p-6 space-y-6" wire:key="tab-programs">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h3 class="font-bold text-sm text-slate-100 flex items-center gap-2">
                        <i data-lucide="graduation-cap" class="w-4 h-4 text-emerald-400"></i>
                        Program Jenjang Pendidikan & Kurikulum ({{ count($programsList) }})
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Kelola seluruh informasi jenjang pendidikan dan fokus kurikulum. Bebas tambah atau hapus.</p>
                </div>
                <x-secondary-button size="xs" type="button" wire:click="addProgram" class="gap-1.5 text-emerald-400 border-emerald-500/30 hover:bg-emerald-500/10 shrink-0">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Tambah Program</span>
                </x-secondary-button>
            </div>

            <form wire:submit="savePrograms" class="space-y-6">
                @if (empty($programsList))
                    <div class="p-8 text-center rounded-xl bg-slate-950 border border-dashed border-slate-800 space-y-3">
                        <p class="text-xs text-slate-400">Belum ada jenjang pendidikan yang dibuat.</p>
                        <x-secondary-button size="xs" type="button" wire:click="addProgram" class="gap-1.5 text-emerald-400 border-emerald-500/30 hover:bg-emerald-500/10">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            <span>Tambah Program Pertama</span>
                        </x-secondary-button>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach ($programsList as $index => $prog)
                            <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-4" wire:key="prog-{{ $index }}">
                                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                                    <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider">Jenjang #{{ $index + 1 }}: {{ $prog['name'] ?: ($prog['id'] ?? '') }}</span>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-slate-400 font-mono">{{ $prog['level'] ?? '' }}</span>
                                        <button type="button" wire:click="removeProgram({{ $index }})" class="p-1.5 text-rose-400 hover:text-rose-300 rounded-lg hover:bg-slate-800 transition" title="Hapus Program">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div>
                                        <x-input-label :value="'Nama Program Jenjang *'" />
                                        <x-text-input wire:model="programsList.{{ $index }}.name" type="text" class="w-full mt-1" />
                                    </div>
                                    <div>
                                        <x-input-label :value="'Badge / Status Akreditasi *'" />
                                        <x-text-input wire:model="programsList.{{ $index }}.badge" type="text" class="w-full mt-1" />
                                    </div>
                                    <div>
                                        <x-input-label :value="'Rentang Usia & Kualifikasi Santri *'" />
                                        <x-text-input wire:model="programsList.{{ $index }}.age" type="text" class="w-full mt-1" />
                                    </div>
                                </div>

                                <div>
                                    <x-input-label :value="'Deskripsi Program & Kurikulum Fokus *'" />
                                    <x-textarea wire:model="programsList.{{ $index }}.desc" rows="3" class="w-full mt-1 text-xs" />
                                </div>

                                <div>
                                    <x-input-label :value="'Kutipan Visi Lulusan (Quote) *'" />
                                    <x-text-input wire:model="programsList.{{ $index }}.quote" type="text" class="w-full mt-1" />
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="pt-4 flex justify-end">
                    <x-primary-button type="submit" wire:loading.attr="disabled">
                        <x-loading-label target="savePrograms" label="Simpan Seluruh Program Pendidikan" busy="Menyimpan..." />
                    </x-primary-button>
                </div>
            </form>
        </div>
    @endif

    {{-- ==================== TAB 6: JALUR MASUK SELEKSI & BEASISWA PPDB ==================== --}}
    @if ($tab === 'tracks')
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 sm:p-6 space-y-6" wire:key="tab-tracks">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h3 class="font-bold text-sm text-slate-100 flex items-center gap-2">
                        <i data-lucide="layers" class="w-4 h-4 text-emerald-400"></i>
                        Jalur Masuk Seleksi & Beasiswa PPDB ({{ count($tracksList) }})
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Kelola kartu jalur masuk seleksi santri baru, benefit beasiswa, dan kuota. Bebas tambah atau hapus.</p>
                </div>
                <x-secondary-button size="xs" type="button" wire:click="addTrack" class="gap-1.5 text-emerald-400 border-emerald-500/30 hover:bg-emerald-500/10 shrink-0">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Tambah Jalur Masuk</span>
                </x-secondary-button>
            </div>

            <form wire:submit="saveTracks" class="space-y-6">
                @if (empty($tracksList))
                    <div class="p-8 text-center rounded-xl bg-slate-950 border border-dashed border-slate-800 space-y-3">
                        <p class="text-xs text-slate-400">Belum ada jalur masuk PPDB yang dibuat.</p>
                        <x-secondary-button size="xs" type="button" wire:click="addTrack" class="gap-1.5 text-emerald-400 border-emerald-500/30 hover:bg-emerald-500/10">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            <span>Tambah Jalur Pertama</span>
                        </x-secondary-button>
                    </div>
                @else
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                        @foreach ($tracksList as $index => $track)
                            <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-4 flex flex-col justify-between" wire:key="track-{{ $index }}">
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                                        <div class="flex items-center gap-3">
                                            <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider">Jalur #{{ $index + 1 }}</span>
                                            <x-checkbox
                                                wire:model="tracksList.{{ $index }}.featured"
                                                label="Unggulan"
                                            />
                                        </div>
                                        <button type="button" wire:click="removeTrack({{ $index }})" class="p-1.5 text-rose-400 hover:text-rose-300 rounded-lg hover:bg-slate-800 transition" title="Hapus Jalur">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>

                                    <div>
                                        <x-input-label :value="'Nama Jalur Masuk *'" />
                                        <x-text-input wire:model="tracksList.{{ $index }}.name" type="text" class="w-full mt-1" />
                                    </div>

                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <x-input-label :value="'Badge / Tag *'" />
                                            <x-text-input wire:model="tracksList.{{ $index }}.badge" type="text" class="w-full mt-1 text-xs" />
                                        </div>
                                        <div>
                                            <x-input-label :value="'Kuota & Kuartal *'" />
                                            <x-text-input wire:model="tracksList.{{ $index }}.quota" type="text" class="w-full mt-1 text-xs" />
                                        </div>
                                    </div>

                                    <div>
                                        <x-input-label :value="'Highlight Beasiswa / Keuntungan *'" />
                                        <x-text-input wire:model="tracksList.{{ $index }}.highlight" type="text" class="w-full mt-1 text-xs font-semibold text-emerald-400" />
                                    </div>

                                    <div>
                                        <x-input-label :value="'Deskripsi Persyaratan & Sasaran *'" />
                                        <x-textarea wire:model="tracksList.{{ $index }}.desc" rows="3" class="w-full mt-1 text-xs" />
                                    </div>

                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <x-input-label :value="'Fasilitas / Keuntungan (Benefits)'" />
                                            <button type="button" wire:click="addTrackBenefit({{ $index }})" class="text-[11px] font-bold text-emerald-400 hover:text-emerald-300 flex items-center gap-1">
                                                <i data-lucide="plus" class="w-3 h-3"></i> Tambah Poin
                                            </button>
                                        </div>
                                        <div class="space-y-2">
                                            @foreach ($track['benefits'] ?? [] as $bIndex => $benefit)
                                                <div class="flex items-center gap-2" wire:key="benefit-{{ $index }}-{{ $bIndex }}">
                                                    <x-text-input wire:model="tracksList.{{ $index }}.benefits.{{ $bIndex }}" type="text" class="w-full text-xs" />
                                                    <button type="button" wire:click="removeTrackBenefit({{ $index }}, {{ $bIndex }})" class="p-1.5 text-rose-400 hover:text-rose-300 rounded-lg hover:bg-slate-800 transition" title="Hapus">
                                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div>
                                        <x-input-label :value="'Label Tombol CTA *'" />
                                        <x-text-input wire:model="tracksList.{{ $index }}.cta_text" type="text" class="w-full mt-1 text-xs" />
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="pt-4 flex justify-end">
                    <x-primary-button type="submit" wire:loading.attr="disabled">
                        <x-loading-label target="saveTracks" label="Simpan Jalur PPDB & Beasiswa" busy="Menyimpan..." />
                    </x-primary-button>
                </div>
            </form>
        </div>
    @endif

    {{-- ==================== TAB 7: TAHFIDZ & JADWAL ASRAMA ==================== --}}
    @if ($tab === 'tahfidz_schedule')
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 sm:p-6 space-y-6" wire:key="tab-tahfidz-schedule">
            <div>
                <h3 class="font-bold text-sm text-slate-100 flex items-center gap-2">
                    <i data-lucide="book-open" class="w-4 h-4 text-emerald-400"></i>
                    Tahapan Tahfidz Mutqin & Jadwal Keseharian Keasramaan
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Kelola tahapan hafalan Al-Qur'an dan alur kehidupan santri berasrama dari subuh hingga malam. Bebas tambah atau hapus.</p>
            </div>

            <form wire:submit="saveTahfidzAndSchedule" class="space-y-6">
                <!-- Tahapan Tahfidz -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-400">Tahapan Program Tahfidz Al-Qur'an ({{ count($tahfidzList) }})</h4>
                        <x-secondary-button size="xs" type="button" wire:click="addTahfidzLevel" class="gap-1.5 text-emerald-400 border-emerald-500/30 hover:bg-emerald-500/10">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            <span>Tambah Tahap</span>
                        </x-secondary-button>
                    </div>

                    @if (empty($tahfidzList))
                        <div class="p-6 text-center rounded-xl bg-slate-950 border border-dashed border-slate-800">
                            <p class="text-xs text-slate-400">Belum ada tahap tahfidz. Klik "Tambah Tahap" untuk menambahkan.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach ($tahfidzList as $index => $step)
                                <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800 space-y-2.5" wire:key="step-{{ $index }}">
                                    <div class="flex items-center justify-between border-b border-slate-800/80 pb-2">
                                        <span class="text-xs font-extrabold text-amber-400">Tahap {{ $step['step'] ?? ($index + 1) }}</span>
                                        <button type="button" wire:click="removeTahfidzLevel({{ $index }})" class="p-1 text-rose-400 hover:text-rose-300 rounded hover:bg-slate-800 transition" title="Hapus Tahap">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                    <div>
                                        <x-input-label :value="'Judul Tahap *'" />
                                        <x-text-input wire:model="tahfidzList.{{ $index }}.title" type="text" class="w-full mt-1 text-xs" />
                                    </div>
                                    <div>
                                        <x-input-label :value="'Target Capaian *'" />
                                        <x-text-input wire:model="tahfidzList.{{ $index }}.target" type="text" class="w-full mt-1 text-xs" />
                                    </div>
                                    <div>
                                        <x-input-label :value="'Deskripsi Singkat *'" />
                                        <x-textarea wire:model="tahfidzList.{{ $index }}.desc" rows="2" class="w-full mt-1 text-xs" />
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Jadwal Asrama -->
                <div class="pt-6 border-t border-slate-800 space-y-4">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-400">Fase Jadwal Keseharian Santri (24 Jam) ({{ count($scheduleList) }})</h4>
                        <x-secondary-button size="xs" type="button" wire:click="addScheduleItem" class="gap-1.5 text-emerald-400 border-emerald-500/30 hover:bg-emerald-500/10">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            <span>Tambah Jadwal</span>
                        </x-secondary-button>
                    </div>

                    @if (empty($scheduleList))
                        <div class="p-6 text-center rounded-xl bg-slate-950 border border-dashed border-slate-800">
                            <p class="text-xs text-slate-400">Belum ada fase jadwal keseharian. Klik "Tambah Jadwal" untuk menambahkan.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach ($scheduleList as $index => $sch)
                                <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-3" wire:key="sch-{{ $index }}">
                                    <div class="flex items-center justify-between border-b border-slate-800/80 pb-2">
                                        <span class="text-xs font-bold text-emerald-400">Jadwal #{{ $index + 1 }}</span>
                                        <button type="button" wire:click="removeScheduleItem({{ $index }})" class="p-1 text-rose-400 hover:text-rose-300 rounded hover:bg-slate-800 transition" title="Hapus Jadwal">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <x-input-label :value="'Fase Waktu *'" />
                                            <x-text-input wire:model="scheduleList.{{ $index }}.period" type="text" class="w-full mt-1" />
                                        </div>
                                        <div>
                                            <x-input-label :value="'Jam Pelaksanaan *'" />
                                            <x-text-input wire:model="scheduleList.{{ $index }}.time" type="text" class="w-full mt-1" />
                                        </div>
                                    </div>
                                    <div>
                                        <x-input-label :value="'Judul Aktivitas *'" />
                                        <x-text-input wire:model="scheduleList.{{ $index }}.title" type="text" class="w-full mt-1" />
                                    </div>
                                    <div>
                                        <x-input-label :value="'Rincian Kegiatan *'" />
                                        <x-textarea wire:model="scheduleList.{{ $index }}.desc" rows="3" class="w-full mt-1 text-xs" />
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="pt-4 flex justify-end">
                    <x-primary-button type="submit" wire:loading.attr="disabled">
                        <x-loading-label target="saveTahfidzAndSchedule" label="Simpan Tahfidz & Jadwal Asrama" busy="Menyimpan..." />
                    </x-primary-button>
                </div>
            </form>
        </div>
    @endif

    {{-- ==================== TAB 8: FASILITAS KAMPUS ==================== --}}
    @if ($tab === 'facilities')
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 sm:p-6 space-y-6" wire:key="tab-facilities">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h3 class="font-bold text-sm text-slate-100 flex items-center gap-2">
                        <i data-lucide="building-2" class="w-4 h-4 text-emerald-400"></i>
                        Fasilitas Kampus & Asrama ({{ count($facilitiesList) }})
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Kelola foto, nama fasilitas, kategori sarana, dan deskripsi prasarana kampus. Bebas tambah atau hapus.</p>
                </div>
                <x-secondary-button size="xs" type="button" wire:click="addFacility" class="gap-1.5 text-emerald-400 border-emerald-500/30 hover:bg-emerald-500/10 shrink-0">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Tambah Fasilitas</span>
                </x-secondary-button>
            </div>

            <form wire:submit="saveFacilities" class="space-y-6">
                @if (empty($facilitiesList))
                    <div class="p-8 text-center rounded-xl bg-slate-950 border border-dashed border-slate-800 space-y-3">
                        <p class="text-xs text-slate-400">Belum ada data fasilitas yang ditambahkan.</p>
                        <x-secondary-button size="xs" type="button" wire:click="addFacility" class="gap-1.5 text-emerald-400 border-emerald-500/30 hover:bg-emerald-500/10">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            <span>Tambah Fasilitas Pertama</span>
                        </x-secondary-button>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($facilitiesList as $index => $fac)
                            <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-3" wire:key="fac-{{ $index }}">
                                <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                                    <span class="text-xs font-bold text-emerald-400">Fasilitas #{{ $index + 1 }}</span>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[11px] text-slate-400 font-mono">{{ $fac['category'] ?? '' }}</span>
                                        <button type="button" wire:click="removeFacility({{ $index }})" class="p-1 text-rose-400 hover:text-rose-300 rounded hover:bg-slate-800 transition" title="Hapus Fasilitas">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <x-input-label :value="'Nama Sarana / Fasilitas *'" />
                                        <x-text-input wire:model="facilitiesList.{{ $index }}.name" type="text" class="w-full mt-1" />
                                    </div>
                                    <div>
                                        <x-input-label :value="'Kategori / Fungsi *'" />
                                        <x-text-input wire:model="facilitiesList.{{ $index }}.category" type="text" class="w-full mt-1" />
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <x-input-label :value="'URL Gambar / Foto *'" />
                                        <x-text-input wire:model="facilitiesList.{{ $index }}.image" type="text" class="w-full mt-1 text-xs" />
                                    </div>
                                    <div>
                                        <x-input-label :value="'Badge Keunggulan *'" />
                                        <x-text-input wire:model="facilitiesList.{{ $index }}.tag" type="text" class="w-full mt-1 text-xs" />
                                    </div>
                                </div>

                                <div>
                                    <x-input-label :value="'Deskripsi Fasilitas *'" />
                                    <x-textarea wire:model="facilitiesList.{{ $index }}.desc" rows="2" class="w-full mt-1 text-xs" />
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="pt-4 flex justify-end">
                    <x-primary-button type="submit" wire:loading.attr="disabled">
                        <x-loading-label target="saveFacilities" label="Simpan Fasilitas Kampus" busy="Menyimpan..." />
                    </x-primary-button>
                </div>
            </form>
        </div>
    @endif

    {{-- ==================== TAB 9: PRESTASI & TESTIMONI ==================== --}}
    @if ($tab === 'social_proof')
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 sm:p-6 space-y-6" wire:key="tab-social-proof">
            <div>
                <h3 class="font-bold text-sm text-slate-100 flex items-center gap-2">
                    <i data-lucide="award" class="w-4 h-4 text-emerald-400"></i>
                    Prestasi Santri, Testimoni Wali Santri & Agenda Kegiatan
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Kelola bukti nyata keberhasilan pendidikan santri, testimoni wali murid, serta agenda acara. Bebas tambah atau hapus.</p>
            </div>

            <form wire:submit="saveSocialProof" class="space-y-6">
                <!-- Prestasi -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-400">Daftar Prestasi Nasional & Global ({{ count($achievementsList) }})</h4>
                        <x-secondary-button size="xs" type="button" wire:click="addAchievement" class="gap-1.5 text-emerald-400 border-emerald-500/30 hover:bg-emerald-500/10">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            <span>Tambah Prestasi</span>
                        </x-secondary-button>
                    </div>

                    @if (empty($achievementsList))
                        <div class="p-6 text-center rounded-xl bg-slate-950 border border-dashed border-slate-800">
                            <p class="text-xs text-slate-400">Belum ada data prestasi. Klik "Tambah Prestasi" untuk menambahkan.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach ($achievementsList as $index => $ach)
                                <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-2.5" wire:key="ach-{{ $index }}">
                                    <div class="flex items-center justify-between border-b border-slate-800/80 pb-2">
                                        <span class="text-xs font-bold text-emerald-400">Prestasi #{{ $index + 1 }}</span>
                                        <button type="button" wire:click="removeAchievement({{ $index }})" class="p-1 text-rose-400 hover:text-rose-300 rounded hover:bg-slate-800 transition" title="Hapus Prestasi">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-3 gap-2">
                                        <div>
                                            <x-input-label :value="'Tahun *'" />
                                            <x-text-input wire:model="achievementsList.{{ $index }}.year" type="text" class="w-full mt-1 text-xs" />
                                        </div>
                                        <div class="col-span-2">
                                            <x-input-label :value="'Kategori / Bidang *'" />
                                            <x-text-input wire:model="achievementsList.{{ $index }}.category" type="text" class="w-full mt-1 text-xs" />
                                        </div>
                                    </div>
                                    <div>
                                        <x-input-label :value="'Nama Gelar / Medali Kejuaraan *'" />
                                        <x-text-input wire:model="achievementsList.{{ $index }}.title" type="text" class="w-full mt-1 text-xs" />
                                    </div>
                                    <div>
                                        <x-input-label :value="'Event / Penyelenggara *'" />
                                        <x-text-input wire:model="achievementsList.{{ $index }}.event" type="text" class="w-full mt-1 text-xs" />
                                    </div>
                                    <div>
                                        <x-input-label :value="'Nama Santri / Tim Peraih *'" />
                                        <x-text-input wire:model="achievementsList.{{ $index }}.student" type="text" class="w-full mt-1 text-xs" />
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Testimoni -->
                <div class="pt-6 border-t border-slate-800 space-y-4">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-400">Testimoni Wali Santri & Alumni ({{ count($testimonialsList) }})</h4>
                        <x-secondary-button size="xs" type="button" wire:click="addTestimonial" class="gap-1.5 text-emerald-400 border-emerald-500/30 hover:bg-emerald-500/10">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            <span>Tambah Testimoni</span>
                        </x-secondary-button>
                    </div>

                    @if (empty($testimonialsList))
                        <div class="p-6 text-center rounded-xl bg-slate-950 border border-dashed border-slate-800">
                            <p class="text-xs text-slate-400">Belum ada testimoni. Klik "Tambah Testimoni" untuk menambahkan.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach ($testimonialsList as $index => $testi)
                                <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-3" wire:key="testi-{{ $index }}">
                                    <div class="flex items-center justify-between border-b border-slate-800/80 pb-2">
                                        <span class="text-xs font-bold text-emerald-400">Testimoni #{{ $index + 1 }}</span>
                                        <button type="button" wire:click="removeTestimonial({{ $index }})" class="p-1 text-rose-400 hover:text-rose-300 rounded hover:bg-slate-800 transition" title="Hapus Testimoni">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <x-input-label :value="'Nama Wali / Alumni *'" />
                                            <x-text-input wire:model="testimonialsList.{{ $index }}.name" type="text" class="w-full mt-1" />
                                        </div>
                                        <div>
                                            <x-input-label :value="'Peran / Status *'" />
                                            <x-text-input wire:model="testimonialsList.{{ $index }}.role" type="text" class="w-full mt-1" />
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <x-input-label :value="'Orang Tua dari Santri *'" />
                                            <x-text-input wire:model="testimonialsList.{{ $index }}.child" type="text" class="w-full mt-1" />
                                        </div>
                                        <div>
                                            <x-input-label :value="'Rating (1-5) *'" />
                                            <x-text-input wire:model="testimonialsList.{{ $index }}.rating" type="number" min="1" max="5" class="w-full mt-1" />
                                        </div>
                                    </div>
                                    <div>
                                        <x-input-label :value="'Kutipan Testimoni *'" />
                                        <x-textarea wire:model="testimonialsList.{{ $index }}.quote" rows="3" class="w-full mt-1 text-xs" />
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Agenda Terkini (Dinamis & Fleksibel) -->
                <div class="pt-6 border-t border-slate-800 space-y-4">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-400">Agenda & Jadwal Kegiatan ({{ count($agendaList) }})</h4>
                        <x-secondary-button size="xs" type="button" wire:click="addAgenda" class="gap-1.5 text-emerald-400 border-emerald-500/30 hover:bg-emerald-500/10">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            <span>Tambah Agenda</span>
                        </x-secondary-button>
                    </div>

                    @if (empty($agendaList))
                        <div class="p-6 text-center rounded-xl bg-slate-950 border border-dashed border-slate-800">
                            <p class="text-xs text-slate-400">Belum ada agenda kegiatan. Klik "Tambah Agenda" untuk menambahkan.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            @foreach ($agendaList as $index => $event)
                                <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800 space-y-2.5" wire:key="agenda-{{ $index }}">
                                    <div class="flex items-center justify-between border-b border-slate-800/80 pb-2">
                                        <span class="text-xs font-bold text-amber-400">Agenda #{{ $index + 1 }}</span>
                                        <button type="button" wire:click="removeAgenda({{ $index }})" class="p-1 text-rose-400 hover:text-rose-300 rounded hover:bg-slate-800 transition" title="Hapus Agenda">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                    <div>
                                        <x-input-label :value="'Judul Agenda *'" />
                                        <x-text-input wire:model="agendaList.{{ $index }}.title" type="text" class="w-full mt-1 text-xs" />
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <x-input-label :value="'Tanggal *'" />
                                            <x-text-input wire:model="agendaList.{{ $index }}.date" type="text" class="w-full mt-1 text-xs" />
                                        </div>
                                        <div>
                                            <x-input-label :value="'Badge / Tag'" />
                                            <x-text-input wire:model="agendaList.{{ $index }}.badge" type="text" placeholder="Agenda" class="w-full mt-1 text-xs" />
                                        </div>
                                    </div>
                                    <div>
                                        <x-input-label :value="'Lokasi Acara'" />
                                        <x-text-input wire:model="agendaList.{{ $index }}.location" type="text" placeholder="Kampus Utama" class="w-full mt-1 text-xs" />
                                    </div>
                                    <div>
                                        <x-input-label :value="'Deskripsi Acara *'" />
                                        <x-textarea wire:model="agendaList.{{ $index }}.desc" rows="2" class="w-full mt-1 text-xs" />
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="pt-4 flex justify-end">
                    <x-primary-button type="submit" wire:loading.attr="disabled">
                        <x-loading-label target="saveSocialProof" label="Simpan Prestasi, Testimoni & Agenda" busy="Menyimpan..." />
                    </x-primary-button>
                </div>
            </form>
        </div>
    @endif

    {{-- ==================== TAB 10: FAQ & KONTAK ==================== --}}
    @if ($tab === 'faq_contact')
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 sm:p-6 space-y-6" wire:key="tab-faq-contact">
            <div>
                <h3 class="font-bold text-sm text-slate-100 flex items-center gap-2">
                    <i data-lucide="help-circle" class="w-4 h-4 text-emerald-400"></i>
                    Tanya Jawab (FAQ), Informasi Kontak & Tautan Media Sosial
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Kelola jawaban atas pertanyaan umum seputar PPDB dan seluruh saluran komunikasi resmi madrasah. Bebas tambah atau hapus pertanyaan.</p>
            </div>

            <form wire:submit="saveFaqAndContact" class="space-y-6">
                <!-- FAQ Accordion -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-400">Pertanyaan Umum (FAQ) ({{ count($faqsList) }})</h4>
                        <x-secondary-button size="xs" type="button" wire:click="addFaq" class="gap-1.5 text-emerald-400 border-emerald-500/30 hover:bg-emerald-500/10">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            <span>Tambah Pertanyaan</span>
                        </x-secondary-button>
                    </div>

                    @if (empty($faqsList))
                        <div class="p-6 text-center rounded-xl bg-slate-950 border border-dashed border-slate-800">
                            <p class="text-xs text-slate-400">Belum ada pertanyaan FAQ. Klik "Tambah Pertanyaan" untuk menambahkan.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach ($faqsList as $index => $faq)
                                <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-2.5" wire:key="faq-{{ $index }}">
                                    <div class="flex items-center justify-between border-b border-slate-800/80 pb-2">
                                        <span class="text-xs font-bold text-emerald-400">Pertanyaan #{{ $index + 1 }}</span>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] text-slate-400">{{ $faq['category'] ?? 'Umum' }}</span>
                                            <button type="button" wire:click="removeFaq({{ $index }})" class="p-1 text-rose-400 hover:text-rose-300 rounded hover:bg-slate-800 transition" title="Hapus Pertanyaan">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                        <div class="sm:col-span-2">
                                            <x-input-label :value="'Pertanyaan *'" />
                                            <x-text-input wire:model="faqsList.{{ $index }}.q" type="text" class="w-full mt-1 text-xs" />
                                        </div>
                                        <div>
                                            <x-input-label :value="'Kategori'" />
                                            <x-text-input wire:model="faqsList.{{ $index }}.category" type="text" placeholder="PPDB/Tahfidz" class="w-full mt-1 text-xs" />
                                        </div>
                                    </div>
                                    <div>
                                        <x-input-label :value="'Jawaban Lengkap *'" />
                                        <x-textarea wire:model="faqsList.{{ $index }}.a" rows="3" class="w-full mt-1 text-xs" />
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Kontak & Sekretariat -->
                <div class="pt-6 border-t border-slate-800 space-y-4">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-400">Saluran Kontak Resmi</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="landing_whatsapp_hotline" value="WhatsApp Hotline PPDB *" />
                            <x-text-input wire:model="landing_whatsapp_hotline" id="landing_whatsapp_hotline" type="text" class="w-full mt-1" />
                            <x-input-error :messages="$errors->get('landing_whatsapp_hotline')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="company_phone" value="Telepon Kantor Sekretariat" />
                            <x-text-input wire:model="company_phone" id="company_phone" type="text" class="w-full mt-1" />
                            <x-input-error :messages="$errors->get('company_phone')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="company_email" value="Email Resmi Lembaga" />
                            <x-text-input wire:model="company_email" id="company_email" type="email" class="w-full mt-1" />
                            <x-input-error :messages="$errors->get('company_email')" class="mt-1" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="landing_office_hours" value="Jam Operasional Layanan *" />
                            <x-text-input wire:model="landing_office_hours" id="landing_office_hours" type="text" class="w-full mt-1" />
                            <x-input-error :messages="$errors->get('landing_office_hours')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="company_address" value="Alamat Lengkap Kampus" />
                            <x-text-input wire:model="company_address" id="company_address" type="text" class="w-full mt-1" />
                            <x-input-error :messages="$errors->get('company_address')" class="mt-1" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="landing_maps_embed" value="Embed URL Google Maps (iframe src)" />
                        <x-text-input wire:model="landing_maps_embed" id="landing_maps_embed" type="text" class="w-full mt-1 text-xs" />
                        <x-input-error :messages="$errors->get('landing_maps_embed')" class="mt-1" />
                    </div>
                </div>

                <!-- Media Sosial -->
                <div class="pt-6 border-t border-slate-800 space-y-4">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-400">Akun Media Sosial Resmi</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <x-input-label for="landing_instagram_url" value="Instagram URL" />
                            <x-text-input wire:model="landing_instagram_url" id="landing_instagram_url" type="text" class="w-full mt-1 text-xs" />
                        </div>
                        <div>
                            <x-input-label for="landing_youtube_url" value="YouTube URL" />
                            <x-text-input wire:model="landing_youtube_url" id="landing_youtube_url" type="text" class="w-full mt-1 text-xs" />
                        </div>
                        <div>
                            <x-input-label for="landing_facebook_url" value="Facebook URL" />
                            <x-text-input wire:model="landing_facebook_url" id="landing_facebook_url" type="text" class="w-full mt-1 text-xs" />
                        </div>
                        <div>
                            <x-input-label for="landing_tiktok_url" value="TikTok URL" />
                            <x-text-input wire:model="landing_tiktok_url" id="landing_tiktok_url" type="text" class="w-full mt-1 text-xs" />
                        </div>
                    </div>
                </div>

                <div class="pt-4 flex justify-end">
                    <x-primary-button type="submit" wire:loading.attr="disabled">
                        <x-loading-label target="saveFaqAndContact" label="Simpan FAQ & Informasi Kontak" busy="Menyimpan..." />
                    </x-primary-button>
                </div>
            </form>
        </div>
    @endif

    {{-- ==================== TAB 11: JUDUL SEKSI & CLOSING CTA ==================== --}}
    @if ($tab === 'headings_cta')
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 sm:p-6 space-y-6" wire:key="tab-headings-cta">
            <div>
                <h3 class="font-bold text-sm text-slate-100 flex items-center gap-2">
                    <i data-lucide="type" class="w-4 h-4 text-emerald-400"></i>
                    Judul Seksi Landing Page & Banner Penutup (Closing CTA)
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Kelola seluruh teks judul, subjudul, dan badge untuk setiap seksi di landing page, serta teks banner penutup ajakan mendaftar.</p>
            </div>

            <form wire:submit="saveHeadingsAndCta" class="space-y-6">
                <!-- Closing CTA Banner -->
                <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-4">
                    <div class="flex items-center gap-2 border-b border-slate-800 pb-3">
                        <i data-lucide="megaphone" class="w-4 h-4 text-emerald-400"></i>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-100">Banner Penutup Ajakan Mendaftar (Closing CTA)</h4>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="landing_cta_badge" value="Badge Penutup *" />
                            <x-text-input wire:model="landing_cta_badge" id="landing_cta_badge" type="text" class="w-full mt-1" />
                            <x-input-error :messages="$errors->get('landing_cta_badge')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="landing_cta_btn_primary" value="Label Tombol Utama (Daftar) *" />
                            <x-text-input wire:model="landing_cta_btn_primary" id="landing_cta_btn_primary" type="text" class="w-full mt-1" />
                            <x-input-error :messages="$errors->get('landing_cta_btn_primary')" class="mt-1" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="landing_cta_title" value="Judul Utama Banner Penutup *" />
                        <x-text-input wire:model="landing_cta_title" id="landing_cta_title" type="text" class="w-full mt-1 font-semibold" />
                        <x-input-error :messages="$errors->get('landing_cta_title')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="landing_cta_desc" value="Deskripsi / Paragraf Banner Penutup *" />
                        <x-textarea wire:model="landing_cta_desc" id="landing_cta_desc" rows="3" class="w-full mt-1 text-xs sm:text-sm" />
                        <x-input-error :messages="$errors->get('landing_cta_desc')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="landing_cta_btn_whatsapp" value="Label Tombol Sekunder (WhatsApp Konsultasi) *" />
                        <x-text-input wire:model="landing_cta_btn_whatsapp" id="landing_cta_btn_whatsapp" type="text" class="w-full mt-1" />
                        <x-input-error :messages="$errors->get('landing_cta_btn_whatsapp')" class="mt-1" />
                    </div>
                </div>

                <!-- Judul & Subtitle Seluruh Seksi -->
                <div class="space-y-4">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-400 flex items-center gap-2">
                        <i data-lucide="layout" class="w-4 h-4"></i>
                        Badge, Judul & Subtitle Tiap Seksi
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @php
                            $sectionNames = [
                                'pillars' => 'Seksi 4: Pilar Keunggulan',
                                'programs' => 'Seksi 5: Program Akademik',
                                'tracks' => 'Seksi 6: Jalur Masuk PPDB & Beasiswa',
                                'tahfidz' => 'Seksi 7: Tahfidz Roadmap Al-Qur\'an',
                                'schedule' => 'Seksi 8: Jadwal Keasramaan 24 Jam',
                                'facilities' => 'Seksi 9: Fasilitas Kampus',
                                'achievements' => 'Seksi 10: Prestasi Santri',
                                'testimonials' => 'Seksi 11: Suara Wali Santri',
                                'agenda' => 'Seksi 12: Agenda & Jadwal Penting',
                                'faq' => 'Seksi 13: Tanya Jawab Umum (FAQ)',
                            ];
                        @endphp

                        @foreach ($sectionNames as $sKey => $sTitle)
                            <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-3" wire:key="sec-{{ $sKey }}">
                                <span class="text-xs font-bold text-slate-200 block border-b border-slate-800 pb-2">{{ $sTitle }}</span>
                                <div class="grid grid-cols-3 gap-2">
                                    <div class="col-span-1">
                                        <x-input-label :value="'Badge / Tag'" />
                                        <x-text-input wire:model="headings.{{ $sKey }}.badge" type="text" class="w-full mt-1 text-xs" />
                                    </div>
                                    <div class="col-span-2">
                                        <x-input-label :value="'Judul Seksi *'" />
                                        <x-text-input wire:model="headings.{{ $sKey }}.title" type="text" class="w-full mt-1 text-xs font-semibold" />
                                    </div>
                                </div>
                                <div>
                                    <x-input-label :value="'Subjudul / Penjelasan Ringkas'" />
                                    <x-textarea wire:model="headings.{{ $sKey }}.sub" rows="2" class="w-full mt-1 text-xs" />
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="pt-4 flex justify-end">
                    <x-primary-button type="submit" wire:loading.attr="disabled">
                        <x-loading-label target="saveHeadingsAndCta" label="Simpan Judul Seksi & Banner CTA" busy="Menyimpan..." />
                    </x-primary-button>
                </div>
            </form>
        </div>
    @endif
</div>
