<?php

namespace App\Livewire\Settings;

use App\Models\Setting;
use App\Support\Branding;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * Branding aplikasi (nama, tagline, logo) dan kop surat dokumen cetak. Semua dibaca lewat
 * App\Support\Branding / Setting, jadi tidak ada nama aplikasi atau kop surat yang di-hardcode
 * di layout maupun file cetak.
 */
#[Layout('layouts.app', ['heading' => 'Profil Lembaga & Sekolah'])]
#[Title('Profil Lembaga & Sekolah')]
class CompanyProfile extends Component
{
    use WithFileUploads;

    public const TABS = ['branding', 'kop-surat', 'presensi-guru'];

    #[Url(as: 'tab', except: 'branding')]
    public string $tab = 'branding';

    public string $app_name = '';

    public string $app_tagline = '';

    public ?TemporaryUploadedFile $logo = null;

    public string $company_name = '';

    public string $company_tagline = '';

    public string $company_address = '';

    public string $company_phone = '';

    public string $school_lat = '';

    public string $school_lng = '';

    public string $school_radius_meters = '';

    public string $teacher_check_in_late = '';

    public function mount(): void
    {
        abort_unless(Auth::user()->can('update', Setting::class), 403);

        if (! in_array($this->tab, self::TABS, true)) {
            $this->tab = 'branding';
        }

        $this->app_name = Setting::get(Branding::APP_NAME_KEY, '');
        $this->app_tagline = Setting::get(Branding::APP_TAGLINE_KEY, '');
        $this->company_name = Setting::get('company_name', '');
        $this->company_tagline = Setting::get('company_tagline', '');
        $this->company_address = Setting::get('company_address', '');
        $this->company_phone = Setting::get('company_phone', '');
        $this->school_lat = (string) Setting::get('school_lat', '');
        $this->school_lng = (string) Setting::get('school_lng', '');
        $this->school_radius_meters = (string) Setting::get('school_radius_meters', '100');
        $this->teacher_check_in_late = (string) Setting::get('teacher_check_in_late', '07:30');
    }

    public function saveTeacherAttendance(): void
    {
        abort_unless(Auth::user()->can('update', Setting::class), 403);

        $validated = $this->validate([
            'school_lat' => ['required', 'numeric', 'between:-90,90'],
            'school_lng' => ['required', 'numeric', 'between:-180,180'],
            'school_radius_meters' => ['required', 'integer', 'between:20,5000'],
            'teacher_check_in_late' => ['required', 'date_format:H:i'],
        ], [], [
            'school_lat' => 'garis lintang',
            'school_lng' => 'garis bujur',
            'school_radius_meters' => 'radius',
            'teacher_check_in_late' => 'batas jam tepat waktu',
        ]);

        Setting::putMany([
            'school_lat' => (float) $validated['school_lat'],
            'school_lng' => (float) $validated['school_lng'],
            'school_radius_meters' => (int) $validated['school_radius_meters'],
            'teacher_check_in_late' => $validated['teacher_check_in_late'],
        ]);

        activity('settings')->causedBy(Auth::user())->log('Pengaturan lokasi dan jam presensi guru diubah.');

        $this->dispatch('notify', message: __('Pengaturan presensi guru tersimpan.'), type: 'success');
    }

    public function save(): void
    {
        abort_unless(Auth::user()->can('update', Setting::class), 403);

        $validated = $this->validate([
            'company_name' => ['required', 'string', 'max:150'],
            'company_tagline' => ['nullable', 'string', 'max:200'],
            'company_address' => ['nullable', 'string', 'max:255'],
            'company_phone' => ['nullable', 'string', 'max:50'],
        ]);

        Setting::putMany($validated);

        $this->dispatch('notify', message: __('Kop surat perusahaan tersimpan.'), type: 'success');
    }

    public function saveBranding(): void
    {
        abort_unless(Auth::user()->can('update', Setting::class), 403);

        $validated = $this->validate([
            'app_name' => ['required', 'string', 'max:60'],
            'app_tagline' => ['nullable', 'string', 'max:80'],
            // Tanpa SVG: file disajikan dari domain aplikasi sendiri, SVG bisa membawa skrip.
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:1024'],
        ], [], ['app_name' => 'nama aplikasi', 'app_tagline' => 'tagline', 'logo' => 'logo']);

        Setting::putMany([
            Branding::APP_NAME_KEY => $validated['app_name'],
            Branding::APP_TAGLINE_KEY => $validated['app_tagline'] ?: null,
        ]);

        if ($this->logo) {
            $path = $this->logo->storeAs('branding', 'logo-'.Str::lower(Str::random(12)).'.'.$this->logo->extension(), 'local');
            $this->deleteStoredLogo();
            Setting::put(Branding::LOGO_KEY, $path);
            $this->logo = null;
        }

        activity('settings')->causedBy(Auth::user())->log("Branding aplikasi diubah: {$validated['app_name']}.");

        session()->flash('notify', ['message' => __('Branding aplikasi tersimpan.'), 'type' => 'success']);
        $this->redirectRoute('settings.company-profile', navigate: true);
    }

    public function removeLogo(): void
    {
        abort_unless(Auth::user()->can('update', Setting::class), 403);

        $this->deleteStoredLogo();
        Setting::put(Branding::LOGO_KEY, null);

        activity('settings')->causedBy(Auth::user())->log('Logo aplikasi dihapus.');

        session()->flash('notify', ['message' => __('Logo dihapus.'), 'type' => 'success']);
        $this->redirectRoute('settings.company-profile', navigate: true);
    }

    protected function deleteStoredLogo(): void
    {
        if ($path = Branding::logoPath()) {
            Storage::disk('local')->delete($path);
        }
    }

    public function render()
    {
        return view('livewire.settings.company-profile');
    }
}
