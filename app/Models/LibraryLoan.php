<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\LibraryLoanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LibraryLoan extends Model
{
    use Auditable;

    /** @use HasFactory<LibraryLoanFactory> */
    use HasFactory;

    protected $fillable = [
        'loan_code',
        'library_book_id',
        'borrower_type',
        'student_id',
        'teacher_id',
        'borrow_date',
        'due_date',
        'return_date',
        'fine_amount',
        'status',
        'notes',
        'processed_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'borrow_date' => 'date',
            'due_date' => 'date',
            'return_date' => 'date',
            'fine_amount' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<LibraryBook, $this>
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(LibraryBook::class, 'library_book_id');
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return BelongsTo<Teacher, $this>
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function getBorrowerNameAttribute(): string
    {
        if ($this->borrower_type === 'student') {
            return $this->student?->name ?? 'Santri';
        }

        return $this->teacher?->name ?? 'Guru / Staf';
    }
}
