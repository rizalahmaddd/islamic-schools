<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\LibraryBookFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LibraryBook extends Model
{
    use Auditable;

    /** @use HasFactory<LibraryBookFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'isbn',
        'title',
        'author',
        'publisher',
        'publish_year',
        'category',
        'call_number',
        'rack_location',
        'total_copies',
        'available_copies',
        'barcode_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'publish_year' => 'integer',
            'total_copies' => 'integer',
            'available_copies' => 'integer',
        ];
    }

    /**
     * @return HasMany<LibraryLoan, $this>
     */
    public function loans(): HasMany
    {
        return $this->hasMany(LibraryLoan::class);
    }

    /**
     * @return HasMany<LibraryLoan, $this>
     */
    public function activeLoans(): HasMany
    {
        return $this->hasMany(LibraryLoan::class)->whereIn('status', ['active', 'overdue']);
    }
}
