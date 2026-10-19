<?php

namespace App\Livewire\Finance;

use App\Livewire\Concerns\WithDataTable;
use App\Models\AcademicYear;
use App\Models\FeeCategory;
use App\Models\FeeStructure;
use App\Models\Student;
use App\Models\StudentFeeWaiver;
use App\Services\BillingService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app', ['heading' => 'Pengaturan Biaya & SPP'])]
#[Title('Pengaturan Biaya & SPP')]
class FeeCategories extends Component
{
    use WithDataTable;

    public string $activeTab = 'categories'; // categories, structures, waivers

    public string $search = '';

    // Modal & Delete state
    public bool $showCategoryModal = false;

    public ?int $editingCategoryId = null;

    public bool $showStructureModal = false;

    public ?int $editingStructureId = null;

    public bool $showWaiverModal = false;

    public ?int $editingWaiverId = null;

    public bool $showGenerateModal = false;

    public ?int $confirmingDeleteId = null;

    public string $deleteTarget = ''; // category, structure, waiver

    // Form fields Kategori Biaya
    public string $category_name = '';

    public string $category_type = 'monthly';

    public bool $category_is_active = true;

    // Form fields Struktur Tarif
    public ?int $structure_academic_year_id = null;

    public ?int $structure_fee_category_id = null;

    public ?string $structure_grade_level = null;

    public float|string $structure_default_amount = '';

    // Form fields Keringanan Siswa
    public ?int $waiver_student_id = null;

    public ?int $waiver_fee_category_id = null;

    public ?int $waiver_academic_year_id = null;

    public string $waiver_discount_type = 'percentage';

    public float|string $waiver_discount_value = '';

    public string $waiver_reason = '';

    // Form fields Generate Tagihan Massal
    public ?int $gen_academic_year_id = null;

    public ?int $gen_fee_category_id = null;

    public int $gen_month = 7;

    public int $gen_year = 2026;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('manage-finance') || auth()->user()->can('view-finance'), 403);

        $activeYear = AcademicYear::query()->where('is_active', true)->first();
        if ($activeYear) {
            $this->structure_academic_year_id = $activeYear->id;
            $this->waiver_academic_year_id = $activeYear->id;
            $this->gen_academic_year_id = $activeYear->id;
        }

        $firstMonthlyCat = FeeCategory::query()->where('type', 'monthly')->first();
        if ($firstMonthlyCat) {
            $this->gen_fee_category_id = $firstMonthlyCat->id;
        }

        $this->gen_month = (int) now()->month;
        $this->gen_year = (int) now()->year;
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
        $this->search = '';
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function canManage(): bool
    {
        return auth()->user()->can('manage-finance');
    }

    protected function authorizeManage(): void
    {
        abort_unless($this->canManage(), 403);
    }

    // --- Kategori Biaya ---
    public function openCategoryCreate(): void
    {
        $this->authorizeManage();
        $this->resetValidation();
        $this->editingCategoryId = null;
        $this->category_name = '';
        $this->category_type = 'monthly';
        $this->category_is_active = true;
        $this->showCategoryModal = true;
        $this->dispatch('open-modal', 'category-form');
    }

    public function openCategoryEdit(int $id): void
    {
        $this->authorizeManage();
        $this->resetValidation();
        $cat = FeeCategory::findOrFail($id);
        $this->editingCategoryId = $id;
        $this->category_name = $cat->name;
        $this->category_type = $cat->type;
        $this->category_is_active = (bool) $cat->is_active;
        $this->showCategoryModal = true;
        $this->dispatch('open-modal', 'category-form');
    }

    public function saveCategory(): void
    {
        $this->authorizeManage();
        $this->validate([
            'category_name' => ['required', 'string', 'max:100'],
            'category_type' => ['required', 'in:monthly,non_monthly'],
            'category_is_active' => ['boolean'],
        ]);

        FeeCategory::updateOrCreate(['id' => $this->editingCategoryId], [
            'name' => $this->category_name,
            'type' => $this->category_type,
            'is_active' => $this->category_is_active,
        ]);

        $this->showCategoryModal = false;
        $this->dispatch('close-modal', 'category-form');
        $this->dispatch('notify', message: 'Kategori biaya berhasil disimpan.', type: 'success');
    }

    // --- Struktur Tarif ---
    public function openStructureCreate(): void
    {
        $this->authorizeManage();
        $this->resetValidation();
        $this->editingStructureId = null;
        $this->structure_grade_level = null;
        $this->structure_default_amount = '';
        $this->showStructureModal = true;
        $this->dispatch('open-modal', 'structure-form');
    }

    public function openStructureEdit(int $id): void
    {
        $this->authorizeManage();
        $this->resetValidation();
        $struct = FeeStructure::findOrFail($id);
        $this->editingStructureId = $id;
        $this->structure_academic_year_id = $struct->academic_year_id;
        $this->structure_fee_category_id = $struct->fee_category_id;
        $this->structure_grade_level = $struct->grade_level;
        $this->structure_default_amount = (float) $struct->default_amount;
        $this->showStructureModal = true;
        $this->dispatch('open-modal', 'structure-form');
    }

    public function saveStructure(): void
    {
        $this->authorizeManage();
        $this->validate([
            'structure_academic_year_id' => ['required', 'exists:academic_years,id'],
            'structure_fee_category_id' => ['required', 'exists:fee_categories,id'],
            'structure_grade_level' => ['nullable', 'string', 'max:10'],
            'structure_default_amount' => ['required', 'numeric', 'min:0'],
        ]);

        FeeStructure::updateOrCreate(['id' => $this->editingStructureId], [
            'academic_year_id' => $this->structure_academic_year_id,
            'fee_category_id' => $this->structure_fee_category_id,
            'grade_level' => $this->structure_grade_level ?: null,
            'default_amount' => $this->structure_default_amount,
        ]);

        $this->showStructureModal = false;
        $this->dispatch('close-modal', 'structure-form');
        $this->dispatch('notify', message: 'Struktur tarif berhasil disimpan.', type: 'success');
    }

    // --- Keringanan Siswa (Waiver) ---
    public function openWaiverCreate(): void
    {
        $this->authorizeManage();
        $this->resetValidation();
        $this->editingWaiverId = null;
        $this->waiver_student_id = null;
        $this->waiver_discount_type = 'percentage';
        $this->waiver_discount_value = '';
        $this->waiver_reason = '';
        $this->showWaiverModal = true;
        $this->dispatch('open-modal', 'waiver-form');
    }

    public function openWaiverEdit(int $id): void
    {
        $this->authorizeManage();
        $this->resetValidation();
        $w = StudentFeeWaiver::findOrFail($id);
        $this->editingWaiverId = $id;
        $this->waiver_student_id = $w->student_id;
        $this->waiver_fee_category_id = $w->fee_category_id;
        $this->waiver_academic_year_id = $w->academic_year_id;
        $this->waiver_discount_type = $w->discount_type;
        $this->waiver_discount_value = (float) $w->discount_value;
        $this->waiver_reason = $w->reason ?? '';
        $this->showWaiverModal = true;
        $this->dispatch('open-modal', 'waiver-form');
    }

    public function saveWaiver(): void
    {
        $this->authorizeManage();
        $this->validate([
            'waiver_student_id' => ['required', 'exists:students,id'],
            'waiver_fee_category_id' => ['required', 'exists:fee_categories,id'],
            'waiver_academic_year_id' => ['required', 'exists:academic_years,id'],
            'waiver_discount_type' => ['required', 'in:percentage,fixed'],
            'waiver_discount_value' => ['required', 'numeric', 'min:0'],
            'waiver_reason' => ['nullable', 'string', 'max:255'],
        ]);

        StudentFeeWaiver::updateOrCreate(['id' => $this->editingWaiverId], [
            'student_id' => $this->waiver_student_id,
            'fee_category_id' => $this->waiver_fee_category_id,
            'academic_year_id' => $this->waiver_academic_year_id,
            'discount_type' => $this->waiver_discount_type,
            'discount_value' => $this->waiver_discount_value,
            'reason' => $this->waiver_reason ?: null,
        ]);

        $this->showWaiverModal = false;
        $this->dispatch('close-modal', 'waiver-form');
        $this->dispatch('notify', message: 'Keringanan biaya berhasil disimpan.', type: 'success');
    }

    // --- Delete Action ---
    public function confirmDelete(string $target, int $id): void
    {
        $this->authorizeManage();
        $this->deleteTarget = $target;
        $this->confirmingDeleteId = $id;
        $this->dispatch('open-modal', 'confirm-delete');
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeleteId = null;
        $this->deleteTarget = '';
        $this->dispatch('close-modal', 'confirm-delete');
    }

    public function delete(): void
    {
        $this->authorizeManage();
        if (! $this->confirmingDeleteId) {
            return;
        }

        if ($this->deleteTarget === 'category') {
            $cat = FeeCategory::findOrFail($this->confirmingDeleteId);
            if ($cat->bills()->exists()) {
                $this->dispatch('close-modal', 'confirm-delete');
                $this->dispatch('notify', message: 'Kategori ini sudah memiliki tagihan siswa, tidak dapat dihapus.', type: 'error');

                return;
            }
            $cat->delete();
        } elseif ($this->deleteTarget === 'structure') {
            FeeStructure::findOrFail($this->confirmingDeleteId)->delete();
        } elseif ($this->deleteTarget === 'waiver') {
            StudentFeeWaiver::findOrFail($this->confirmingDeleteId)->delete();
        }

        $this->confirmingDeleteId = null;
        $this->deleteTarget = '';
        $this->dispatch('close-modal', 'confirm-delete');
        $this->dispatch('notify', message: 'Data berhasil dihapus.', type: 'success');
    }

    // --- Generate Tagihan Massal ---
    public function openGenerateModal(): void
    {
        $this->authorizeManage();
        $this->resetValidation();
        $this->showGenerateModal = true;
        $this->dispatch('open-modal', 'generate-bills-modal');
    }

    public function closeGenerateModal(): void
    {
        $this->showGenerateModal = false;
        $this->dispatch('close-modal', 'generate-bills-modal');
    }

    public function runGenerateBills(): void
    {
        $this->authorizeManage();
        $this->validate([
            'gen_academic_year_id' => ['required', 'exists:academic_years,id'],
            'gen_fee_category_id' => ['required', 'exists:fee_categories,id'],
            'gen_month' => ['required', 'integer', 'between:1,12'],
            'gen_year' => ['required', 'integer', 'min:2020', 'max:2040'],
        ]);

        $result = app(BillingService::class)->generateMonthlyBills(
            academicYearId: (int) $this->gen_academic_year_id,
            feeCategoryId: (int) $this->gen_fee_category_id,
            month: (int) $this->gen_month,
            year: (int) $this->gen_year
        );

        $this->showGenerateModal = false;
        $this->dispatch('close-modal', 'generate-bills-modal');

        $msg = "Hasil Generate Tagihan: {$result['created']} tagihan baru dibuat, {$result['skipped']} siswa dilewati (sudah ada / tidak ada rombel).";
        $this->dispatch('notify', message: $msg, type: 'success');
    }

    public function render(): View
    {
        $academicYears = AcademicYear::query()->orderByDesc('start_date')->get();
        $feeCategories = FeeCategory::query()->orderBy('name')->get();
        $students = Student::query()->where('status', 'active')->orderBy('name')->get();

        $categories = collect();
        $structures = collect();
        $waivers = collect();

        if ($this->activeTab === 'categories') {
            $categories = FeeCategory::query()
                ->when($this->search !== '', fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'))
                ->orderBy('name')
                ->paginate(15);
        } elseif ($this->activeTab === 'structures') {
            $structures = FeeStructure::query()
                ->with(['academicYear', 'feeCategory'])
                ->when($this->search !== '', function ($q) {
                    $q->whereHas('feeCategory', fn ($sq) => $sq->where('name', 'like', '%'.$this->search.'%'));
                })
                ->orderBy('academic_year_id', 'desc')
                ->paginate(15);
        } elseif ($this->activeTab === 'waivers') {
            $waivers = StudentFeeWaiver::query()
                ->with(['student.currentEnrollment.schoolClass', 'feeCategory', 'academicYear'])
                ->when($this->search !== '', function ($q) {
                    $q->whereHas('student', fn ($sq) => $sq->where('name', 'like', '%'.$this->search.'%')->orWhere('nis', 'like', '%'.$this->search.'%'));
                })
                ->orderByDesc('id')
                ->paginate(15);
        }

        return view('livewire.finance.fee-categories', [
            'academicYears' => $academicYears,
            'feeCategories' => $feeCategories,
            'students' => $students,
            'categories' => $categories,
            'structures' => $structures,
            'waivers' => $waivers,
        ]);
    }
}
