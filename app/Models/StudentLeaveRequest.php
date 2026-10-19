<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\StudentLeaveRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentLeaveRequest extends Model
{
    use Auditable;

    /** @use HasFactory<StudentLeaveRequestFactory> */
    use HasFactory;

    protected $fillable = [
        'leave_number',
        'student_id',
        'academic_year_id',
        'leave_type',
        'reason',
        'departure_plan',
        'return_plan',
        'pickup_person',
        'status',
        'qr_pass_token',
        'approved_by',
        'approved_at',
        'actual_departure_at',
        'actual_returned_at',
        'security_departure_user_id',
        'security_return_user_id',
        'rejection_reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'departure_plan' => 'datetime',
            'return_plan' => 'datetime',
            'approved_at' => 'datetime',
            'actual_departure_at' => 'datetime',
            'actual_returned_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /**
     * @return BelongsTo<AcademicYear, $this>
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function securityDepartureUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'security_departure_user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function securityReturnUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'security_return_user_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return in_array($this->status, ['approved_mentor', 'approved_head'], true);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isReturned(): bool
    {
        return in_array($this->status, ['returned', 'late'], true);
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu Persetujuan',
            'approved_mentor' => 'Disetujui Musyrif',
            'approved_head' => 'Disetujui Kepala Asrama',
            'active' => 'Di Luar Pondok',
            'returned' => 'Kembali Tepat Waktu',
            'late' => 'Kembali Terlambat',
            'rejected' => 'Ditolak',
            default => ucfirst((string) $this->status),
        };
    }

    public function statusBadgeColor(): string
    {
        return match ($this->status) {
            'pending' => 'amber',
            'approved_mentor', 'approved_head' => 'sky',
            'active' => 'emerald',
            'returned' => 'slate',
            'late', 'rejected' => 'rose',
            default => 'slate',
        };
    }

    public function leaveTypeLabel(): string
    {
        return match ($this->leave_type) {
            'weekend' => 'Pulang Rutin / Weekend',
            'sick' => 'Izin Berobat / Sakit',
            'family' => 'Acara Keluarga / Penting',
            'dispensation' => 'Dispensasi Lomba / Dinas',
            default => ucfirst((string) $this->leave_type),
        };
    }
}
