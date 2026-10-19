<?php

use App\Livewire\Settings\LandingPageCms;
use App\Models\Setting;
use Livewire\Livewire;

test('guests are redirected to the login page', function () {
    $this->get(route('settings.landing-page'))->assertRedirect('/login');
});

test('unauthorized users cannot access landing page cms', function () {
    actingAsRole('guardian');

    $this->get(route('settings.landing-page'))->assertForbidden();
});

test('admin can access landing page cms', function () {
    actingAsAdmin();

    $this->get(route('settings.landing-page'))
        ->assertOk()
        ->assertSee('CMS Landing Page')
        ->assertSeeLivewire(LandingPageCms::class);
});

test('admin can update hero section', function () {
    actingAsAdmin();

    Livewire::test(LandingPageCms::class)
        ->set('landing_hero_badge', 'PPDB 2026/2027 Resmi Dibuka')
        ->set('landing_hero_title', 'Sekolah Islam Modern Berkarakter Global')
        ->set('landing_hero_subtitle', 'Mencetak kader ulama, intelektual muslim, dan pemimpin masa depan dengan kurikulum internasional.')
        ->set('landing_hero_cta_primary', 'Daftar Sekarang')
        ->set('landing_batch_name', 'Gelombang 1 Khusus')
        ->set('landing_batch_quota', 'Tersisa 15 Kursi')
        ->set('landing_batch_progress', '85')
        ->call('saveHero')
        ->assertHasNoErrors()
        ->assertDispatched('notify');

    expect(Setting::get('landing_hero_title'))->toBe('Sekolah Islam Modern Berkarakter Global')
        ->and(Setting::get('landing_hero_badge'))->toBe('PPDB 2026/2027 Resmi Dibuka')
        ->and(Setting::get('landing_batch_quota'))->toBe('Tersisa 15 Kursi');
});

test('admin can update mudir section', function () {
    actingAsAdmin();

    Livewire::test(LandingPageCms::class)
        ->set('landing_mudir_name', 'Prof. Dr. KH. Mahmud Yunus, M.A.')
        ->set('landing_mudir_title', 'Pimpinan Pondok Pesantren')
        ->set('landing_mudir_credentials', 'Universitas Al-Azhar Kairo · Doktor Pendidikan Islam')
        ->set('landing_mudir_quote', 'Pendidikan adalah proses memanusiakan manusia dengan bimbingan wahyu ilahi.')
        ->call('saveMudir')
        ->assertHasNoErrors()
        ->assertDispatched('notify');

    expect(Setting::get('landing_mudir_name'))->toBe('Prof. Dr. KH. Mahmud Yunus, M.A.')
        ->and(Setting::get('landing_mudir_quote'))->toContain('Pendidikan adalah proses');
});

test('admin can update stats section', function () {
    actingAsAdmin();

    Livewire::test(LandingPageCms::class)
        ->set('landing_stat_students', '2.500+')
        ->set('landing_stat_tahfidz', '30 Juz Mutqin')
        ->set('landing_stat_teachers', '120+')
        ->set('landing_stat_ptn', '98%')
        ->call('saveStats')
        ->assertHasNoErrors()
        ->assertDispatched('notify');

    expect(Setting::get('landing_stat_students'))->toBe('2.500+')
        ->and(Setting::get('landing_stat_ptn'))->toBe('98%')
        ->and(Setting::get('landing_stats_json'))->not->toBeNull();
});

test('admin can update pillars section', function () {
    actingAsAdmin();

    Livewire::test(LandingPageCms::class)
        ->set('pillarsList.0.title', 'Tahfidz 30 Juz Bersanad Emas')
        ->set('pillarsList.0.badge', 'Sanad Emas')
        ->set('pillarsList.0.desc', 'Metode mutqin talaqqi dengan dewan asatidz teruji.')
        ->call('savePillars')
        ->assertHasNoErrors()
        ->assertDispatched('notify');

    expect(Setting::get('landing_pillars_json'))->toContain('Tahfidz 30 Juz Bersanad Emas');
});

test('admin can update programs section', function () {
    actingAsAdmin();

    Livewire::test(LandingPageCms::class)
        ->set('programsList.0.name', 'MTs Sains Internasional')
        ->set('programsList.0.badge', 'Akreditasi A Unggul')
        ->set('programsList.0.level', 'Tingkat Menengah Pertama')
        ->set('programsList.0.age', 'Usia 12-15 Tahun')
        ->set('programsList.0.desc', 'Fokus pada riset dasar sains dan tahfidz.')
        ->set('programsList.0.quote', 'Membina nalar kritis sejak dini.')
        ->call('savePrograms')
        ->assertHasNoErrors()
        ->assertDispatched('notify');

    expect(Setting::get('landing_programs_json'))->toContain('MTs Sains Internasional');
});

test('admin can update tahfidz roadmap and schedule', function () {
    actingAsAdmin();

    Livewire::test(LandingPageCms::class)
        ->set('tahfidzList.0.title', 'Tahsin & Standarisasi Makharij')
        ->set('scheduleList.0.title', 'Shalat Tahajjud & Halaqah Fajar')
        ->call('saveTahfidzAndSchedule')
        ->assertHasNoErrors()
        ->assertDispatched('notify');

    expect(Setting::get('landing_tahfidz_json'))->toContain('Tahsin & Standarisasi Makharij')
        ->and(Setting::get('landing_schedule_json'))->toContain('Shalat Tahajjud & Halaqah Fajar');
});

test('admin can update facilities section', function () {
    actingAsAdmin();

    Livewire::test(LandingPageCms::class)
        ->set('facilitiesList.0.name', 'Masjid Raya Al-Muhajirin')
        ->call('saveFacilities')
        ->assertHasNoErrors()
        ->assertDispatched('notify');

    expect(Setting::get('landing_facilities_json'))->toContain('Masjid Raya Al-Muhajirin');
});

test('admin can update social proof section', function () {
    actingAsAdmin();

    Livewire::test(LandingPageCms::class)
        ->set('achievementsList.0.title', 'Medali Perak Olimpiade Sains Internasional')
        ->set('testimonialsList.0.name', 'Drs. H. Ahmad Fauzan, M.Pd.')
        ->call('saveSocialProof')
        ->assertHasNoErrors()
        ->assertDispatched('notify');

    expect(Setting::get('landing_achievements_json'))->toContain('Medali Perak Olimpiade Sains')
        ->and(Setting::get('landing_testimonials_json'))->toContain('Ahmad Fauzan');
});

test('admin can update faq and contact section', function () {
    actingAsAdmin();

    Livewire::test(LandingPageCms::class)
        ->set('faqsList.0.q', 'Bagaimana cara mendaftar santri baru secara online?')
        ->set('landing_whatsapp_hotline', '0812-9999-8888')
        ->set('landing_office_hours', 'Senin - Ahad 24 Jam')
        ->call('saveFaqAndContact')
        ->assertHasNoErrors()
        ->assertDispatched('notify');

    expect(Setting::get('landing_faqs_json'))->toContain('Bagaimana cara mendaftar santri baru')
        ->and(Setting::get('landing_whatsapp_hotline'))->toBe('0812-9999-8888');
});

test('admin can update agenda section', function () {
    actingAsAdmin();

    Livewire::test(LandingPageCms::class)
        ->set('landing_event_1_title', 'Olimpiade Sains & Qur\'an Nasional')
        ->set('landing_event_1_date', '10 Desember 2026')
        ->set('landing_event_1_desc', 'Kompetisi tahunan bidang sains terpadu dan hifdzil quran tingkat nasional.')
        ->call('saveAgenda')
        ->assertHasNoErrors()
        ->assertDispatched('notify');

    expect(Setting::get('landing_event_1_title'))->toBe('Olimpiade Sains & Qur\'an Nasional');
});

test('admin can update contact section', function () {
    actingAsAdmin();

    Livewire::test(LandingPageCms::class)
        ->set('landing_whatsapp_hotline', '0812-9999-8888')
        ->set('landing_office_hours', 'Senin - Ahad 24 Jam')
        ->call('saveContact')
        ->assertHasNoErrors()
        ->assertDispatched('notify');

    expect(Setting::get('landing_whatsapp_hotline'))->toBe('0812-9999-8888')
        ->and(Setting::get('landing_office_hours'))->toBe('Senin - Ahad 24 Jam');
});

test('admin can update tracks section and manage benefits', function () {
    actingAsAdmin();

    Livewire::test(LandingPageCms::class)
        ->set('tracksList.0.name', 'Jalur Beasiswa Khusus Santri Teladan')
        ->set('tracksList.0.highlight', 'Beasiswa SPP & Asrama 100%')
        ->call('addTrackBenefit', 0)
        ->set('tracksList.0.benefits.3', 'Fasilitas Laptop Penunjang Riset')
        ->call('saveTracks')
        ->assertHasNoErrors()
        ->assertDispatched('notify');

    expect(Setting::get('landing_tracks_json'))
        ->toContain('Jalur Beasiswa Khusus Santri Teladan')
        ->toContain('Fasilitas Laptop Penunjang Riset');
});

test('admin can update section headings and closing cta banner', function () {
    actingAsAdmin();

    Livewire::test(LandingPageCms::class)
        ->set('landing_cta_title', 'Mari Bergabung Membangun Generasi Gemilang 2026')
        ->set('landing_cta_btn_primary', 'Daftar Sekarang Juga')
        ->set('headings.pillars.title', 'Pondasi Karakter & Intelektual Santri')
        ->call('saveHeadingsAndCta')
        ->assertHasNoErrors()
        ->assertDispatched('notify');

    expect(Setting::get('landing_cta_title'))->toBe('Mari Bergabung Membangun Generasi Gemilang 2026')
        ->and(Setting::get('landing_cta_btn_primary'))->toBe('Daftar Sekarang Juga')
        ->and(Setting::get('landing_section_headings_json'))->toContain('Pondasi Karakter & Intelektual Santri');
});

test('public landing page reflects updated settings from CMS', function () {
    Setting::put('landing_hero_title', 'Sekolah Peradaban Islam Masa Depan');
    Setting::put('landing_mudir_name', 'Kiai Haji Syamsul Arifin');
    Setting::put('landing_stat_students', '3.000+');
    Setting::put('landing_cta_title', 'Mari Bergabung Membangun Generasi Gemilang 2026');

    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('Sekolah Peradaban Islam Masa Depan')
        ->assertSee('Kiai Haji Syamsul Arifin')
        ->assertSee('3.000+')
        ->assertSee('Mari Bergabung Membangun Generasi Gemilang 2026');
});

test('admin can dynamically add and remove achievements without static limits', function () {
    actingAsAdmin();

    $component = Livewire::test(LandingPageCms::class);
    $initialCount = count($component->get('achievementsList'));

    $component->call('addAchievement');
    expect(count($component->get('achievementsList')))->toBe($initialCount + 1);

    $component->set("achievementsList.{$initialCount}.title", 'Juara 1 Debat Bahasa Arab Internasional')
        ->set("achievementsList.{$initialCount}.year", '2027')
        ->set("achievementsList.{$initialCount}.event", 'Asean Arabic Championship')
        ->set("achievementsList.{$initialCount}.student", 'Ahmad Syakir')
        ->set("achievementsList.{$initialCount}.category", 'Bahasa')
        ->call('saveSocialProof')
        ->assertHasNoErrors();

    expect(Setting::get('landing_achievements_json'))->toContain('Juara 1 Debat Bahasa Arab Internasional');

    // Hapus item yang baru ditambahkan
    $component->call('removeAchievement', $initialCount);
    expect(count($component->get('achievementsList')))->toBe($initialCount);
    $component->call('saveSocialProof')->assertHasNoErrors();
    expect(Setting::get('landing_achievements_json'))->not->toContain('Juara 1 Debat Bahasa Arab Internasional');
});

test('admin can dynamically add and remove faqs without static limits', function () {
    actingAsAdmin();

    $component = Livewire::test(LandingPageCms::class);
    $initialCount = count($component->get('faqsList'));

    $component->call('addFaq');
    expect(count($component->get('faqsList')))->toBe($initialCount + 1);

    $component->set("faqsList.{$initialCount}.q", 'Apakah santri mendapatkan laundry gratis?')
        ->set("faqsList.{$initialCount}.a", 'Ya, layanan laundry pakaian harian santri sudah termasuk dalam SPP.')
        ->set("faqsList.{$initialCount}.category", 'Fasilitas')
        ->call('saveFaqAndContact')
        ->assertHasNoErrors();

    expect(Setting::get('landing_faqs_json'))->toContain('Apakah santri mendapatkan laundry gratis?');

    // Hapus FAQ
    $component->call('removeFaq', $initialCount);
    expect(count($component->get('faqsList')))->toBe($initialCount);
    $component->call('saveFaqAndContact')->assertHasNoErrors();
    expect(Setting::get('landing_faqs_json'))->not->toContain('Apakah santri mendapatkan laundry gratis?');
});

test('admin can dynamically add and remove agenda without static limits', function () {
    actingAsAdmin();

    $component = Livewire::test(LandingPageCms::class);
    $initialCount = count($component->get('agendaList'));

    $component->call('addAgenda');
    expect(count($component->get('agendaList')))->toBe($initialCount + 1);

    $component->set("agendaList.{$initialCount}.title", 'Khotmil Qur\'an Akbar dan Wisuda Sanad')
        ->set("agendaList.{$initialCount}.date", '15 Juli 2027')
        ->set("agendaList.{$initialCount}.desc", 'Prosesi khataman akbar santri tahfidz 30 juz.')
        ->set("agendaList.{$initialCount}.badge", 'Wisuda')
        ->set("agendaList.{$initialCount}.location", 'Masjid Jami')
        ->call('saveAgenda')
        ->assertHasNoErrors();

    expect(Setting::get('landing_agenda_json'))->toContain('Khotmil Qur\'an Akbar dan Wisuda Sanad');

    // Hapus agenda
    $component->call('removeAgenda', $initialCount);
    expect(count($component->get('agendaList')))->toBe($initialCount);
    $component->call('saveAgenda')->assertHasNoErrors();
    expect(Setting::get('landing_agenda_json'))->not->toContain('Khotmil Qur\'an Akbar dan Wisuda Sanad');
});

test('admin can dynamically add and remove tracks, facilities, pillars, and programs', function () {
    actingAsAdmin();

    $component = Livewire::test(LandingPageCms::class);

    // Tracks
    $initialTracks = count($component->get('tracksList'));
    $component->call('addTrack');
    expect(count($component->get('tracksList')))->toBe($initialTracks + 1);
    $component->call('removeTrack', $initialTracks);
    expect(count($component->get('tracksList')))->toBe($initialTracks);

    // Facilities
    $initialFacilities = count($component->get('facilitiesList'));
    $component->call('addFacility');
    expect(count($component->get('facilitiesList')))->toBe($initialFacilities + 1);
    $component->call('removeFacility', $initialFacilities);
    expect(count($component->get('facilitiesList')))->toBe($initialFacilities);

    // Pillars
    $initialPillars = count($component->get('pillarsList'));
    $component->call('addPillar');
    expect(count($component->get('pillarsList')))->toBe($initialPillars + 1);
    $component->call('removePillar', $initialPillars);
    expect(count($component->get('pillarsList')))->toBe($initialPillars);

    // Programs
    $initialPrograms = count($component->get('programsList'));
    $component->call('addProgram');
    expect(count($component->get('programsList')))->toBe($initialPrograms + 1);
    $component->call('removeProgram', $initialPrograms);
    expect(count($component->get('programsList')))->toBe($initialPrograms);
});
