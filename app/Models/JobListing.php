<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobListing extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'employer',
        'category',
        'location',
        'employment_type',
        'excerpt',
        'description',
        'salary',
        'deadline',
        'application_url',
        'application_email',
        'application_instructions',
        'status',
        'is_featured',
        'published_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'deadline' => 'date',
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where(fn (Builder $open) => $open
                ->whereNull('deadline')
                ->orWhereDate('deadline', '>=', today()));
    }

    public function scopePubliclyPublished(Builder $query): Builder
    {
        return $query
            ->whereIn('status', ['published', 'closed'])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function isOpen(): bool
    {
        return $this->status === 'published'
            && (! $this->deadline || $this->deadline->greaterThanOrEqualTo(today()));
    }

    public function getCategoryLabelAttribute(): string
    {
        return [
            'teaching' => 'Teaching',
            'administration' => 'Administration',
            'higher' => 'Higher education',
            'ngo' => 'NGO & education sector',
            'support' => 'Support staff',
            'other' => 'Other',
        ][$this->category] ?? 'Education';
    }

    public function getEmploymentTypeLabelAttribute(): string
    {
        return [
            'full_time' => 'Full-time',
            'part_time' => 'Part-time',
            'temporary' => 'Temporary',
            'contract' => 'Contract',
            'internship' => 'Internship',
            'volunteer' => 'Volunteer',
        ][$this->employment_type] ?? str_replace('_', ' ', ucfirst($this->employment_type));
    }
}
