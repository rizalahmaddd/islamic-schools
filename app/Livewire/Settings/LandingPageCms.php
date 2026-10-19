<?php

namespace App\Livewire\Settings;

use App\Models\Setting;
use App\Support\LandingPageData;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * CMS Pengaturan Halaman Publik (Landing Page) untuk role Admin / Superadmin.
 * Mengelola seluruh konten landing page: Hero, Sambutan Mudir, Statistik,
 * Pilar Keunggulan, Program Akademik, Tahfidz & Keasramaan, Fasilitas,
 * Prestasi, Testimoni, Agenda, FAQ, dan Kontak/Sosial Media.
 */
#[Layout('layouts.app', ['heading' => 'CMS Landing Page'])]
#[Title('CMS Landing Page')]
class LandingPageCms extends Component
{
    public const TABS = [
        'hero',
        'mudir',
        'stats',
        'pillars',
        'programs',
        'tracks',
        'tahfidz_schedule',
        'facilities',
        'social_proof',
        'faq_contact',
        'headings_cta',
    ];

    #[Url(as: 'tab', except: 'hero')]
    public string $tab = 'hero';

    // 1. Hero & Banner
    public string $landing_hero_badge = '';

    public string $landing_hero_title = '';

    public string $landing_hero_subtitle = '';

    public string $landing_announcement_text = '';

    public string $landing_hero_cta_primary = '';

    public string $landing_hero_cta_secondary = '';

    public string $landing_video_url = '';

    public string $landing_batch_name = '';

    public string $landing_batch_quota = '';

    public string $landing_batch_progress = '';

    public string $landing_hero_quran_arabic = '';

    public string $landing_hero_quran_translation = '';

    public string $landing_hero_quran_surah = '';

    public string $landing_hero_image = '';

    public string $landing_hero_caption_title = '';

    public string $landing_hero_caption_sub = '';

    public string $landing_hero_badge_1_label = '';

    public string $landing_hero_badge_1_value = '';

    public string $landing_hero_badge_2_label = '';

    public string $landing_hero_badge_2_value = '';

    // 2. Sambutan Mudir & Profil
    public string $landing_mudir_name = '';

    public string $landing_mudir_title = '';

    public string $landing_mudir_credentials = '';

    public string $landing_mudir_quote = '';

    public string $landing_mudir_headline = '';

    public string $landing_mudir_badge = '';

    public string $landing_mudir_photo_url = '';

    // 3. Statistik / Metrik (single string keys for backward-compat + array)
    public string $landing_stat_students = '';

    public string $landing_stat_tahfidz = '';

    public string $landing_stat_teachers = '';

    public string $landing_stat_ptn = '';

    public array $statsList = [];

    // 4. Pilar Keunggulan Utama
    public array $pillarsList = [];

    // 5. Program Jenjang Pendidikan
    public array $programsList = [];

    // 6. Jalur Masuk Seleksi & Beasiswa PPDB
    public array $tracksList = [];

    // 7. Tahfidz Roadmap & Jadwal Asrama
    public array $tahfidzList = [];

    public array $scheduleList = [];

    // 8. Fasilitas Kampus
    public array $facilitiesList = [];

    // 9. Prestasi, Testimoni & Agenda
    public array $achievementsList = [];

    public array $testimonialsList = [];

    public string $landing_event_1_title = '';

    public string $landing_event_1_date = '';

    public string $landing_event_1_desc = '';

    public string $landing_event_2_title = '';

    public string $landing_event_2_date = '';

    public string $landing_event_2_desc = '';

    public string $landing_event_3_title = '';

    public string $landing_event_3_date = '';

    public string $landing_event_3_desc = '';

    public array $agendaList = [];

    // 10. FAQ & Kontak
    public array $faqsList = [];

    public string $landing_whatsapp_hotline = '';

    public string $landing_office_hours = '';

    public string $company_phone = '';

    public string $company_email = '';

    public string $company_address = '';

    public string $landing_maps_embed = '';

    public string $landing_instagram_url = '';

    public string $landing_youtube_url = '';

    public string $landing_facebook_url = '';

    public string $landing_tiktok_url = '';

    // 11. Judul Seksi & Closing CTA Banner
    public array $headings = [];

    public string $landing_cta_badge = '';

    public string $landing_cta_title = '';

    public string $landing_cta_desc = '';

    public string $landing_cta_btn_primary = '';

    public string $landing_cta_btn_whatsapp = '';

    public function mount(): void
    {
        abort_unless(Auth::user()->can('update', Setting::class), 403);

        if (! in_array($this->tab, self::TABS, true)) {
            $this->tab = 'hero';
        }

        $this->loadSettings();
    }

    public function loadSettings(): void
    {
        // 1. Hero
        $hero = LandingPageData::hero();
        $this->landing_hero_badge = $hero['badge'];
        $this->landing_hero_title = $hero['title'];
        $this->landing_hero_subtitle = $hero['subtitle'];
        $this->landing_announcement_text = $hero['announcement'];
        $this->landing_hero_cta_primary = $hero['cta_primary'];
        $this->landing_hero_cta_secondary = $hero['cta_secondary'];
        $this->landing_video_url = $hero['video_url'];
        $this->landing_batch_name = $hero['batch_name'];
        $this->landing_batch_quota = $hero['batch_quota'];
        $this->landing_batch_progress = (string) $hero['batch_progress'];
        $this->landing_hero_quran_arabic = $hero['quran_arabic'] ?? '';
        $this->landing_hero_quran_translation = $hero['quran_translation'] ?? '';
        $this->landing_hero_quran_surah = $hero['quran_surah'] ?? '';
        $this->landing_hero_image = $hero['hero_image'] ?? '';
        $this->landing_hero_caption_title = $hero['hero_caption_title'] ?? '';
        $this->landing_hero_caption_sub = $hero['hero_caption_sub'] ?? '';
        $this->landing_hero_badge_1_label = $hero['badge_1_label'] ?? '';
        $this->landing_hero_badge_1_value = $hero['badge_1_value'] ?? '';
        $this->landing_hero_badge_2_label = $hero['badge_2_label'] ?? '';
        $this->landing_hero_badge_2_value = $hero['badge_2_value'] ?? '';

        // 2. Mudir
        $mudir = LandingPageData::mudir();
        $this->landing_mudir_name = $mudir['name'];
        $this->landing_mudir_title = $mudir['title'];
        $this->landing_mudir_credentials = $mudir['credentials'];
        $this->landing_mudir_quote = $mudir['quote'];
        $this->landing_mudir_headline = $mudir['headline'] ?? '';
        $this->landing_mudir_badge = $mudir['badge'] ?? '';
        $this->landing_mudir_photo_url = $mudir['photo_url'];

        // 3. Stats
        $this->statsList = LandingPageData::stats();
        $this->landing_stat_students = $this->statsList[0]['number'] ?? '1.250+';
        $this->landing_stat_tahfidz = $this->statsList[1]['number'] ?? '30 Juz';
        $this->landing_stat_teachers = $this->statsList[2]['number'] ?? '85+';
        $this->landing_stat_ptn = $this->statsList[3]['number'] ?? '100%';

        // 4. Pillars
        $this->pillarsList = LandingPageData::pillars();

        // 5. Programs
        $this->programsList = LandingPageData::programs();

        // 6. Tracks PPDB & Beasiswa
        $this->tracksList = LandingPageData::tracks();

        // 7. Tahfidz & Schedule
        $this->tahfidzList = LandingPageData::tahfidzRoadmap();
        $this->scheduleList = LandingPageData::dailySchedule();

        // 8. Facilities
        $this->facilitiesList = LandingPageData::facilities();

        // 9. Social Proof
        $this->achievementsList = LandingPageData::achievements();
        $this->testimonialsList = LandingPageData::testimonials();
        $this->agendaList = LandingPageData::agenda();
        $this->landing_event_1_title = $this->agendaList[0]['title'] ?? '';
        $this->landing_event_1_date = $this->agendaList[0]['date'] ?? '';
        $this->landing_event_1_desc = $this->agendaList[0]['desc'] ?? '';
        $this->landing_event_2_title = $this->agendaList[1]['title'] ?? '';
        $this->landing_event_2_date = $this->agendaList[1]['date'] ?? '';
        $this->landing_event_2_desc = $this->agendaList[1]['desc'] ?? '';
        $this->landing_event_3_title = $this->agendaList[2]['title'] ?? '';
        $this->landing_event_3_date = $this->agendaList[2]['date'] ?? '';
        $this->landing_event_3_desc = $this->agendaList[2]['desc'] ?? '';

        // 10. FAQ & Contact
        $this->faqsList = LandingPageData::faqs();
        $contact = LandingPageData::contact();
        $this->landing_whatsapp_hotline = $contact['whatsapp'];
        $this->company_phone = $contact['phone'];
        $this->company_email = $contact['email'];
        $this->company_address = $contact['address'];
        $this->landing_office_hours = $contact['office_hours'];
        $this->landing_maps_embed = $contact['maps_embed'];
        $this->landing_instagram_url = $contact['instagram'];
        $this->landing_youtube_url = $contact['youtube'];
        $this->landing_facebook_url = $contact['facebook'];
        $this->landing_tiktok_url = $contact['tiktok'];

        // 11. Headings & Closing CTA
        $this->headings = LandingPageData::sectionHeadings();
        $cta = LandingPageData::cta();
        $this->landing_cta_badge = $cta['badge'];
        $this->landing_cta_title = $cta['title'];
        $this->landing_cta_desc = $cta['desc'];
        $this->landing_cta_btn_primary = $cta['primary_text'];
        $this->landing_cta_btn_whatsapp = $cta['whatsapp_text'];
    }

    public function saveHero(): void
    {
        abort_unless(Auth::user()->can('update', Setting::class), 403);

        $validated = $this->validate([
            'landing_hero_badge' => ['required', 'string', 'max:150'],
            'landing_hero_title' => ['required', 'string', 'max:250'],
            'landing_hero_subtitle' => ['required', 'string', 'max:600'],
            'landing_announcement_text' => ['required', 'string', 'max:250'],
            'landing_hero_cta_primary' => ['required', 'string', 'max:60'],
            'landing_hero_cta_secondary' => ['nullable', 'string', 'max:60'],
            'landing_video_url' => ['nullable', 'string', 'max:255'],
            'landing_batch_name' => ['required', 'string', 'max:100'],
            'landing_batch_quota' => ['required', 'string', 'max:60'],
            'landing_batch_progress' => ['required', 'numeric', 'between:0,100'],
            'landing_hero_quran_arabic' => ['nullable', 'string', 'max:500'],
            'landing_hero_quran_translation' => ['nullable', 'string', 'max:800'],
            'landing_hero_quran_surah' => ['nullable', 'string', 'max:100'],
            'landing_hero_image' => ['nullable', 'string', 'max:500'],
            'landing_hero_caption_title' => ['nullable', 'string', 'max:150'],
            'landing_hero_caption_sub' => ['nullable', 'string', 'max:250'],
            'landing_hero_badge_1_label' => ['nullable', 'string', 'max:80'],
            'landing_hero_badge_1_value' => ['nullable', 'string', 'max:80'],
            'landing_hero_badge_2_label' => ['nullable', 'string', 'max:80'],
            'landing_hero_badge_2_value' => ['nullable', 'string', 'max:80'],
        ]);

        Setting::putMany($validated);

        activity('settings')->causedBy(Auth::user())->log('CMS Landing Page: Konten Hero & Banner diperbarui.');

        $this->dispatch('notify', message: __('Pengaturan Hero & Banner berhasil disimpan.'), type: 'success');
    }

    public function saveMudir(): void
    {
        abort_unless(Auth::user()->can('update', Setting::class), 403);

        $validated = $this->validate([
            'landing_mudir_name' => ['required', 'string', 'max:150'],
            'landing_mudir_title' => ['required', 'string', 'max:120'],
            'landing_mudir_credentials' => ['required', 'string', 'max:200'],
            'landing_mudir_quote' => ['required', 'string', 'max:800'],
            'landing_mudir_headline' => ['nullable', 'string', 'max:200'],
            'landing_mudir_badge' => ['nullable', 'string', 'max:100'],
            'landing_mudir_photo_url' => ['nullable', 'string', 'max:500'],
        ]);

        Setting::putMany($validated);

        activity('settings')->causedBy(Auth::user())->log('CMS Landing Page: Profil & Sambutan Mudir diperbarui.');

        $this->dispatch('notify', message: __('Profil & Sambutan Mudir berhasil disimpan.'), type: 'success');
    }

    public function saveStats(): void
    {
        abort_unless(Auth::user()->can('update', Setting::class), 403);

        // Sinkronisasi bila properti legacy diisi via test/form
        if (! empty($this->landing_stat_students) && isset($this->statsList[0])) {
            $this->statsList[0]['number'] = $this->landing_stat_students;
        }
        if (! empty($this->landing_stat_tahfidz) && isset($this->statsList[1])) {
            $this->statsList[1]['number'] = $this->landing_stat_tahfidz;
        }
        if (! empty($this->landing_stat_teachers) && isset($this->statsList[2])) {
            $this->statsList[2]['number'] = $this->landing_stat_teachers;
        }
        if (! empty($this->landing_stat_ptn) && isset($this->statsList[3])) {
            $this->statsList[3]['number'] = $this->landing_stat_ptn;
        }

        $this->validate([
            'statsList' => ['required', 'array', 'min:1'],
            'statsList.*.number' => ['required', 'string', 'max:30'],
            'statsList.*.label' => ['required', 'string', 'max:60'],
            'statsList.*.sub' => ['required', 'string', 'max:100'],
        ]);

        // Sinkronisasi data single untuk backward-compat
        $this->landing_stat_students = $this->statsList[0]['number'] ?? $this->landing_stat_students;
        $this->landing_stat_tahfidz = $this->statsList[1]['number'] ?? $this->landing_stat_tahfidz;
        $this->landing_stat_teachers = $this->statsList[2]['number'] ?? $this->landing_stat_teachers;
        $this->landing_stat_ptn = $this->statsList[3]['number'] ?? $this->landing_stat_ptn;

        Setting::putMany([
            'landing_stat_students' => $this->landing_stat_students,
            'landing_stat_tahfidz' => $this->landing_stat_tahfidz,
            'landing_stat_teachers' => $this->landing_stat_teachers,
            'landing_stat_ptn' => $this->landing_stat_ptn,
            'landing_stats_json' => json_encode($this->statsList, JSON_UNESCAPED_UNICODE),
        ]);

        activity('settings')->causedBy(Auth::user())->log('CMS Landing Page: Statistik & Metrik diperbarui.');

        $this->dispatch('notify', message: __('Statistik unggulan berhasil disimpan.'), type: 'success');
    }

    public function addStat(): void
    {
        $this->statsList[] = [
            'number' => '100+',
            'label' => 'Metrik Baru',
            'sub' => 'Keterangan pencapaian',
            'icon' => 'award',
        ];
    }

    public function removeStat(int $index): void
    {
        if (isset($this->statsList[$index])) {
            unset($this->statsList[$index]);
            $this->statsList = array_values($this->statsList);
        }
    }

    public function savePillars(): void
    {
        abort_unless(Auth::user()->can('update', Setting::class), 403);

        $this->validate([
            'pillarsList' => ['required', 'array', 'min:1'],
            'pillarsList.*.title' => ['required', 'string', 'max:100'],
            'pillarsList.*.badge' => ['required', 'string', 'max:50'],
            'pillarsList.*.desc' => ['required', 'string', 'max:400'],
        ]);

        Setting::put('landing_pillars_json', json_encode($this->pillarsList, JSON_UNESCAPED_UNICODE));

        activity('settings')->causedBy(Auth::user())->log('CMS Landing Page: Pilar Keunggulan diperbarui.');

        $this->dispatch('notify', message: __('Pilar keunggulan berhasil disimpan.'), type: 'success');
    }

    public function addPillar(): void
    {
        $this->pillarsList[] = [
            'title' => '',
            'badge' => 'Keunggulan',
            'desc' => '',
            'icon' => 'award',
            'points' => [],
        ];
    }

    public function removePillar(int $index): void
    {
        if (isset($this->pillarsList[$index])) {
            unset($this->pillarsList[$index]);
            $this->pillarsList = array_values($this->pillarsList);
        }
    }

    public function savePrograms(): void
    {
        abort_unless(Auth::user()->can('update', Setting::class), 403);

        $this->validate([
            'programsList' => ['required', 'array', 'min:1'],
            'programsList.*.name' => ['required', 'string', 'max:100'],
            'programsList.*.badge' => ['required', 'string', 'max:50'],
            'programsList.*.level' => ['required', 'string', 'max:60'],
            'programsList.*.age' => ['required', 'string', 'max:60'],
            'programsList.*.desc' => ['required', 'string', 'max:500'],
            'programsList.*.quote' => ['required', 'string', 'max:300'],
        ]);

        Setting::put('landing_programs_json', json_encode($this->programsList, JSON_UNESCAPED_UNICODE));

        activity('settings')->causedBy(Auth::user())->log('CMS Landing Page: Program Akademik diperbarui.');

        $this->dispatch('notify', message: __('Program jenjang pendidikan berhasil disimpan.'), type: 'success');
    }

    public function addProgram(): void
    {
        $this->programsList[] = [
            'id' => 'program-'.(count($this->programsList) + 1),
            'name' => '',
            'badge' => 'Akreditasi A',
            'level' => 'Tingkat Baru',
            'age' => 'Rentang Usia',
            'desc' => '',
            'features' => [],
            'quote' => '',
        ];
    }

    public function removeProgram(int $index): void
    {
        if (isset($this->programsList[$index])) {
            unset($this->programsList[$index]);
            $this->programsList = array_values($this->programsList);
        }
    }

    public function saveTracks(): void
    {
        abort_unless(Auth::user()->can('update', Setting::class), 403);

        $this->validate([
            'tracksList' => ['required', 'array', 'min:1'],
            'tracksList.*.name' => ['required', 'string', 'max:100'],
            'tracksList.*.badge' => ['required', 'string', 'max:60'],
            'tracksList.*.quota' => ['required', 'string', 'max:60'],
            'tracksList.*.desc' => ['required', 'string', 'max:500'],
            'tracksList.*.highlight' => ['nullable', 'string', 'max:200'],
            'tracksList.*.benefits' => ['nullable', 'array'],
            'tracksList.*.benefits.*' => ['required', 'string', 'max:150'],
            'tracksList.*.cta_text' => ['required', 'string', 'max:60'],
            'tracksList.*.featured' => ['nullable', 'boolean'],
        ]);

        Setting::put('landing_tracks_json', json_encode($this->tracksList, JSON_UNESCAPED_UNICODE));

        activity('settings')->causedBy(Auth::user())->log('CMS Landing Page: Jalur Masuk Seleksi & Beasiswa diperbarui.');

        $this->dispatch('notify', message: __('Jalur Masuk Seleksi & Beasiswa berhasil disimpan.'), type: 'success');
    }

    public function addTrack(): void
    {
        $this->tracksList[] = [
            'id' => 'jalur-'.(count($this->tracksList) + 1),
            'name' => '',
            'badge' => 'Jalur Baru',
            'highlight' => '',
            'quota' => 'Kuota Tersedia',
            'icon' => 'award',
            'color' => 'emerald',
            'desc' => '',
            'benefits' => ['Fasilitas & Pendampingan Santri'],
            'cta_text' => 'Pilih Jalur',
            'featured' => false,
        ];
    }

    public function removeTrack(int $index): void
    {
        if (isset($this->tracksList[$index])) {
            unset($this->tracksList[$index]);
            $this->tracksList = array_values($this->tracksList);
        }
    }

    public function addTrackBenefit(int $trackIndex): void
    {
        if (isset($this->tracksList[$trackIndex])) {
            $this->tracksList[$trackIndex]['benefits'][] = '';
        }
    }

    public function removeTrackBenefit(int $trackIndex, int $benefitIndex): void
    {
        if (isset($this->tracksList[$trackIndex]['benefits'][$benefitIndex])) {
            unset($this->tracksList[$trackIndex]['benefits'][$benefitIndex]);
            $this->tracksList[$trackIndex]['benefits'] = array_values($this->tracksList[$trackIndex]['benefits']);
        }
    }

    public function saveTahfidzAndSchedule(): void
    {
        abort_unless(Auth::user()->can('update', Setting::class), 403);

        $this->validate([
            'tahfidzList' => ['required', 'array', 'min:1'],
            'tahfidzList.*.title' => ['required', 'string', 'max:100'],
            'tahfidzList.*.desc' => ['required', 'string', 'max:300'],
            'tahfidzList.*.target' => ['required', 'string', 'max:80'],
            'scheduleList' => ['required', 'array', 'min:1'],
            'scheduleList.*.period' => ['required', 'string', 'max:80'],
            'scheduleList.*.time' => ['required', 'string', 'max:80'],
            'scheduleList.*.title' => ['required', 'string', 'max:100'],
            'scheduleList.*.desc' => ['required', 'string', 'max:400'],
        ]);

        Setting::put('landing_tahfidz_json', json_encode($this->tahfidzList, JSON_UNESCAPED_UNICODE));
        Setting::put('landing_schedule_json', json_encode($this->scheduleList, JSON_UNESCAPED_UNICODE));

        activity('settings')->causedBy(Auth::user())->log('CMS Landing Page: Tahfidz & Jadwal Keasramaan diperbarui.');

        $this->dispatch('notify', message: __('Tahapan Tahfidz & Jadwal Keasramaan berhasil disimpan.'), type: 'success');
    }

    public function addTahfidzLevel(): void
    {
        $stepNum = str_pad((string) (count($this->tahfidzList) + 1), 2, '0', STR_PAD_LEFT);
        $this->tahfidzList[] = [
            'step' => $stepNum,
            'title' => '',
            'desc' => '',
            'target' => '',
        ];
    }

    public function removeTahfidzLevel(int $index): void
    {
        if (isset($this->tahfidzList[$index])) {
            unset($this->tahfidzList[$index]);
            $this->tahfidzList = array_values($this->tahfidzList);
        }
    }

    public function addScheduleItem(): void
    {
        $this->scheduleList[] = [
            'period' => 'Waktu / Periode',
            'time' => '00.00 - 00.00 WIB',
            'title' => '',
            'desc' => '',
        ];
    }

    public function removeScheduleItem(int $index): void
    {
        if (isset($this->scheduleList[$index])) {
            unset($this->scheduleList[$index]);
            $this->scheduleList = array_values($this->scheduleList);
        }
    }

    public function saveFacilities(): void
    {
        abort_unless(Auth::user()->can('update', Setting::class), 403);

        $this->validate([
            'facilitiesList' => ['required', 'array', 'min:1'],
            'facilitiesList.*.name' => ['required', 'string', 'max:100'],
            'facilitiesList.*.category' => ['required', 'string', 'max:60'],
            'facilitiesList.*.desc' => ['required', 'string', 'max:300'],
            'facilitiesList.*.image' => ['required', 'string', 'max:500'],
            'facilitiesList.*.tag' => ['required', 'string', 'max:60'],
        ]);

        Setting::put('landing_facilities_json', json_encode($this->facilitiesList, JSON_UNESCAPED_UNICODE));

        activity('settings')->causedBy(Auth::user())->log('CMS Landing Page: Fasilitas Kampus diperbarui.');

        $this->dispatch('notify', message: __('Daftar fasilitas kampus berhasil disimpan.'), type: 'success');
    }

    public function addFacility(): void
    {
        $this->facilitiesList[] = [
            'name' => '',
            'category' => 'Sarana & Prasarana',
            'desc' => '',
            'image' => 'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?auto=format&fit=crop&w=800&q=80',
            'tag' => 'Fasilitas Unggulan',
        ];
    }

    public function removeFacility(int $index): void
    {
        if (isset($this->facilitiesList[$index])) {
            unset($this->facilitiesList[$index]);
            $this->facilitiesList = array_values($this->facilitiesList);
        }
    }

    public function saveAgenda(): void
    {
        abort_unless(Auth::user()->can('update', Setting::class), 403);

        // Sinkronisasi jika properti individual diset lewat test atau legacy call
        if (! empty($this->landing_event_1_title)) {
            if (! isset($this->agendaList[0])) {
                $this->agendaList[0] = [];
            }
            $this->agendaList[0]['title'] = $this->landing_event_1_title;
            $this->agendaList[0]['date'] = $this->landing_event_1_date;
            $this->agendaList[0]['desc'] = $this->landing_event_1_desc;
            $this->agendaList[0]['badge'] = $this->agendaList[0]['badge'] ?? 'Agenda';
            $this->agendaList[0]['location'] = $this->agendaList[0]['location'] ?? 'Kampus Utama';
        }
        if (! empty($this->landing_event_2_title)) {
            if (! isset($this->agendaList[1])) {
                $this->agendaList[1] = [];
            }
            $this->agendaList[1]['title'] = $this->landing_event_2_title;
            $this->agendaList[1]['date'] = $this->landing_event_2_date;
            $this->agendaList[1]['desc'] = $this->landing_event_2_desc;
            $this->agendaList[1]['badge'] = $this->agendaList[1]['badge'] ?? 'Agenda';
            $this->agendaList[1]['location'] = $this->agendaList[1]['location'] ?? 'Kampus Utama';
        }
        if (! empty($this->landing_event_3_title)) {
            if (! isset($this->agendaList[2])) {
                $this->agendaList[2] = [];
            }
            $this->agendaList[2]['title'] = $this->landing_event_3_title;
            $this->agendaList[2]['date'] = $this->landing_event_3_date;
            $this->agendaList[2]['desc'] = $this->landing_event_3_desc;
            $this->agendaList[2]['badge'] = $this->agendaList[2]['badge'] ?? 'Agenda';
            $this->agendaList[2]['location'] = $this->agendaList[2]['location'] ?? 'Kampus Utama';
        }

        $this->validate([
            'agendaList' => ['required', 'array', 'min:1'],
            'agendaList.*.title' => ['required', 'string', 'max:150'],
            'agendaList.*.date' => ['required', 'string', 'max:80'],
            'agendaList.*.desc' => ['required', 'string', 'max:500'],
            'agendaList.*.location' => ['nullable', 'string', 'max:150'],
            'agendaList.*.badge' => ['nullable', 'string', 'max:60'],
        ]);

        // Mirror ke legacy setting bila ada
        $legacy = [];
        for ($i = 1; $i <= 3; $i++) {
            $idx = $i - 1;
            if (isset($this->agendaList[$idx])) {
                $legacy["landing_event_{$i}_title"] = $this->agendaList[$idx]['title'];
                $legacy["landing_event_{$i}_date"] = $this->agendaList[$idx]['date'];
                $legacy["landing_event_{$i}_desc"] = $this->agendaList[$idx]['desc'];
                $this->{"landing_event_{$i}_title"} = $this->agendaList[$idx]['title'];
                $this->{"landing_event_{$i}_date"} = $this->agendaList[$idx]['date'];
                $this->{"landing_event_{$i}_desc"} = $this->agendaList[$idx]['desc'];
            }
        }
        if (! empty($legacy)) {
            Setting::putMany($legacy);
        }

        Setting::put('landing_agenda_json', json_encode($this->agendaList, JSON_UNESCAPED_UNICODE));

        activity('settings')->causedBy(Auth::user())->log('CMS Landing Page: Agenda kegiatan diperbarui.');

        $this->dispatch('notify', message: __('Agenda kegiatan berhasil disimpan.'), type: 'success');
    }

    public function addAgenda(): void
    {
        $this->agendaList[] = [
            'title' => '',
            'date' => '',
            'location' => 'Kampus Utama',
            'desc' => '',
            'badge' => 'Agenda',
        ];
    }

    public function removeAgenda(int $index): void
    {
        if (isset($this->agendaList[$index])) {
            unset($this->agendaList[$index]);
            $this->agendaList = array_values($this->agendaList);
        }
    }

    public function saveSocialProof(): void
    {
        abort_unless(Auth::user()->can('update', Setting::class), 403);

        $this->validate([
            'achievementsList' => ['required', 'array', 'min:1'],
            'achievementsList.*.year' => ['required', 'string', 'max:10'],
            'achievementsList.*.title' => ['required', 'string', 'max:150'],
            'achievementsList.*.event' => ['required', 'string', 'max:150'],
            'achievementsList.*.student' => ['required', 'string', 'max:120'],
            'achievementsList.*.category' => ['required', 'string', 'max:60'],
            'testimonialsList' => ['required', 'array', 'min:1'],
            'testimonialsList.*.name' => ['required', 'string', 'max:100'],
            'testimonialsList.*.role' => ['required', 'string', 'max:100'],
            'testimonialsList.*.child' => ['required', 'string', 'max:100'],
            'testimonialsList.*.quote' => ['required', 'string', 'max:500'],
            'testimonialsList.*.rating' => ['required', 'numeric', 'between:1,5'],
        ]);

        Setting::put('landing_achievements_json', json_encode($this->achievementsList, JSON_UNESCAPED_UNICODE));
        Setting::put('landing_testimonials_json', json_encode($this->testimonialsList, JSON_UNESCAPED_UNICODE));

        // Juga simpan agenda jika terisi
        if (! empty($this->agendaList)) {
            $this->saveAgenda();
        }

        activity('settings')->causedBy(Auth::user())->log('CMS Landing Page: Prestasi, Testimoni & Agenda diperbarui.');

        $this->dispatch('notify', message: __('Prestasi & Testimoni berhasil disimpan.'), type: 'success');
    }

    public function addAchievement(): void
    {
        $this->achievementsList[] = [
            'year' => (string) date('Y'),
            'title' => '',
            'event' => '',
            'student' => '',
            'category' => 'Prestasi Santri',
        ];
    }

    public function removeAchievement(int $index): void
    {
        if (isset($this->achievementsList[$index])) {
            unset($this->achievementsList[$index]);
            $this->achievementsList = array_values($this->achievementsList);
        }
    }

    public function addTestimonial(): void
    {
        $this->testimonialsList[] = [
            'name' => '',
            'role' => 'Wali Santri',
            'child' => '',
            'quote' => '',
            'rating' => 5,
            'avatar' => '',
        ];
    }

    public function removeTestimonial(int $index): void
    {
        if (isset($this->testimonialsList[$index])) {
            unset($this->testimonialsList[$index]);
            $this->testimonialsList = array_values($this->testimonialsList);
        }
    }

    public function saveContact(): void
    {
        abort_unless(Auth::user()->can('update', Setting::class), 403);

        $validated = $this->validate([
            'landing_whatsapp_hotline' => ['required', 'string', 'max:50'],
            'landing_office_hours' => ['required', 'string', 'max:100'],
            'company_phone' => ['nullable', 'string', 'max:50'],
            'company_email' => ['nullable', 'email', 'max:100'],
            'company_address' => ['nullable', 'string', 'max:250'],
            'landing_maps_embed' => ['nullable', 'string', 'max:1000'],
            'landing_instagram_url' => ['nullable', 'string', 'max:255'],
            'landing_youtube_url' => ['nullable', 'string', 'max:255'],
            'landing_facebook_url' => ['nullable', 'string', 'max:255'],
            'landing_tiktok_url' => ['nullable', 'string', 'max:255'],
        ]);

        Setting::putMany(array_filter($validated, fn ($val) => $val !== null));

        activity('settings')->causedBy(Auth::user())->log('CMS Landing Page: Kontak & Jam Layanan diperbarui.');

        $this->dispatch('notify', message: __('Kontak & Jam Layanan berhasil disimpan.'), type: 'success');
    }

    public function saveFaqAndContact(): void
    {
        abort_unless(Auth::user()->can('update', Setting::class), 403);

        $this->validate([
            'faqsList' => ['required', 'array', 'min:1'],
            'faqsList.*.q' => ['required', 'string', 'max:200'],
            'faqsList.*.a' => ['required', 'string', 'max:1000'],
            'faqsList.*.category' => ['required', 'string', 'max:50'],
        ]);

        Setting::put('landing_faqs_json', json_encode($this->faqsList, JSON_UNESCAPED_UNICODE));

        $this->saveContact();

        activity('settings')->causedBy(Auth::user())->log('CMS Landing Page: FAQ & Kontak diperbarui.');

        $this->dispatch('notify', message: __('FAQ & Informasi Kontak berhasil disimpan.'), type: 'success');
    }

    public function addFaq(): void
    {
        $this->faqsList[] = [
            'q' => '',
            'a' => '',
            'category' => 'Umum',
        ];
    }

    public function removeFaq(int $index): void
    {
        if (isset($this->faqsList[$index])) {
            unset($this->faqsList[$index]);
            $this->faqsList = array_values($this->faqsList);
        }
    }

    public function saveHeadingsAndCta(): void
    {
        abort_unless(Auth::user()->can('update', Setting::class), 403);

        $this->validate([
            'headings' => ['required', 'array'],
            'headings.*.badge' => ['nullable', 'string', 'max:80'],
            'headings.*.title' => ['required', 'string', 'max:200'],
            'headings.*.sub' => ['nullable', 'string', 'max:500'],
            'landing_cta_badge' => ['required', 'string', 'max:100'],
            'landing_cta_title' => ['required', 'string', 'max:200'],
            'landing_cta_desc' => ['required', 'string', 'max:500'],
            'landing_cta_btn_primary' => ['required', 'string', 'max:60'],
            'landing_cta_btn_whatsapp' => ['required', 'string', 'max:60'],
        ]);

        Setting::put('landing_section_headings_json', json_encode($this->headings, JSON_UNESCAPED_UNICODE));
        Setting::putMany([
            'landing_cta_badge' => $this->landing_cta_badge,
            'landing_cta_title' => $this->landing_cta_title,
            'landing_cta_desc' => $this->landing_cta_desc,
            'landing_cta_btn_primary' => $this->landing_cta_btn_primary,
            'landing_cta_btn_whatsapp' => $this->landing_cta_btn_whatsapp,
        ]);

        activity('settings')->causedBy(Auth::user())->log('CMS Landing Page: Judul Seksi & Banner CTA diperbarui.');

        $this->dispatch('notify', message: __('Judul seksi dan banner penutup berhasil disimpan.'), type: 'success');
    }

    public function render()
    {
        return view('livewire.settings.landing-page-cms');
    }
}
