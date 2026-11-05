<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SleepEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'bedtime',
        'wake_time',
        'duration_minutes',
        'quality',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'bedtime' => 'datetime',
        'wake_time' => 'datetime',
    ];

    public const QUALITY_LABELS = [
        1 => 'Awful',
        2 => 'Poor',
        3 => 'Okay',
        4 => 'Good',
        5 => 'Great',
    ];

    public function getQualityLabelAttribute(): string
    {
        return self::QUALITY_LABELS[$this->quality] ?? '—';
    }

    protected static function booted(): void
    {
        static::saving(function (SleepEntry $entry) {
            $entry->duration_minutes = (int) $entry->bedtime->diffInMinutes($entry->wake_time);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function habits(): BelongsToMany
    {
        return $this->belongsToMany(Habit::class);
    }
}