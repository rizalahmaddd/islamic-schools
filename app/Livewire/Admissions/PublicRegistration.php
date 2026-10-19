<?php

namespace App\Livewire\Admissions;

use App\Models\AdmissionBatch;
use App\Models\AdmissionCandidate;
use App\Services\AdmissionConversionService;
use App\Support\Phone;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.ppdb')]
#[Title('Pendaftaran Santri Baru (PPDB Online)')]
class PublicRegistration extends Component
{
    public int $currentStep = 1;

    public ?int $admission_batch_id = null;

    // Data Santri
    public string $student_name = '';

    public string $gender = 'male';

    public ?string $nisn = null;

    public ?string $nik = null;

    public ?string $pob = null;

    public ?string $dob = null;

    public ?string $previous_school = null;

    // Data Wali
    public string $guardian_name = '';

    public string $guardian_relationship = 'Ayah';

    public string $guardian_phone = '';

    public ?string $guardian_email = null;

    public ?string $address = null;

    public ?string $notes = null;

    // Status sukses registrasi
    public bool $isSuccess = false;

    public ?AdmissionCandidate $registeredCandidate = null;

    // Cek Status Pendaftaran
    public bool $showLookupModal = false;

    public string $lookup_registration_number = '';

    public string $lookup_phone_last4 = '';

    public ?AdmissionCandidate $lookupResult = null;

    public bool $lookupNotFound = false;

    public function mount(): void
    {
        $this->admission_batch_id = $this->availableBatches()->first()?->id;
    }

    /**
     * @return Collection<int, AdmissionBatch>
     */
    protected function availableBatches(): Collection
    {
        return AdmissionBatch::with('academicYear')
            ->where('is_active', true)
            ->orderBy('start_date')
            ->get()
            ->filter(fn (AdmissionBatch $batch) => $batch->acceptsRegistrations())
            ->values();
    }

    public function nextStep(): void
    {
        if ($this->currentStep === 1) {
            $this->validate([
                'admission_batch_id' => ['required', 'exists:admission_batches,id'],
            ], [
                'admission_batch_id.required' => 'Silakan pilih gelombang pendaftaran terlebih dahulu.',
            ]);

            if ($reason = AdmissionBatch::findOrFail($this->admission_batch_id)->closedReason()) {
                $this->addError('admission_batch_id', $reason);

                return;
            }

            $this->currentStep = 2;

            return;
        }

        if ($this->currentStep === 2) {
            $this->validate([
                'student_name' => ['required', 'string', 'min:3', 'max:150'],
                'gender' => ['required', 'in:male,female'],
                'nisn' => ['nullable', 'string', 'max:20'],
                'nik' => ['nullable', 'string', 'max:20'],
                'pob' => ['nullable', 'string', 'max:100'],
                'dob' => ['nullable', 'date'],
                'previous_school' => ['nullable', 'string', 'max:150'],
            ]);
            $this->currentStep = 3;

            return;
        }

        if ($this->currentStep === 3) {
            $this->validate([
                'guardian_name' => ['required', 'string', 'min:3', 'max:150'],
                'guardian_relationship' => ['required', 'string', 'max:50'],
                'guardian_phone' => Phone::rules(max: 25),
                'guardian_email' => ['nullable', 'email', 'max:100'],
                'address' => ['nullable', 'string', 'max:500'],
                'notes' => ['nullable', 'string', 'max:500'],
            ]);
            $this->currentStep = 4;
        }
    }

    public function prevStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function goToStep(int $step): void
    {
        if ($step < $this->currentStep) {
            $this->currentStep = $step;
        }
    }

    public function register(AdmissionConversionService $service): void
    {
        $validated = $this->validate([
            'admission_batch_id' => ['required', 'exists:admission_batches,id'],
            'student_name' => ['required', 'string', 'min:3', 'max:150'],
            'gender' => ['required', 'in:male,female'],
            'nisn' => ['nullable', 'string', 'max:20'],
            'nik' => ['nullable', 'string', 'max:20'],
            'pob' => ['nullable', 'string', 'max:100'],
            'dob' => ['nullable', 'date'],
            'previous_school' => ['nullable', 'string', 'max:150'],
            'guardian_name' => ['required', 'string', 'min:3', 'max:150'],
            'guardian_relationship' => ['required', 'string', 'max:50'],
            'guardian_phone' => Phone::rules(max: 25),
            'guardian_email' => ['nullable', 'email', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $throttleKey = 'ppdb-register:'.request()->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 10)) {
            $this->addError('guardian_phone', 'Terlalu banyak pendaftaran dari perangkat ini. Coba lagi dalam '.ceil(RateLimiter::availableIn($throttleKey) / 60).' menit.');

            return;
        }

        try {
            $candidate = $service->registerCandidate($validated);
        } catch (ValidationException $e) {
            $this->currentStep = 1;

            throw $e;
        }

        RateLimiter::hit($throttleKey, 3600);

        $this->registeredCandidate = $candidate;
        $this->isSuccess = true;
    }

    public function resetForm(): void
    {
        $this->isSuccess = false;
        $this->currentStep = 1;
        $this->registeredCandidate = null;
        $this->student_name = '';
        $this->gender = 'male';
        $this->nisn = null;
        $this->nik = null;
        $this->pob = null;
        $this->dob = null;
        $this->previous_school = null;
        $this->guardian_name = '';
        $this->guardian_phone = '';
        $this->guardian_email = null;
        $this->address = null;
        $this->notes = null;
    }

    public function openLookupModal(): void
    {
        $this->showLookupModal = true;
        $this->lookup_registration_number = '';
        $this->lookup_phone_last4 = '';
        $this->lookupResult = null;
        $this->lookupNotFound = false;
    }

    public function closeLookupModal(): void
    {
        $this->showLookupModal = false;
        $this->lookupResult = null;
        $this->lookupNotFound = false;
    }

    public function checkStatus(): void
    {
        $cleanNumber = strtoupper(trim($this->lookup_registration_number));
        $phoneLast4 = preg_replace('/\D/', '', $this->lookup_phone_last4);

        if ($cleanNumber === '' || strlen($phoneLast4) !== 4) {
            $this->addError('lookup_phone_last4', 'Isi nomor registrasi dan 4 digit terakhir nomor HP wali yang didaftarkan.');

            return;
        }

        $throttleKey = 'ppdb-lookup:'.request()->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 10)) {
            $this->addError('lookup_phone_last4', 'Terlalu banyak percobaan. Coba lagi dalam '.RateLimiter::availableIn($throttleKey).' detik.');

            return;
        }
        RateLimiter::hit($throttleKey, 60);

        $candidate = AdmissionCandidate::with(['batch', 'batch.academicYear'])
            ->where('registration_number', $cleanNumber)
            ->first();

        // Nomor registrasi berurutan, jadi 4 digit HP wali dipakai sebagai faktor kedua.
        if ($candidate && str_ends_with(preg_replace('/\D/', '', $candidate->guardian_phone), $phoneLast4)) {
            $this->lookupResult = $candidate;
            $this->lookupNotFound = false;
        } else {
            $this->lookupResult = null;
            $this->lookupNotFound = true;
        }
    }

    public function render(): View
    {
        $openBatches = $this->availableBatches();

        $selectedBatch = $this->admission_batch_id
            ? $openBatches->firstWhere('id', $this->admission_batch_id)
            : null;

        return view('livewire.admissions.public-registration', [
            'openBatches' => $openBatches,
            'selectedBatch' => $selectedBatch,
        ]);
    }
}
