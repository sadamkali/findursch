<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Review extends Model
{
    protected $fillable = [
        'school_id',
        'user_id',
        'reviewer_type',
        'rating',
        'title',
        'body',
        'academic_rating',
        'facilities_rating',
        'staff_rating',
        'environment_rating',
        'status',
        'is_verified',
        'owner_response',
        'owner_responded_at',
        'helpful_count',
        'moderated_by',
        'moderated_at',
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'owner_responded_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (Review $review): void {
            if ($review->wasRecentlyCreated || $review->wasChanged(['rating', 'status'])) {
                $review->school?->updateRatingCache();
            }
        });

        static::deleted(function (Review $review): void {
            $review->school?->updateRatingCache();
        });
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(ReviewVote::class);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }
}
