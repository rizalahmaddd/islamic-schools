<?php

namespace App\Models;

use Database\Factories\QuranSurahFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuranSurah extends Model
{
    /** @use HasFactory<QuranSurahFactory> */
    use HasFactory;

    protected $fillable = [
        'number',
        'name_arabic',
        'name_latin',
        'total_verses',
        'juz_start',
        'revelation_type',
    ];

    protected function casts(): array
    {
        return [
            'number' => 'integer',
            'total_verses' => 'integer',
            'juz_start' => 'integer',
        ];
    }

    /**
     * @return HasMany<TahfidzSession, $this>
     */
    public function tahfidzSessions(): HasMany
    {
        return $this->hasMany(TahfidzSession::class, 'surah_id');
    }
}
