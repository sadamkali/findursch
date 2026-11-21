<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Scout\Searchable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class School extends Model implements HasMedia
{
    use HasSlug, InteractsWithMedia, Searchable, LogsActivity;

    protected $fillable = [
        'owner_id', 'type_id', 'district_id', 'sub_county_id', 'custom_fields',
        'name', 'slug', 'tagline', 'description',
        'founded_year', 'registration_number',
        'latitude', 'longitude', 'address', 'town', 'po_box',
        'nearest_landmark', 'maps_url', 'ownership', 'religious_affiliation',
        'phone', 'email', 'website', 'whatsapp', 'facebook_url', 'instagram_url',
        'admissions_open', 'admission_classes', 'admission_deadline', 'application_fee',
        'admissions_contact', 'admission_requirements', 'how_to_apply',
        'boarding_type', 'gender_policy', 'student_count_approx',
        'fees_from', 'fees_to', 'activities', 'referral_source',
        'status', 'verification_status',
        'verified_at', 'verified_by', 'submitted_at', 'rejection_reason',
        'is_featured', 'featured_until',
        'meta_title', 'meta_description',
    ];

    protected $casts = [
        'custom_fields' => 'array',
        'is_featured' => 'boolean',
        'admissions_open' => 'boolean',
        'admission_deadline' => 'date',
        'featured_until' => 'date',
        'rating_average' => 'decimal:2',
        'submitted_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'status', 'verification_status', 'is_featured'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(280)
            ->doNotGenerateSlugsOnUpdate();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(SchoolType::class, 'type_id');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function subCounty(): BelongsTo
    {
        return $this->belongsTo(SubCounty::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function curricula(): BelongsToMany
    {
        return $this->belongsToMany(Curriculum::class, 'school_curriculum');
    }

    public function programs(): HasMany
    {
        return $this->hasMany(SchoolProgram::class);
    }

    public function fees(): HasMany
    {
        return $this->hasMany(SchoolFee::class);
    }

    public function facilities(): BelongsToMany
    {
        return $this->belongsToMany(Facility::class, 'school_facilities', 'school_id', 'facility_id')->withPivot('notes');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('status', 'approved');
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->where('status', 'active')->latestOfMany();
    }

    public function featuredSlots(): HasMany
    {
        return $this->hasMany(FeaturedSlot::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(SchoolContact::class);
    }

    public function savedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'saved_schools')->withPivot('notes')->withTimestamps();
    }

    public function views(): HasMany
    {
        return $this->hasMany(SchoolView::class);
    }

    public function toSearchableArray(): array
    {
        $this->loadMissing(['district', 'type', 'curricula']);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'tagline' => $this->tagline,
            'description' => $this->description,
            'district' => $this->district?->name,
            'region' => $this->district?->region?->name,
            'type' => $this->type?->name,
            'curricula' => $this->curricula->pluck('abbreviation')->toArray(),
            'status' => $this->status,
            'verification_status' => $this->verification_status,
            'is_featured' => (bool) $this->is_featured,
            'rating_average' => (float) $this->rating_average,
            'district_id' => $this->district_id,
            'type_id' => $this->type_id,
            'boarding_type' => $this->boarding_type,
            'gender_policy' => $this->gender_policy,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->status === 'active' && $this->verification_status === 'verified';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('verification_status', 'verified');
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->where('verification_status', 'verified');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true)
            ->where(function ($q) {
                $q->whereNull('featured_until')->orWhere('featured_until', '>=', now());
            });
    }

    public function scopeInDistrict(Builder $query, int $districtId): Builder
    {
        return $query->where('district_id', $districtId);
    }

    public function scopeByType(Builder $query, int $typeId): Builder
    {
        return $query->where('type_id', $typeId);
    }

    public function scopeByCurriculum(Builder $query, int $curriculumId): Builder
    {
        return $query->whereHas('curricula', fn ($q) => $q->where('curricula.id', $curriculumId));
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function isPremium(): bool
    {
        $sub = $this->activeSubscription;
        return $sub && $sub->plan && in_array($sub->plan->slug, ['premium', 'standard']);
    }

    public function canReceiveInquiries(): bool
    {
        return $this->status === 'active'
            && $this->verification_status === 'verified';
    }

    public function updateRatingCache(): void
    {
        $stats = $this->approvedReviews()
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as review_count')
            ->first();

        $this->rating_average = $stats->avg_rating ? round($stats->avg_rating, 2) : 0;
        $this->reviews_count = $stats->review_count ?? 0;
        $this->saveQuietly();
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    public function incrementInquiries(): void
    {
        $this->increment('inquiries_count');
    }

    public function getLowestAnnualFee(): ?float
    {
        $fee = $this->fees()
            ->where('category', 'tuition')
            ->where('is_active', true)
            ->orderBy('amount')
            ->first();

        if (! $fee) return null;

        return match ($fee->period) {
            'per_term' => $fee->amount * 3,
            'per_year' => $fee->amount,
            'per_month' => $fee->amount * 12,
            'once_off' => $fee->amount,
            default => $fee->amount,
        };
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('gallery')->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
        $this->addMediaCollection('cover')->singleFile()->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
        $this->addMediaCollection('logo')->singleFile()->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml']);
        $this->addMediaCollection('badge')->singleFile()->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml']);
    }
}
