<?php

namespace App\Livewire\Admissions;

use App\Livewire\Concerns\WithDataTable;
use App\Models\AdmissionBatch;
use App\Models\AdmissionCandidate;
use App\Models\SchoolClass;
use App\Services\AdmissionConversionService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app', ['heading' => 'Data Calon Santri (PPDB)'])]
#[Title('Data Calon Santri')]
class CandidateIndex extends Component
{
    use WithDataTable;

    public string $search = '';

    public string $batchFilter = '';

    public string $statusFilter = '';

    // Modal Input Nilai Tes
    public bool $showScoreModal = false;

    public ?int $scoringCandidateId = null;

    public ?float $test_score_academic = null;

    public ?float $test_score_quran = null;

    public ?float $test_score_interview = null;

    public ?string $test_notes = null;

    // Modal 1-Klik Konversi
    public bool $showConvertModal = false;

    public ?int $convertingCandidateId = null;

    public ?int $target_class_id = null;

    public ?string $custom_nis = null;

    public bool $create_user_account = true;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingBatchFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function verifyCandidate(int $candidateId, AdmissionConversionService $service): void
    {
        $this->authorizeManage();
        $candidate = AdmissionCandidate::findOrFail($candidateId);
        $service->updateStatus($candidate, 'verified');
        $this->dispatch('notify', 'Berkas calon santri '.$candidate->student_name.' berhasil diverifikasi.');
    }

    public function openScoreModal(int $candidateId): void
    {
        $this->authorizeManage();
        $candidate = AdmissionCandidate::findOrFail($candidateId);
        $this->scoringCandidateId = $candidateId;
        $this->test_score_academic = $candidate->test_score_academic ? (float) $candidate->test_score_academic : null;
        $this->test_score_quran = $candidate->test_score_quran ? (float) $candidate->test_score_quran : null;
        $this->test_score_interview = $candidate->test_score_interview ? (float) $candidate->test_score_interview : null;
        $this->test_notes = $candidate->test_notes;
        $this->showScoreModal = true;
        $this->dispatch('open-modal', 'score-modal');
    }

    public function saveScores(AdmissionConversionService $service): void
    {
        $this->authorizeManage();
        $this->validate([
            'test_score_academic' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'test_score_quran' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'test_score_interview' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'test_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $candidate = AdmissionCandidate::findOrFail($this->scoringCandidateId);
        $service->updateTestScores($candidate, [
            'test_score_academic' => $this->test_score_academic,
            'test_score_quran' => $this->test_score_quran,
            'test_score_interview' => $this->test_score_interview,
            'test_notes' => $this->test_notes,
        ]);

        $this->showScoreModal = false;
        $this->dispatch('close-modal', 'score-modal');
        $this->dispatch('notify', 'Nilai tes seleksi berhasil disimpan.');
    }

    public function markAsPassed(int $candidateId, AdmissionConversionService $service): void
    {
        $this->authorizeManage();
        $candidate = AdmissionCandidate::findOrFail($candidateId);
        $service->updateStatus($candidate, 'passed');
        $this->dispatch('notify', 'Calon santri '.$candidate->student_name.' dinyatakan LULUS SELEKSI. Notifikasi WA telah dikirimkan.');
    }

    public function markAsRejected(int $candidateId, AdmissionConversionService $service): void
    {
        $this->authorizeManage();
        $candidate = AdmissionCandidate::findOrFail($candidateId);
        $service->updateStatus($candidate, 'rejected');
        $this->dispatch('notify', 'Calon santri '.$candidate->student_name.' ditandai TIDAK LULUS.');
    }

    public function openConvertModal(int $candidateId): void
    {
        $this->authorizeManage();
        $candidate = AdmissionCandidate::findOrFail($candidateId);
        $this->convertingCandidateId = $candidateId;
        $this->custom_nis = null;
        $this->create_user_account = true;

        $firstClass = SchoolClass::where('academic_year_id', $candidate->batch->academic_year_id)->first()
            ?: SchoolClass::first();
        $this->target_class_id = $firstClass?->id;

        $this->showConvertModal = true;
        $this->dispatch('open-modal', 'convert-modal');
    }

    public function executeConversion(AdmissionConversionService $service): void
    {
        $this->authorizeManage();
        $this->validate([
            'target_class_id' => ['required', 'exists:classes,id'],
            'custom_nis' => ['nullable', 'string', 'max:30', 'unique:students,nis'],
        ]);

        $candidate = AdmissionCandidate::findOrFail($this->convertingCandidateId);
        $student = $service->convertCandidateToStudent(
            $candidate,
            $this->target_class_id,
            $this->custom_nis,
            $this->create_user_account
        );

        $this->showConvertModal = false;
        $this->dispatch('close-modal', 'convert-modal');
        $this->dispatch('notify', 'Alhamdulillah! Calon santri berhasil dikonversi menjadi Santri Aktif dengan NIS '.$student->nis.'.');
    }

    protected function authorizeManage(): void
    {
        abort_unless(auth()->user()->can('admissions.manage') || auth()->user()->hasRole('superadmin'), 403);
    }

    public function render()
    {
        $batches = AdmissionBatch::with('academicYear')->orderByDesc('start_date')->get();

        $schoolClasses = SchoolClass::with('academicYear')->orderBy('name')->get();

        $query = AdmissionCandidate::query()
            ->with(['batch.academicYear', 'convertedStudent'])
            ->when($this->batchFilter, fn ($q) => $q->where('admission_batch_id', $this->batchFilter))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->search, function ($q) {
                $q->where(function ($search) {
                    $search->where('student_name', 'like', "%{$this->search}%")
                        ->orWhere('registration_number', 'like', "%{$this->search}%")
                        ->orWhere('guardian_name', 'like', "%{$this->search}%")
                        ->orWhere('guardian_phone', 'like', "%{$this->search}%")
                        ->orWhere('previous_school', 'like', "%{$this->search}%");
                });
            })
            ->latest('id');

        $candidates = $query->paginate($this->perPage);

        return view('livewire.admissions.candidate-index', [
            'candidates' => $candidates,
            'batches' => $batches,
            'schoolClasses' => $schoolClasses,
        ]);
    }
}
