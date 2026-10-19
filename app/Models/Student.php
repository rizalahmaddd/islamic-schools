<?php

namespace App\Models;

use App\Events\StudentChanged;
use App\Models\Concerns\Auditable;
use Database\Factories\StudentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use Auditable;

    /** @use HasFactory<StudentFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Lewat event model supaya perubahan dari halaman web, impor Excel, maupun API sama-sama tersiar.
     *
     * @var array<string, class-string>
     */
    protected $dispatchesEvents = [
        'saved' => StudentChanged::class,
        'deleted' => StudentChanged::class,
    ];

    protected $fillable = [
        'guardian_id',
        'nis',
        'nisn',
        'nik',
        'name',
        'nickname',
        'gender',
        'pob',
        'dob',
        'phone',
        'address',
        'status',
        'photo_path',
    ];

    protected function casts(): array
    {
        return [
            'dob' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Guardian, $this>
     */
    public function guardian(): BelongsTo
    {
        return $this->belongsTo(Guardian::class, 'guardian_id');
    }

    /**
     * @return HasMany<StudentEnrollment, $this>
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class, 'student_id');
    }

    /**
     * @return HasOne<StudentEnrollment, $this>
     */
    public function currentEnrollment(): HasOne
    {
        return $this->hasOne(StudentEnrollment::class, 'student_id')
            ->whereHas('academicYear', fn ($q) => $q->where('is_active', true))
            ->latestOfMany();
    }

    public function currentClass(): ?SchoolClass
    {
        return $this->currentEnrollment?->schoolClass;
    }

    /**
     * @return HasMany<StudentAttendance, $this>
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(StudentAttendance::class, 'student_id');
    }

    /**
     * @return HasMany<StudentBill, $this>
     */
    public function bills(): HasMany
    {
        return $this->hasMany(StudentBill::class, 'student_id');
    }

    /**
     * @return HasMany<PaymentTransaction, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class, 'student_id');
    }

    /**
     * @return HasMany<StudentFeeWaiver, $this>
     */
    public function feeWaivers(): HasMany
    {
        return $this->hasMany(StudentFeeWaiver::class, 'student_id');
    }

    /**
     * @return HasMany<TahfidzSession, $this>
     */
    public function tahfidzSessions(): HasMany
    {
        return $this->hasMany(TahfidzSession::class, 'student_id');
    }

    /**
     * @return HasMany<StudentDailyWorship, $this>
     */
    public function dailyWorships(): HasMany
    {
        return $this->hasMany(StudentDailyWorship::class, 'student_id');
    }

    /**
     * @return HasMany<StudentConductRecord, $this>
     */
    public function conductRecords(): HasMany
    {
        return $this->hasMany(StudentConductRecord::class, 'student_id');
    }

    /**
     * @return HasMany<StudentGrade, $this>
     */
    public function grades(): HasMany
    {
        return $this->hasMany(StudentGrade::class, 'student_id');
    }

    /**
     * @return HasMany<ReportCardSummary, $this>
     */
    public function reportCardSummaries(): HasMany
    {
        return $this->hasMany(ReportCardSummary::class, 'student_id');
    }

    /**
     * @return HasMany<DormPlacement, $this>
     */
    public function dormPlacements(): HasMany
    {
        return $this->hasMany(DormPlacement::class, 'student_id');
    }

    public function currentDormPlacement(): ?DormPlacement
    {
        return $this->dormPlacements()->with('room.building')->latest('placed_at')->first();
    }

    /**
     * @return HasMany<StudentLeaveRequest, $this>
     */
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(StudentLeaveRequest::class, 'student_id');
    }

    /**
     * @return HasMany<RfidCard, $this>
     */
    public function rfidCards(): HasMany
    {
        return $this->hasMany(RfidCard::class);
    }

    /**
     * @return HasOne<StudentWallet, $this>
     */
    public function wallet(): HasOne
    {
        return $this->hasOne(StudentWallet::class);
    }

    public function totalViolationPoints(): int
    {
        return (int) $this->conductRecords()->where('type', 'violation')->sum('points');
    }

    public function totalMeritPoints(): int
    {
        return (int) $this->conductRecords()->where('type', 'merit')->sum('points');
    }

    public function statusBadgeColor(): string
    {
        return match ($this->status) {
            'active' => 'emerald',
            'graduated' => 'sky',
            'transferred' => 'amber',
            'dropped_out' => 'rose',
            default => 'slate',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'active' => 'Aktif',
            'inactive' => 'Nonaktif',
            'graduated' => 'Lulus',
            'transferred' => 'Pindah',
            'dropped_out' => 'Keluar',
            default => ucfirst((string) $this->status),
        };
    }
}
